<script setup lang="ts">
const authStore = useAuthStore()
const router = useRouter()
const menu = ref()

const items = computed(() => [
  {
    label: 'Profile',
    icon: 'pi pi-user',
    command: () => router.push('/profile'),
  },
  {
    label: 'Security',
    icon: 'pi pi-shield',
    command: () => router.push('/auth/2fa'),
  },
  {
    label: 'Settings',
    icon: 'pi pi-cog',
    command: () => {},
  },
  { separator: true },
  {
    label: 'Logout',
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
  <div class="user-menu">
    <button
      type="button"
      class="trigger-btn group flex items-center justify-center p-0.5 rounded-full border border-surface-200 dark:border-surface-800 transition-all hover:border-primary-500/40 hover:shadow-lg hover:shadow-primary-500/10 active:scale-95"
      @click="toggle"
    >
      <Avatar
        :label="initials"
        shape="circle"
        class="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 font-bold"
      />
    </button>
    
    <Menu
      ref="menu"
      :model="items"
      :popup="true"
      class="user-dropdown-menu p-1 border border-surface-200/60 dark:border-surface-800/60 bg-white/90 dark:bg-surface-950/90 backdrop-blur-xl shadow-2xl rounded-2xl mt-2 overflow-hidden"
    >
      <template #start>
        <div class="px-4 py-3 border-b border-surface-100 dark:border-surface-800/50 mb-1">
          <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-1">Account</p>
          <p class="text-sm font-bold text-surface-900 dark:text-surface-50 truncate">{{ authStore.user?.email }}</p>
        </div>
      </template>
      
      <template #item="{ item, props }">
        <a 
          v-bind="props.action" 
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group/item hover:bg-surface-100/50 dark:hover:bg-surface-800/50"
        >
          <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-100 dark:bg-surface-800 text-surface-500 group-hover/item:text-primary-500 group-hover/item:bg-primary-500/10 transition-colors">
            <i :class="item.icon" class="text-sm" />
          </div>
          <span class="text-sm font-medium text-surface-700 dark:text-surface-300 group-hover/item:text-surface-900 dark:group-hover/item:text-surface-100">
            {{ item.label }}
          </span>
        </a>
      </template>
    </Menu>
  </div>
</template>

<style scoped>
@reference "~/assets/css/main.css";

:deep(.p-menu) {
  min-width: 220px;
}

:deep(.p-menu-list) {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
</style>
