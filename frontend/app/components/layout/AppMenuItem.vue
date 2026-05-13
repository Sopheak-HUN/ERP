<script setup>
import { computed } from 'vue';
import AppMenuItem from "./AppMenuItem.vue";

const { layoutState } = useLayout()
const route = useRoute()

const props = defineProps({
  item: {
    type: Object,
    default: () => ({})
  },
  index: {
    type: Number,
    default: 0
  },
  root: {
    type: Boolean,
    default: true
  }
})

const isActive = computed(() => {
  if (!props.item.to) return false
  return route.path === props.item.to || route.path.startsWith(props.item.to + '/')
})

const itemClick = (event) => {
  if (props.item.disabled) {
    event.preventDefault()
    return
  }

  if (props.item.command) {
    props.item.command({ originalEvent: event, item: props.item })
  }

  if (!props.item.items) {
    layoutState.mobileMenuActive = false
  }
}
</script>

<template>
  <li :class="{ 'layout-root-menuitem': root, 'active-menuitem': isActive }" class="list-none">
    <!-- Root Label -->
    <div v-if="root && item.visible !== false" class="text-[12px] font-bold uppercase tracking-wider text-surface-900 dark:text-surface-0 mb-3 px-3 mt-6">
      {{ item.label }}
    </div>
    
    <!-- Link Item -->
    <NuxtLink
      v-if="item.to && !item.items && item.visible !== false"
      :to="item.to"
      @click="itemClick"
      class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition-all duration-200 no-underline group"
      :class="[
        isActive
          ? 'text-primary font-bold bg-primary-50/50 dark:bg-primary-900/10'
          : 'text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800'
      ]"
    >
      <i :class="[item.icon, 'text-lg transition-colors', isActive ? 'text-primary' : 'group-hover:text-surface-900']" />
      <span class="text-sm tracking-tight">{{ item.label }}</span>
    </NuxtLink>

    <!-- Submenu -->
    <Transition v-if="item.items && item.visible !== false" name="layout-submenu">
      <ul v-show="root ? true : isActive" class="list-none p-0 m-0 space-y-1 ml-2">
        <AppMenuItem v-for="(child, i) in item.items" :key="child.label + i" :item="child" :index="i" :root="false" />
      </ul>
    </Transition>
  </li>
</template>
