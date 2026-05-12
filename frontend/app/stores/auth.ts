import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export interface AuthUser {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  has_two_factor: boolean
  created_at: string
  updated_at: string
}

export interface LoginResponse {
  access_token: string
  token_type: 'Bearer'
  expires_at: string
  user: AuthUser
}

export const useAuthStore = defineStore('auth', () => {
  const token = useCookie<string | null>('erp:token', {
    maxAge: 60 * 60 * 24 * 30,
    path: '/',
    sameSite: 'lax',
    secure: !import.meta.dev,
  })

  const user = ref<AuthUser | null>(null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  async function fetchUser() {
    if (!token.value) return
    try {
      const api = useApi()
      user.value = await api<AuthUser>('/auth/me')
    }
    catch {
      clearAuth()
    }
  }

  function setToken(newToken: string) {
    token.value = newToken
  }

  function setUser(newUser: AuthUser) {
    user.value = newUser
  }

  function clearAuth() {
    token.value = null
    user.value = null
  }

  async function logout() {
    if (token.value) {
      try {
        const api = useApi()
        await api('/auth/logout', { method: 'POST' })
      }
      catch {
        // server-side revocation is best-effort; we always clear locally
      }
    }
    clearAuth()
  }

  return {
    token,
    user,
    isAuthenticated,
    fetchUser,
    setToken,
    setUser,
    clearAuth,
    logout,
  }
})
