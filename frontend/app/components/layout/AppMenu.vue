<script setup lang="ts">
import { ref } from 'vue';
import AppMenuItem from "./AppMenuItem.vue";
import appMenuData from "~/data/app-menu.json";

// Extract data from JSON (handling both Array and Default Export)
const rawData = Array.isArray(appMenuData) ? appMenuData : (appMenuData as any).default || [];

const model = ref(rawData.map((m: any) => ({
  ...m,
  label: m.label || m.title || 'Unknown',
  items: m.items || []
})));

// Safety check: if model is still empty, add a debug item
if (model.value.length === 0) {
  model.value = [
    { label: 'DEBUG: Menu Empty', items: [{ label: 'Check app-menu.json', icon: 'pi pi-exclamation-triangle', to: '/' }] }
  ];
}
</script>

<template>
  <nav class="layout-menu p-4">
    <ul class="list-none p-0 m-0">
      <template v-for="(item, i) in model" :key="item.label + i">
        <AppMenuItem :item="item" :index="i" :root="true" />
      </template>
    </ul>
  </nav>
</template>
