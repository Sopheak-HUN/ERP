<script setup lang="ts">
const config = useRuntimeConfig()
const appName = computed(() => config.public.appName)
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-950 p-4">
    <div class="w-full max-w-[420px]">
      <div class="mb-6 text-center">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-50">
          {{ appName }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Enterprise Resource Planning
        </p>
      </div>

      <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-sm">
        <slot />
      </div>

      <p class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
        &copy; {{ new Date().getFullYear() }} {{ appName }}
      </p>
    </div>
  </div>
</template>
