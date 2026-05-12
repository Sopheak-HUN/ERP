import type { FetchOptions } from 'ofetch'

type ApiFetch = ReturnType<typeof $fetch.create>

/**
 * Standard JSON envelope returned by the Laravel backend.
 */
export interface ApiSuccess<T> {
  success: true
  data: T
  message?: string
  meta?: Record<string, unknown>
}

export interface ApiErrorPayload {
  code: string
  message: string
  details?: unknown
}

export interface ApiErrorResponse {
  success: false
  error: ApiErrorPayload
}

/**
 * Thrown by `$api` when the server returns a non-2xx response that follows the
 * error envelope. Carries the structured error payload so UI code can branch on
 * `code` instead of parsing strings.
 */
export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly code: string,
    message: string,
    public readonly details?: unknown,
    public readonly requestId?: string,
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

const REQUEST_ID_STORAGE_KEY = 'erp:lastRequestId'

/**
 * Build a request id locally — server will echo it back, or replace it if not a ULID.
 * We only need a stable correlation id per request, not a real ULID.
 */
function generateRequestId(): string {
  if (typeof crypto !== 'undefined' && 'randomUUID' in crypto) {
    return crypto.randomUUID()
  }
  return `req_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`
}

/**
 * `$api` is a pre-configured fetch wrapper around the Laravel API. It:
 *   - Prefixes the public `apiBase`
 *   - Sends Accept/Content-Type JSON
 *   - Attaches the Sanctum bearer token (if present in cookies/local storage)
 *   - Sends a fresh `X-Request-Id` header per request and remembers the server's reply
 *   - Unwraps the success envelope into the bare `data` payload
 *   - Throws `ApiError` on error envelopes, carrying `code`, `message`, `details`
 *
 * Usage:
 *   const { $api } = useNuxtApp()           // inside composable / store
 *   const users = await $api<User[]>('/users')
 *
 *   // Or via this composable:
 *   const api = useApi()
 *   const user = await api<User>('/user')
 */
export function useApi(): ApiFetch {
  const { $api } = useNuxtApp()
  return $api as ApiFetch
}

/**
 * Factory used by the Nuxt plugin in `plugins/api.ts`.
 * Kept separate so it can be unit-tested without booting Nuxt.
 */
export function createApi(options: {
  baseURL: string
  getToken?: () => string | null | undefined
  onError?: (error: ApiError) => void
}): ApiFetch {
  const { baseURL, getToken, onError } = options

  const defaults: FetchOptions = {
    baseURL,
    credentials: 'include',
    retry: 0,
    onRequest({ options }) {
      const headers = new Headers(options.headers)
      headers.set('Accept', 'application/json')
      if (!headers.has('Content-Type') && options.method && options.method !== 'GET') {
        headers.set('Content-Type', 'application/json')
      }
      headers.set('X-Request-Id', generateRequestId())

      const token = getToken?.()
      if (token) {
        headers.set('Authorization', `Bearer ${token}`)
      }
      options.headers = headers
    },
    onResponse({ response }) {
      const reqId = response.headers.get('X-Request-Id')
      if (reqId && typeof window !== 'undefined') {
        try {
          window.sessionStorage?.setItem(REQUEST_ID_STORAGE_KEY, reqId)
        }
        catch {
          // sessionStorage may be unavailable (private mode, SSR)
        }
      }
      const body = response._data as ApiSuccess<unknown> | ApiErrorResponse | undefined
      if (body && typeof body === 'object' && 'success' in body && body.success) {
        // Unwrap success envelope — callers receive bare `data`.
        response._data = (body as ApiSuccess<unknown>).data
      }
    },
    onResponseError({ response }) {
      const body = response._data as ApiErrorResponse | undefined
      const reqId = response.headers.get('X-Request-Id') ?? undefined
      const err = new ApiError(
        response.status,
        body?.error?.code ?? 'HTTP_ERROR',
        body?.error?.message ?? response.statusText ?? 'Request failed',
        body?.error?.details,
        reqId,
      )
      onError?.(err)
      throw err
    },
  }

  return $fetch.create(defaults)
}
