<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  title?: string
  placeholder?: string
  showAdd?: boolean
  addLabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  title: '',
  placeholder: 'Search...',
  showAdd: true,
  addLabel: 'Add New'
})

const emit = defineEmits(['search', 'add', 'export', 'filter-toggle'])

const searchValue = ref('')
const showFilters = ref(false)
const exportMenu = ref()

const exportOptions = ref([
  { label: 'Export as CSV', icon: 'pi pi-file', command: () => emit('export', 'csv') },
  { label: 'Export as Excel', icon: 'pi pi-file-excel', command: () => emit('export', 'excel') },
  { label: 'Export as PDF', icon: 'pi pi-file-pdf', command: () => emit('export', 'pdf') }
])

const toggleExport = (event: any) => {
  exportMenu.value.toggle(event)
}

const toggleFilters = () => {
  showFilters.value = !showFilters.value
  emit('filter-toggle', showFilters.value)
}

const onSearch = () => {
  emit('search', searchValue.value)
}
</script>

<template>
  <div class="bg-gray-50/30 dark:bg-gray-800/20 border-b border-gray-100 dark:border-gray-800">
    <!-- Main Toolbar -->
    <div class="p-6 flex flex-col md:flex-row justify-between items-center gap-6">
      <div class="w-full md:w-auto flex items-center gap-4">
        <span class="relative w-full">
          <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 z-10" />
          <InputText 
            v-model="searchValue" 
            :placeholder="placeholder" 
            @input="onSearch"
            class="w-full md:w-[28rem] rounded-2xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 py-3 pl-12 focus:ring-4 focus:ring-primary-500/10 transition-all" 
          />
        </span>
      </div>

      <div class="flex items-center gap-3 w-full md:w-auto justify-end">
        <Button 
          icon="pi pi-filter" 
          :label="showFilters ? 'Hide Filters' : 'Filters'" 
          :text="!showFilters"
          :severity="showFilters ? 'primary' : 'secondary'"
          class="rounded-xl px-4 border border-gray-200 dark:border-gray-700 font-semibold transition-all" 
          @click="toggleFilters"
        />
        
        <Button 
          type="button" 
          icon="pi pi-download" 
          label="Export" 
          text 
          severity="secondary" 
          class="rounded-xl px-4 border border-gray-200 dark:border-gray-700 font-semibold" 
          @click="toggleExport"
          aria-haspopup="true" 
          aria-controls="overlay_menu"
        />
        <Menu 
          ref="exportMenu" 
          id="overlay_menu" 
          :model="exportOptions" 
          :popup="true" 
          class="rounded-2xl border-none shadow-2xl shadow-gray-500/20 dark:shadow-none bg-white dark:bg-gray-800 p-2"
        >
          <template #item="{ item, props }">
            <a v-bind="props.action" class="flex items-center rounded-xl py-3 px-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
              <i :class="[item.icon, 'text-gray-400 mr-3 group-hover:text-primary-500 transition-colors']" />
              <span class="font-semibold text-gray-700 dark:text-gray-300">{{ item.label }}</span>
            </a>
          </template>
        </Menu>
      </div>
    </div>

    <!-- Collapsible Filter Panel -->
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform -translate-y-4 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform -translate-y-4 opacity-0"
    >
      <div v-if="showFilters" class="px-6 pb-6 pt-2 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900/50">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <!-- Slot for external filters -->
          <slot name="filters"></slot>
          
          <div class="flex items-end">
            <Button label="Reset Filters" icon="pi pi-refresh" text severity="danger" size="small" class="font-bold" />
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
/* Only keep non-Tailwind custom styles here if needed */
</style>
