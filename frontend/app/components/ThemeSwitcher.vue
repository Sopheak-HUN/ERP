<script setup lang="ts">
const colorMode = useColorMode()
const menu = ref()

const themes: { label: string; value: string; icon: string }[] = [
  { label: 'Light', value: 'light', icon: 'pi pi-sun' },
  { label: 'Dark', value: 'dark', icon: 'pi pi-moon' },
  { label: 'System', value: 'system', icon: 'pi pi-desktop' }
]

const menuItems = computed(() => themes.map(theme => ({
  label: theme.label,
  icon: theme.icon,
  command: () => {
    colorMode.preference = theme.value
  },
  class: colorMode.preference === theme.value ? 'active-theme' : ''
})))

const currentThemeIcon = computed(() => {
  return themes.find(t => t.value === colorMode.preference)?.icon || 'pi pi-desktop'
})

const toggle = (event: Event) => {
  menu.value.toggle(event)
}
</script>

<template>
  <div class="theme-switcher">
    <button
      type="button"
      class="trigger-btn group flex items-center justify-center gap-2 h-9 px-3 rounded-full border transition-all duration-300 active:scale-95"
      @click="toggle"
    >
      <div class="flex items-center gap-2">
        <i :class="currentThemeIcon" class="text-base text-surface-600 dark:text-surface-300 group-hover:text-primary-500 transition-colors" />
        <i class="pi pi-chevron-down text-[8px] text-surface-400 group-hover:text-surface-600 dark:group-hover:text-surface-200 transition-colors" />
      </div>
    </button>

    <Menu
      ref="menu"
      :model="menuItems"
      :popup="true"
      class="theme-menu p-1 border border-surface-200/60 dark:border-surface-800/60 bg-white/90 dark:bg-surface-950/90 backdrop-blur-xl shadow-2xl rounded-2xl mt-2 overflow-hidden"
    >
      <template #item="{ item, props }">
        <a 
          v-bind="props.action" 
          class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group/item"
          :class="item.class === 'active-theme' ? 'bg-primary-500/10 dark:bg-primary-500/20' : 'hover:bg-surface-100/50 dark:hover:bg-surface-800/50'"
        >
          <div class="flex items-center gap-3">
            <div 
              class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
              :class="item.class === 'active-theme' ? 'bg-primary-500/20 text-primary-500' : 'bg-surface-100 dark:bg-surface-800 text-surface-500 group-hover/item:text-surface-900 dark:group-hover/item:text-surface-100'"
            >
              <i :class="[item.icon, item.class === 'active-theme' ? 'text-primary-500' : 'text-surface-500 group-hover/item:text-surface-900 dark:group-hover/item:text-surface-100']" />
            </div>
            <span 
              class="text-sm font-medium transition-colors"
              :class="item.class === 'active-theme' ? 'text-primary-700 dark:text-primary-400' : 'text-surface-600 dark:text-surface-400 group-hover/item:text-surface-900 dark:group-hover/item:text-surface-100'"
            >
              {{ item.label }}
            </span>
          </div>
          <div v-if="item.class === 'active-theme'" class="flex items-center">
             <i class="pi pi-check text-primary-500 text-[10px] animate-in zoom-in duration-300" />
          </div>
        </a>
      </template>
    </Menu>
  </div>
</template>

<style scoped>
@reference "~/assets/css/main.css";

.trigger-btn {
  @apply bg-white/80 dark:bg-surface-900/40 border-surface-200/80 dark:border-surface-800 shadow-sm backdrop-blur-sm transition-all duration-300;
}

.trigger-btn:hover {
  @apply border-primary-500/40 bg-white dark:bg-surface-900 shadow-[0_0_15px_-3px_rgba(var(--p-primary-500-rgb),0.15)];
  transform: translateY(-1px);
}

:deep(.p-menu) {
  min-width: 170px;
  border: 1px solid var(--p-surface-200);
}

.dark :deep(.p-menu) {
  border: 1px solid var(--p-surface-800);
}

:deep(.p-menu-list) {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

/* Custom animation for the checkmark */
@keyframes zoom-in {
  from { opacity: 0; transform: scale(0.5); }
  to { opacity: 1; transform: scale(1); }
}

.animate-in {
  animation-fill-mode: both;
}

.zoom-in {
  animation-name: zoom-in;
}

.duration-300 {
  animation-duration: 300ms;
}
</style>
