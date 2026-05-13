<script setup lang="ts">
import { ref } from 'vue'

useHead({ title: 'Users | Identity & Access' })

// Mock data for UI design
const users = ref([
  {
    id: 1,
    name: 'Sopheak Admin',
    email: 'sopheak@example.com',
    role: 'Super Admin',
    status: 'Active',
    twoFactor: true,
    joined: '2023-01-15',
    lastActive: '2 mins ago'
  },
  {
    id: 2,
    name: 'John Doe',
    email: 'john.doe@example.com',
    role: 'HR Manager',
    status: 'Active',
    twoFactor: false,
    joined: '2023-03-22',
    lastActive: '5 hours ago'
  },
  {
    id: 3,
    name: 'Alice Smith',
    email: 'alice.smith@example.com',
    role: 'Sales Representative',
    status: 'Inactive',
    twoFactor: false,
    joined: '2023-05-10',
    lastActive: '3 days ago'
  },
  {
    id: 4,
    name: 'Michael Brown',
    email: 'michael.brown@example.com',
    role: 'Finance Admin',
    status: 'Active',
    twoFactor: true,
    joined: '2023-06-05',
    lastActive: 'Just now'
  }
])

const filters = ref({
  global: { value: null, matchMode: 'contains' }
})

const selectedUsers = ref([])

const getStatusSeverity = (status: string) => {
  switch (status.toLowerCase()) {
    case 'active': return 'success'
    case 'inactive': return 'danger'
    case 'pending': return 'warn'
    default: return 'info'
  }
}


</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div>
        <div class="flex items-center gap-2 text-primary-600 dark:text-primary-400 font-semibold text-sm mb-2 uppercase tracking-widest">
          <i class="pi pi-shield text-xs"></i>
          Identity & Access
        </div>
        <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
          User Management
        </h1>
        <p class="mt-2 text-lg text-gray-500 dark:text-gray-400 max-w-2xl">
          Control access, manage permissions, and track user activity across your organization.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <Button 
          icon="pi pi-user-plus" 
          label="Add New User" 
          severity="primary" 
          class="rounded-2xl px-6 py-3 font-bold shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 transform hover:-translate-y-0.5 transition-all duration-200" 
        />
      </div>
    </div>



    <!-- Main Content Table -->
    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[2rem] shadow-xl shadow-gray-200/50 dark:shadow-none overflow-hidden">
      <DataTable 
        :value="users" 
        v-model:selection="selectedUsers" 
        v-model:filters="filters" 
        dataKey="id" 
        paginator 
        :rows="10" 
        :rowsPerPageOptions="[5, 10, 20, 50]" 
        responsiveLayout="scroll"
        :pt="{
          header: 'p-0 border-none bg-transparent',
          thead: 'bg-gray-50/50 dark:bg-gray-800/40',
          column: {
            headerCell: 'text-gray-400 dark:text-gray-500 font-bold text-[11px] uppercase tracking-[0.1em] py-5 border-b border-gray-100 dark:border-gray-800',
            bodyCell: 'py-5 border-b border-gray-50 dark:border-gray-800/50 px-6'
          }
        }"
      >
        <template #header>
          <CommonDataTableToolbar
            placeholder="Search by name, email or role..."
            @search="(val) => filters['global'].value = val"
            @export="(type) => console.log('Exporting as', type)"
          >
            <template #filters>
              <div class="flex flex-col gap-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">Security Role</label>
                <Dropdown placeholder="All Roles" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900" />
              </div>
              <div class="flex flex-col gap-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">Account Status</label>
                <Dropdown placeholder="All Status" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900" />
              </div>
              <div class="flex flex-col gap-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">2FA Verification</label>
                <Dropdown placeholder="Any" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900" />
              </div>
            </template>
          </CommonDataTableToolbar>
        </template>
        
        <template #empty>
          <div class="flex flex-col items-center justify-center p-8 text-gray-500">
            <i class="pi pi-users text-4xl mb-4 text-gray-400"></i>
            <p>No users found.</p>
          </div>
        </template>

        <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
        
        <Column field="name" header="User Member" sortable>
          <template #body="slotProps">
            <div class="flex items-center gap-4">
              <div class="relative">
                <Avatar 
                  :label="slotProps.data.name.charAt(0)" 
                  class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 text-gray-700 dark:text-gray-300 font-bold shadow-inner" 
                  shape="circle" 
                  size="large" 
                />
                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white dark:border-gray-900 bg-green-500"></div>
              </div>
              <div>
                <div class="font-bold text-gray-900 dark:text-white text-base">{{ slotProps.data.name }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                  {{ slotProps.data.email }}
                  <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                  {{ slotProps.data.lastActive }}
                </div>
              </div>
            </div>
          </template>
        </Column>

        <Column field="role" header="Security Role" sortable>
          <template #body="slotProps">
            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-500/20">
              {{ slotProps.data.role }}
            </span>
          </template>
        </Column>

        <Column field="status" header="Account Status" sortable>
          <template #body="slotProps">
            <div class="flex items-center gap-2">
              <div :class="['w-2 h-2 rounded-full', slotProps.data.status === 'Active' ? 'bg-green-500 animate-pulse' : 'bg-red-500']"></div>
              <span :class="['text-sm font-bold uppercase tracking-wider', slotProps.data.status === 'Active' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
                {{ slotProps.data.status }}
              </span>
            </div>
          </template>
        </Column>

        <Column field="twoFactor" header="2FA Security" sortable>
          <template #body="slotProps">
            <div class="flex items-center gap-2">
              <i :class="['pi', slotProps.data.twoFactor ? 'pi-shield text-green-500' : 'pi-shield text-gray-300 dark:text-gray-600', 'text-lg']"></i>
              <span :class="['text-xs font-bold uppercase', slotProps.data.twoFactor ? 'text-green-600' : 'text-gray-400']">
                {{ slotProps.data.twoFactor ? 'Verified' : 'Unprotected' }}
              </span>
            </div>
          </template>
        </Column>

        <Column field="joined" header="Joined Date" sortable>
          <template #body="slotProps">
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ slotProps.data.joined }}</div>
          </template>
        </Column>

        <Column header="Actions" :exportable="false" style="min-width:8rem">
          <template #body="slotProps">
            <div class="flex items-center gap-2">
              <Button icon="pi pi-pencil" rounded text severity="secondary" v-tooltip="'Edit User'" class="w-10 h-10 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" />
              <Button icon="pi pi-trash" rounded text severity="danger" v-tooltip="'Delete User'" class="w-10 h-10 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors" />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>
