<script setup lang="ts">
import { ref } from 'vue'

useHead({ title: 'User Management | Identity & Access' })

// Mock data
const users = ref([
  { id: 1, name: 'Sopheak Admin', email: 'sopheak@example.com', role: 'Super Admin', joined: '2023-01-15', status: 'Active' },
  { id: 2, name: 'John Doe', email: 'john.doe@example.com', role: 'HR Manager', joined: '2023-03-22', status: 'Active' },
  { id: 3, name: 'Alice Smith', email: 'alice.smith@example.com', role: 'Sales Representative', joined: '2023-05-10', status: 'Inactive' },
  { id: 4, name: 'Michael Brown', email: 'michael.brown@example.com', role: 'Finance Admin', joined: '2023-06-05', status: 'Active' },
])

const filters = ref({
  global: { value: null, matchMode: 'contains' }
})
</script>

<template>
  <div class="bg-card border border-surface-border rounded-lg shadow-sm overflow-hidden min-h-full flex flex-col">
    <!-- Action Header -->
    <div class="px-6 py-5 border-b border-surface-100 flex items-center justify-between bg-surface-50/50">
      <div>
        <h1 class="text-xl font-extrabold text-surface-900 tracking-tight leading-none mb-1.5">User Management</h1>
        <p class="text-[12px] text-surface-500 font-medium">Manage your team members and their account permissions.</p>
      </div>
      <div class="flex items-center gap-2">
        <Button icon="pi pi-refresh" outlined severity="secondary" size="small" class="h-8 w-8 !p-0" />
        <Button icon="pi pi-plus" label="Add User" size="small" class="h-8 px-4 font-bold" />
      </div>
    </div>

    <!-- Integrated Toolbar -->
    <div class="px-6 py-3 border-b border-surface-50 flex items-center justify-between bg-surface-50/30">
      <div class="flex items-center gap-3">
        <div class="relative group">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-surface-400 text-[10px] group-focus-within:text-primary transition-colors" />
          <InputText v-model="filters['global'].value" placeholder="Quick Search..." class="pl-8 h-8 text-[12px] w-56 bg-white dark:bg-surface-950" />
        </div>
        <Button icon="pi pi-filter" label="Filters" outlined severity="secondary" class="h-8 px-3 text-[11px] font-bold" />
      </div>
      <div class="flex items-center gap-2">
        <Button icon="pi pi-download" label="Export Data" text severity="secondary" class="h-8 px-3 text-[11px] font-bold" />
      </div>
    </div>

    <!-- Professional Data Table -->
    <div class="flex-1">
      <DataTable 
        :value="users" 
        class="p-datatable-sm"
        striped-rows
        :pt="{
          thead: 'bg-transparent',
          column: {
            headerCell: 'text-surface-500 font-bold text-[10px] py-4 bg-transparent px-6 uppercase tracking-[0.1em] border-b border-primary-100 dark:border-primary-900',
            bodyCell: 'py-2.5 border-b border-surface-50 px-6 text-[13px] text-surface-700'
          }
        }"
      >
        <Column field="name" header="User Member" sortable>
          <template #body="slotProps">
            <div class="flex items-center gap-3">
              <Avatar :label="slotProps.data.name.charAt(0)" shape="circle" class="bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 font-bold text-[10px] w-6 h-6 border border-primary-200 dark:border-primary-800" />
              <span class="font-bold text-surface-900 tracking-tight">{{ slotProps.data.name }}</span>
            </div>
          </template>
        </Column>
        
        <Column field="email" header="Email Address"></Column>
        
        <Column field="role" header="Security Role">
          <template #body="slotProps">
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-surface-100 text-surface-600 border border-surface-200 uppercase tracking-wider">
              {{ slotProps.data.role }}
            </span>
          </template>
        </Column>
        
        <Column field="status" header="Account Status" sortable>
          <template #body="slotProps">
            <div class="flex items-center gap-1.5">
              <span :class="['w-1.5 h-1.5 rounded-full', slotProps.data.status === 'Active' ? 'bg-green-500' : 'bg-red-500']"></span>
              <span :class="['text-[11px] font-bold uppercase tracking-wide', slotProps.data.status === 'Active' ? 'text-green-600' : 'text-red-600']">
                {{ slotProps.data.status }}
              </span>
            </div>
          </template>
        </Column>
        
        <Column field="joined" header="Joined Date"></Column>
        
        <Column header="Actions" class="text-right" style="width: 100px">
          <template #body>
            <div class="flex justify-end gap-1">
              <Button icon="pi pi-pencil" text rounded severity="secondary" size="small" class="h-8 w-8 text-surface-400 hover:text-primary" />
              <Button icon="pi pi-trash" text rounded severity="danger" size="small" class="h-8 w-8 text-surface-400" />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Action Footer -->
    <div class="px-6 py-4 border-t border-surface-100 bg-surface-50/30 flex items-center justify-between">
      <span class="text-[10px] text-surface-400 font-bold uppercase tracking-[0.1em]">Showing 4 of 24 Users</span>
      <div class="flex items-center gap-1">
        <Button icon="pi pi-chevron-left" text size="small" disabled class="h-8 w-8 !p-0" />
        <Button label="1" size="small" class="h-8 w-8 !p-0 font-bold" />
        <Button label="2" text size="small" class="h-8 w-8 !p-0 text-surface-400 font-bold hover:text-primary" />
        <Button icon="pi pi-chevron-right" text size="small" class="h-8 w-8 !p-0" />
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Mapping Tailwind-like semantic classes to PrimeVue tokens */
.bg-card { background-color: var(--p-content-background); }
.border-surface-border { border-color: var(--p-content-border-color); }
.text-primary { color: var(--p-primary-color); }
.bg-primary-100 { background-color: var(--p-primary-100); }
.text-primary-700 { color: var(--p-primary-700); }
.bg-surface-50 { background-color: var(--p-surface-50); }
.bg-surface-100 { background-color: var(--p-surface-100); }
.border-surface-100 { border-color: var(--p-surface-100); }
.border-surface-200 { border-color: var(--p-surface-200); }
.text-surface-400 { color: var(--p-surface-400); }
.text-surface-500 { color: var(--p-surface-500); }
.text-surface-600 { color: var(--p-surface-600); }
.text-surface-700 { color: var(--p-surface-700); }
.text-surface-900 { color: var(--p-surface-900); }
</style>
