import { defineNuxtPlugin } from '#app'
import { useAuthStore } from '~/stores/auth'

export default defineNuxtPlugin((nuxtApp) => {
  const checkPermission = (permission: string | string[]) => {
    // We access the store lazily to avoid Pinia active instance issues during SSR init
    const authStore = useAuthStore()
    
    if (!authStore.user) return false
    
    // Automatically grant all permissions to super-admin
    if (authStore.user.roles?.includes('super-admin')) return true
    
    const perms = authStore.user.permissions || []
    if (Array.isArray(permission)) {
      return permission.some(p => perms.includes(p))
    }
    return perms.includes(permission)
  }

  const checkRole = (role: string | string[]) => {
    const authStore = useAuthStore()
    
    if (!authStore.user) return false
    
    const roles = authStore.user.roles || []
    if (Array.isArray(role)) {
      return role.some(r => roles.includes(r))
    }
    return roles.includes(role)
  }

  // Register v-can directive
  // Usage: <button v-can="'delete-users'">Delete</button>
  // Usage: <div v-can="['edit-posts', 'delete-posts']">...</div>
  nuxtApp.vueApp.directive('can', (el, binding) => {
    if (!checkPermission(binding.value)) {
      el.style.display = 'none'
    } else {
      el.style.display = ''
    }
  })

  // Register v-role directive
  // Usage: <div v-role="'manager'">...</div>
  nuxtApp.vueApp.directive('role', (el, binding) => {
    if (!checkRole(binding.value)) {
      el.style.display = 'none'
    } else {
      el.style.display = ''
    }
  })

  return {
    provide: {
      can: checkPermission,
      hasRole: checkRole
    }
  }
})
