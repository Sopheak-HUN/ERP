<script setup lang="ts">
const config = useRuntimeConfig()
const appName = computed(() => config.public.appName)

const sidebarOpen = ref(true)
</script>

<template>
  <div class="flex h-screen w-screen overflow-hidden bg-gray-50 dark:bg-gray-900">
    <aside
      v-show="sidebarOpen"
      class="flex h-full w-64 flex-col border-r border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950"
    >
      <div class="flex h-14 items-center border-b border-gray-200 px-4 font-semibold dark:border-gray-800">
        {{ appName }}
      </div>
      <nav class="flex-1 overflow-y-auto p-4 text-sm text-gray-500 dark:text-gray-400">
        <p class="italic">
          Menu will populate once auth + RBAC ships.
        </p>
      </nav>
    </aside>

    <div class="flex h-full flex-1 flex-col overflow-hidden">
      <header
        class="flex h-14 shrink-0 items-center justify-between border-b border-gray-200 bg-white px-4 dark:border-gray-800 dark:bg-gray-950"
      >
        <Button
          icon="pi pi-bars"
          severity="secondary"
          text
          rounded
          aria-label="Toggle sidebar"
          @click="sidebarOpen = !sidebarOpen"
        />
        <div class="flex items-center gap-4">
          <ClientOnly>
            <ThemeSwitcher />
            <UserMenu />
            <template #fallback>
              <div class="flex items-center gap-4">
                <div class="w-24 h-8 rounded-lg bg-gray-200 dark:bg-gray-800 animate-pulse" />
                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-800 animate-pulse" />
              </div>
            </template>
          </ClientOnly>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
