<script setup lang="ts">
import AppTopbar from "~/components/layout/AppTopbar.vue";
import AppMenu from "~/components/layout/AppMenu.vue";

const { layoutState } = useLayout();
</script>

<template>
  <div class="layout-wrapper min-h-screen bg-surface-50 dark:bg-surface-950 font-sans transition-colors duration-300">
    <AppTopbar />

    <!-- Sakai Sidebar -->
    <aside
      class="fixed top-16 left-0 h-[calc(100vh-4rem)] w-72 bg-surface-0 dark:bg-surface-900 border-r border-surface-200 dark:border-surface-800 z-40 overflow-y-auto transition-all duration-300"
      :class="[layoutState.staticMenuInactive ? 'translate-x-[-100%] md:translate-x-0 md:w-0 overflow-hidden' : 'translate-x-0 w-72']"
    >
      <AppMenu />
    </aside>

    <!-- Main Content Area -->
    <div 
      class="transition-all duration-300 pt-16 min-h-screen flex flex-col"
      :class="[layoutState.staticMenuInactive ? 'ml-0' : 'md:ml-72']"
    >
      <main class="flex-1 p-6 lg:p-8">
        <slot />
      </main>
      
      <footer class="py-4 px-8 border-t border-surface-200 dark:border-surface-800 bg-surface-0 dark:bg-surface-900 text-center text-xs text-surface-500">
        &copy; {{ new Date().getFullYear() }} Antigravity ERP System. All rights reserved.
      </footer>
    </div>

    <!-- Mobile Mask -->
    <div 
      v-if="layoutState.mobileMenuActive"
      class="fixed inset-0 bg-black/20 z-30 md:hidden animate-fadein"
      @click="layoutState.mobileMenuActive = false"
    />
  </div>
</template>

<style>
/* Sakai Transitions */
.layout-submenu-enter-active,
.layout-submenu-leave-active {
  transition: all 0.15s ease-in-out;
  max-height: 500px;
  overflow: hidden;
}

.layout-submenu-enter-from,
.layout-submenu-leave-to {
  max-height: 0;
  opacity: 0;
}

/* Custom Scrollbar for Sidebar */
.layout-sidebar::-webkit-scrollbar {
  width: 6px;
}
.layout-sidebar::-webkit-scrollbar-thumb {
  background: var(--p-surface-200);
  border-radius: 10px;
}
</style>
