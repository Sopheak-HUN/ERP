<script setup lang="ts">
import { ref } from 'vue'

useHead({ title: 'Roles & Permissions | Identity & Access' })

// Mock data
const roles = ref([
  {
    id: 1,
    name: 'Super Admin',
    description: 'Full access to all system features and settings.',
    users_count: 2,
    permissions_count: 145,
    is_system: true
  },
  {
    id: 2,
    name: 'HR Manager',
    description: 'Manage employees, attendance, payroll, and leave requests.',
    users_count: 5,
    permissions_count: 32,
    is_system: false
  },
  {
    id: 3,
    name: 'Finance Admin',
    description: 'Access to financial records, journals, and chart of accounts.',
    users_count: 3,
    permissions_count: 45,
    is_system: false
  },
  {
    id: 4,
    name: 'Employee',
    description: 'Basic access to view own profile, submit leaves, and timesheets.',
    users_count: 124,
    permissions_count: 12,
    is_system: true
  }
])
</script>

<template>
  <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Roles & Permissions</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Define access control roles and their associated system permissions.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <Button icon="pi pi-plus" label="Create Role" severity="primary" class="rounded-xl" />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Role Cards -->
      <div 
        v-for="role in roles" 
        :key="role.id" 
        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 hover:shadow-lg transition-shadow duration-300 relative group"
      >
        <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
          <Button icon="pi pi-pencil" text rounded severity="secondary" size="small" />
          <Button v-if="!role.is_system" icon="pi pi-trash" text rounded severity="danger" size="small" />
        </div>
        
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600 dark:text-primary-400">
            <i class="pi pi-shield text-xl"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
              {{ role.name }}
              <Tag v-if="role.is_system" value="System" severity="secondary" class="text-[10px] px-2 py-0" />
            </h3>
          </div>
        </div>
        
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 line-clamp-2 h-10">
          {{ role.description }}
        </p>
        
        <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-800 pt-4">
          <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <i class="pi pi-users"></i>
            <span>{{ role.users_count }} Users</span>
          </div>
          <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <i class="pi pi-key"></i>
            <span>{{ role.permissions_count }} Permissions</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
