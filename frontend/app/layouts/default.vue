<script setup lang="ts">
import AppMenu from "~/components/layout/AppMenu.vue";

const config = useRuntimeConfig();
const appName = computed(() => config.public.appName);

const sidebarOpen = ref(true);
</script>

<template>
  <div
    class="flex h-screen w-screen overflow-hidden bg-surface-50 dark:bg-surface-950 text-surface-900 font-sans"
  >
    <!-- Refined Enterprise Sidebar -->
    <aside
      v-show="sidebarOpen"
      class="flex h-full w-64 flex-col border-r border-surface-200 bg-surface-0 dark:border-surface-800 dark:bg-surface-900 z-30 transition-all duration-300"
    >
      <div
        class="flex h-16 items-center border-b border-surface-100 px-6 font-black text-xl tracking-tighter text-primary dark:border-surface-800"
      >
        {{ appName }}
      </div>
      <div class="flex-1 overflow-y-auto pt-4 custom-scrollbar">
        <AppMenu />
      </div>
    </aside>

    <div class="flex h-full flex-1 flex-col overflow-hidden relative">
      <!-- Elite Top Header -->
      <header
        class="flex h-16 shrink-0 items-center justify-between border-b border-surface-200 bg-surface-0 px-8 dark:border-surface-800 dark:bg-surface-900 z-20 shadow-sm shadow-surface-200/50 dark:shadow-none"
      >
        <div class="flex items-center gap-6">
          <Button
            icon="pi pi-bars"
            severity="secondary"
            text
            rounded
            class="hover:bg-primary-50 text-surface-400 hover:text-primary transition-all"
            @click="sidebarOpen = !sidebarOpen"
          />
          <div class="flex items-center gap-2 text-sm text-surface-400">
            <i class="pi pi-home text-xs"></i>
            <i class="pi pi-chevron-right text-[10px]"></i>
            <span class="text-surface-900 font-bold tracking-tight">Identity & Access</span>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <div class="flex items-center gap-3 bg-surface-50 dark:bg-surface-800 p-1.5 rounded-full border border-surface-200 dark:border-surface-700">
            <ClientOnly>
              <ThemeSwitcher />
              <div class="w-px h-4 bg-surface-200 dark:bg-surface-700 mx-1" />
              <UserMenu />
            </ClientOnly>
          </div>
        </div>
      </header>

      <!-- The 'Sheet' Workspace -->
      <main class="flex-1 overflow-y-auto p-5">
        <div class="min-h-full bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl shadow-xl shadow-surface-200/30 dark:shadow-none overflow-hidden transition-all duration-500">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>
