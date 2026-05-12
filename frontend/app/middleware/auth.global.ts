import type { RouteLocationNormalized } from 'vue-router'

export default defineNuxtRouteMiddleware(async (to: RouteLocationNormalized) => {
  const authStore = useAuthStore()

  // Routes reachable without an authenticated session. 2FA setup lives behind
  // auth (it's a logged-in user enabling 2FA); the 2FA challenge during login
  // is handled inline on /auth/login.
  const publicRoutes = ['/auth/login', '/auth/forgot-password', '/auth/reset-password']
  const isPublic = publicRoutes.some(path => to.path.startsWith(path))

  // If we have a token but no user, try to fetch the user
  if (authStore.token && !authStore.user) {
    await authStore.fetchUser()
  }

  // Redirect unauthenticated users to login
  if (!authStore.isAuthenticated && !isPublic) {
    return navigateTo({
      path: '/auth/login',
      query: { redirect: to.fullPath }
    })
  }

  // Redirect authenticated users away from public routes (like login)
  if (authStore.isAuthenticated && isPublic) {
    return navigateTo('/')
  }
})
