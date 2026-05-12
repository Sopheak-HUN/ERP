import { createApi } from '~/composables/useApi'

/**
 * Boots `$api` for the whole app. Pulls `apiBase` from runtime config so the
 * value can change between dev (proxied via nginx) and prod without rebuilding.
 *
 * Toast/auth hooks are intentionally stubs — the auth module slice will wire
 * the real token getter and error toasts later.
 */
export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const api = createApi({
    baseURL: config.public.apiBase,
    getToken: () => {
      if (typeof window === 'undefined') return null
      try {
        return window.localStorage?.getItem('erp:token')
      }
      catch {
        return null
      }
    },
    onError: (error) => {
      if (import.meta.dev) {
        // eslint-disable-next-line no-console
        console.warn('[api]', error.status, error.code, error.message, error)
      }
    },
  })

  nuxtApp.provide('api', api)
})
