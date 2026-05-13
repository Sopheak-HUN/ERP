<script setup lang="ts">
import { ref } from 'vue'

useHead({ title: 'Roles & Permissions | Identity & Access' })

// Mock data
const roles = ref([
  { id: 1, name: 'Super Admin', description: 'Full access to all system features and settings.', users_count: 2, permissions_count: 145, is_system: true },
  { id: 2, name: 'HR Manager', description: 'Manage employees, attendance, payroll, and leave requests.', users_count: 5, permissions_count: 32, is_system: false },
  { id: 3, name: 'Finance Admin', description: 'Access to financial records, journals, and chart of accounts.', users_count: 3, permissions_count: 45, is_system: false },
  { id: 4, name: 'Employee', description: 'Basic access to view own profile, submit leaves, and timesheets.', users_count: 124, permissions_count: 12, is_system: true },
])
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Roles & Permissions</h1>
        <p class="text-xs text-slate-500 font-medium">Define and manage access control for your organization.</p>
      </div>
      <Button icon="pi pi-plus" label="Create New Role" size="small" class="bg-sky-500 border-sky-500 text-white h-9 px-5 font-bold shadow-sm" />
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white border border-slate-200 p-4 rounded-lg shadow-sm">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Roles</span>
        <div class="text-2xl font-black text-slate-800 mt-1">04</div>
      </div>
      <div class="bg-white border border-slate-200 p-4 rounded-lg shadow-sm">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">System Defined</span>
        <div class="text-2xl font-black text-sky-500 mt-1">02</div>
      </div>
      <div class="bg-white border border-slate-200 p-4 rounded-lg shadow-sm">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Active Users</span>
        <div class="text-2xl font-black text-slate-800 mt-1">134</div>
      </div>
      <div class="bg-white border border-slate-200 p-4 rounded-lg shadow-sm">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Global Permissions</span>
        <div class="text-2xl font-black text-slate-800 mt-1">234</div>
      </div>
    </div>

    <!-- Cards Grid - Professional Style -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="role in roles" 
        :key="role.id" 
        class="bg-white border border-slate-200 rounded-xl overflow-hidden hover:border-sky-300 transition-all duration-300 group shadow-sm hover:shadow-md"
      >
        <div class="p-5 border-b border-slate-50">
          <div class="flex items-center justify-between mb-3">
            <div class="h-9 w-9 rounded-lg bg-sky-50 flex items-center justify-center text-sky-600">
              <i class="pi pi-shield text-lg"></i>
            </div>
            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
              <Button icon="pi pi-pencil" text rounded severity="secondary" size="small" class="h-7 w-7" />
              <Button v-if="!role.is_system" icon="pi pi-trash" text rounded severity="danger" size="small" class="h-7 w-7" />
            </div>
          </div>
          <div class="flex items-center gap-2 mb-1">
            <h3 class="font-bold text-slate-800 tracking-tight">{{ role.name }}</h3>
            <Tag v-if="role.is_system" value="System" class="bg-slate-100 text-slate-500 text-[9px] px-1.5 py-0 font-bold uppercase" />
          </div>
          <p class="text-[12px] text-slate-500 leading-relaxed line-clamp-2 h-9">
            {{ role.description }}
          </p>
        </div>
        <div class="px-5 py-3 bg-slate-50/50 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <i class="pi pi-users text-xs text-slate-400"></i>
            <span class="text-[11px] font-bold text-slate-600">{{ role.users_count }} Users</span>
          </div>
          <div class="flex items-center gap-2">
            <i class="pi pi-key text-xs text-slate-400"></i>
            <span class="text-[11px] font-bold text-slate-600">{{ role.permissions_count }} Permissions</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
