<script setup>
const { toggleMenu, isDarkTheme } = useLayout()
const config = useRuntimeConfig()
const appName = computed(() => config.public.appName)
</script>

<template>
  <header class="flex items-center justify-between h-16 bg-surface-0 dark:bg-surface-900 border-b border-surface-200 dark:border-surface-800 px-6 fixed top-0 w-full z-50 shadow-sm transition-colors duration-300">
    <div class="flex items-center gap-4">
      <Button 
        icon="pi pi-bars" 
        text 
        rounded 
        class="text-surface-600 hover:bg-surface-100 transition-colors h-10 w-10" 
        @click="toggleMenu" 
      />
      <NuxtLink to="/" class="flex items-center gap-2 no-underline">
        <svg width="35" height="40" viewBox="0 0 35 40" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M25.87 18.05L23.16 14.84L12.63 27.39L5.8 19.22L3.09 22.43L12.63 33.8L25.87 18.05Z" fill="var(--p-primary-color)" />
          <path d="M30.12 13L27.41 9.79L12.63 27.39L5.8 19.22L3.09 16L12.63 27.39L30.12 13Z" fill="var(--p-primary-color)" />
        </svg>
        <span class="text-xl font-bold text-surface-900 dark:text-surface-0 tracking-widest uppercase ml-1">{{ appName }}</span>
      </NuxtLink>
    </div>

    <div class="flex items-center gap-2">
      <ThemeSwitcher />
      <Button icon="pi pi-user" rounded class="bg-primary border-primary text-white h-9 w-9 p-0" />
      <Button icon="pi pi-calendar" text rounded severity="secondary" class="h-10 w-10 hidden sm:flex" />
      <Button icon="pi pi-envelope" text rounded severity="secondary" class="h-10 w-10 hidden sm:flex" />
      <Button icon="pi pi-power-off" text rounded severity="secondary" class="h-10 w-10" />
    </div>
  </header>
</template>
