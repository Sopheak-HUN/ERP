<script setup lang="ts">
import type { MenuItem } from 'primevue/menuitem'

const authStore = useAuthStore()
const router = useRouter()
const menu = ref()

const items = computed<MenuItem[]>(() => [
  {
    label: authStore.user?.email || 'Signed in',
    disabled: true,
  },
  { separator: true },
  {
    label: 'Profile settings',
    icon: 'pi pi-user',
    command: () => router.push('/profile'),
  },
  {
    label: 'Security (2FA)',
    icon: 'pi pi-shield',
    command: () => router.push('/auth/2fa'),
  },
  { separator: true },
  {
    label: 'Sign out',
    icon: 'pi pi-sign-out',
    command: async () => {
      await authStore.logout()
      router.push('/auth/login')
    },
  },
])

function toggle(event: MouseEvent) {
  menu.value?.toggle(event)
}

const initials = computed(() => {
  const name = authStore.user?.name || authStore.user?.email || 'U'
  return name.charAt(0).toUpperCase()
})
</script>

<template>
  <div>
    <button
      type="button"
      class="flex items-center gap-2 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
      aria-haspopup="true"
      aria-controls="user-menu"
      @click="toggle"
    >
      <Avatar
        :label="initials"
        shape="circle"
        size="normal"
        class="bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-100"
      />
    </button>
    <Menu
      id="user-menu"
      ref="menu"
      :model="items"
      :popup="true"
    />
  </div>
</template>
