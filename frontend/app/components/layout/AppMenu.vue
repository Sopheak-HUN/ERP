<script setup lang="ts">
import { useRoute } from 'vue-router'

import appMenu from '~/data/app-menu.json'

interface MenuItem {
  label: string
  icon: string
  to: string
  permission?: string
}

interface MenuModule {
  title: string
  items: MenuItem[]
}

const modules = ref<MenuModule[]>(appMenu as MenuModule[])

const route = useRoute()

function isActive(path: string) {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(path)
}
</script>

<template>
  <nav class="flex-1 overflow-y-auto p-4 space-y-6 text-sm">
    <div v-for="(module, mIdx) in modules" :key="mIdx">
      <h3 class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
        {{ module.title }}
      </h3>
      <ul class="space-y-1">
        <template v-for="(item, iIdx) in module.items" :key="iIdx">
          <li v-if="!item.permission || $can(item.permission)">
            <NuxtLink
              :to="item.to"
              class="group flex items-center gap-3 rounded-lg px-3 py-2 transition-colors"
              :class="[
                isActive(item.to)
                  ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400 font-medium'
                  : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
              ]"
            >
              <i :class="[item.icon, 'text-lg']" />
              <span>{{ item.label }}</span>
            </NuxtLink>
          </li>
        </template>
      </ul>
    </div>
  </nav>
</template>
