import { afterEach, describe, expect, it, vi } from 'vitest'
import { ApiError, createApi } from '../../app/composables/useApi'

/**
 * `createApi` is the testable surface of the $api plugin. We stub global
 * `$fetch.create` so we can drive its callbacks without a real network round-trip.
 */
describe('createApi', () => {
  afterEach(() => {
    vi.restoreAllMocks()
  })

  it('unwraps the success envelope into bare data', () => {
    const opts: Record<string, unknown> = {}
    // @ts-expect-error -- inject minimal $fetch surface for the test
    globalThis.$fetch = { create: (o: Record<string, unknown>) => { Object.assign(opts, o); return (() => {}) } }

    createApi({ baseURL: '/api/v1' })

    const onResponse = opts.onResponse as (ctx: { response: { _data: unknown, headers: Headers } }) => void
    const fakeResponse = {
      _data: { success: true, data: { id: 1, name: 'Ada' }, message: 'ok' },
      headers: new Headers({ 'X-Request-Id': 'req-1' }),
    }
    onResponse({ response: fakeResponse })

    expect(fakeResponse._data).toEqual({ id: 1, name: 'Ada' })
  })

  it('throws ApiError carrying code/message/details on error responses', () => {
    const opts: Record<string, unknown> = {}
    // @ts-expect-error -- inject minimal $fetch surface for the test
    globalThis.$fetch = { create: (o: Record<string, unknown>) => { Object.assign(opts, o); return (() => {}) } }

    createApi({ baseURL: '/api/v1' })

    const onResponseError = opts.onResponseError as (ctx: { response: { status: number, statusText: string, _data: unknown, headers: Headers } }) => void

    expect(() => onResponseError({
      response: {
        status: 422,
        statusText: 'Unprocessable Entity',
        _data: {
          success: false,
          error: { code: 'VALIDATION_ERROR', message: 'invalid', details: { email: ['required'] } },
        },
        headers: new Headers({ 'X-Request-Id': 'req-2' }),
      },
    })).toThrowError(ApiError)

    try {
      onResponseError({
        response: {
          status: 422,
          statusText: 'Unprocessable Entity',
          _data: {
            success: false,
            error: { code: 'VALIDATION_ERROR', message: 'invalid', details: { email: ['required'] } },
          },
          headers: new Headers({ 'X-Request-Id': 'req-2' }),
        },
      })
    }
    catch (err) {
      expect(err).toBeInstanceOf(ApiError)
      const apiErr = err as ApiError
      expect(apiErr.status).toBe(422)
      expect(apiErr.code).toBe('VALIDATION_ERROR')
      expect(apiErr.message).toBe('invalid')
      expect(apiErr.details).toEqual({ email: ['required'] })
      expect(apiErr.requestId).toBe('req-2')
    }
  })

  it('attaches Authorization header when getToken returns a value', () => {
    const opts: Record<string, unknown> = {}
    // @ts-expect-error -- inject minimal $fetch surface for the test
    globalThis.$fetch = { create: (o: Record<string, unknown>) => { Object.assign(opts, o); return (() => {}) } }

    createApi({ baseURL: '/api/v1', getToken: () => 'tok_123' })

    const onRequest = opts.onRequest as (ctx: { options: { headers?: HeadersInit, method?: string } }) => void
    const requestOptions: { headers?: HeadersInit, method?: string } = { method: 'POST' }
    onRequest({ options: requestOptions })

    const headers = new Headers(requestOptions.headers)
    expect(headers.get('Authorization')).toBe('Bearer tok_123')
    expect(headers.get('Accept')).toBe('application/json')
    expect(headers.get('Content-Type')).toBe('application/json')
    expect(headers.get('X-Request-Id')).not.toBeNull()
  })
})
