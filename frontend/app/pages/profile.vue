<script setup lang="ts">
import { computed, ref } from 'vue'
import { ApiError } from '~/composables/useApi'
import EnableTwoFactorDialog from '~/components/auth/EnableTwoFactorDialog.vue'

useHead({ title: 'Profile | ERP System' })

const authStore = useAuthStore()
const api = useApi()
const user = computed(() => authStore.user)

const disableDialogVisible = ref(false)
const enableDialogVisible = ref(false)
const disablePassword = ref('')
const isDisabling = ref(false)
const disableError = ref('')

function openDisableDialog() {
  disablePassword.value = ''
  disableError.value = ''
  disableDialogVisible.value = true
}

async function confirmDisable() {
  if (!disablePassword.value) {
    disableError.value = 'Password is required'
    return
  }

  isDisabling.value = true
  disableError.value = ''
  try {
    await api('/auth/two-factor', { 
      method: 'DELETE',
      body: { password: disablePassword.value }
    })
    await authStore.fetchUser()
    disableDialogVisible.value = false
  } catch (err: any) {
    disableError.value = err instanceof ApiError ? err.message : 'Failed to disable 2FA.'
  } finally {
    isDisabling.value = false
  }
}

const joinDate = computed(() => {
  if (!user.value?.created_at) return 'Unknown'
  return new Date(user.value.created_at).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const initials = computed(() => {
  const name = user.value?.name || user.value?.email || 'U'
  return name.charAt(0).toUpperCase()
})
</script>

<template>
  <div class="max-w-4xl mx-auto py-8 lg:py-12 px-4 sm:px-6">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Account Settings</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-xl">
          Manage your personal information, security preferences, and account settings.
        </p>
      </div>
    </div>

    <div class="space-y-8">
      
      <!-- Profile Card -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-center sm:items-start gap-6">
          <div class="w-24 h-24 rounded-full bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-3xl font-bold shrink-0">
            {{ initials }}
          </div>
          <div class="flex-1 text-center sm:text-left">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ user?.name || 'User' }}</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ user?.email }}</p>
            
            <div class="mt-6 flex flex-wrap justify-center sm:justify-start gap-3">
              <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300">
                <i class="pi pi-calendar text-primary-500"></i>
                Joined {{ joinDate }}
              </span>
              <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                Active Account
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Security Section -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-5 bg-gray-50/50 dark:bg-gray-800/20">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="pi pi-shield text-primary-500"></i>
            Security
          </h3>
        </div>
        
        <div class="p-6 sm:p-8">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1">
              <h4 class="text-base font-medium text-gray-900 dark:text-white flex items-center gap-2">
                Two-Factor Authentication
                <i v-if="user?.has_two_factor" class="pi pi-check-circle text-emerald-500"></i>
                <Badge v-else value="Recommended" severity="warn" class="text-xs" />
              </h4>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                {{ user?.has_two_factor 
                  ? 'Your account is currently protected with two-factor authentication. This provides an extra layer of security.' 
                  : 'Add an additional layer of security to your account by requiring more than just a password to sign in.' }}
              </p>
              <Message v-if="disableError" severity="error" variant="simple" size="small" class="mt-2">{{ disableError }}</Message>
            </div>
            
            <div class="shrink-0">
              <Button 
                v-if="user?.has_two_factor"
                label="Disable 2FA" 
                icon="pi pi-times"
                severity="danger"
                variant="outlined"
                class="w-full md:w-auto"
                @click="openDisableDialog"
              />
              <Button 
                v-else
                label="Enable 2FA" 
                icon="pi pi-lock"
                severity="primary"
                class="w-full md:w-auto"
                @click="enableDialogVisible = true"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Personal Details Section -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-5 bg-gray-50/50 dark:bg-gray-800/20 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="pi pi-user text-primary-500"></i>
            Personal Details
          </h3>
          <Button label="Edit" icon="pi pi-pencil" severity="secondary" size="small" text />
        </div>
        
        <div class="p-6 sm:p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <div class="flex flex-col gap-2">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
              <InputText 
                :value="user?.name" 
                readonly 
                class="w-full bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 shadow-none cursor-not-allowed" 
              />
            </div>
            
            <div class="flex flex-col gap-2">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
              <InputText 
                :value="user?.email" 
                readonly 
                class="w-full bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 shadow-none cursor-not-allowed" 
              />
            </div>
          </div>
        </div>
      </div>

    </div>
    
    <Dialog 
      v-model:visible="disableDialogVisible" 
      modal 
      header="Disable 2FA" 
      :style="{ width: '400px' }"
    >
      <div class="flex flex-col gap-4 py-4">
        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
          Please enter your password to confirm you want to disable two-factor authentication.
        </p>
        <div class="flex flex-col gap-2">
          <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-200">Password</label>
          <Password 
            id="password" 
            v-model="disablePassword" 
            :feedback="false" 
            toggle-mask 
            placeholder="****************"
            input-class="w-full"
            class="w-full"
            @keyup.enter="confirmDisable"
            :invalid="!!disableError"
            :input-props="{ autocomplete: 'new-password' }"
          />
          <small v-if="disableError" class="text-red-500 font-medium">{{ disableError }}</small>
          
          <Message severity="warn" variant="simple" class="mt-2 text-sm bg-amber-50/50 dark:bg-amber-900/20 border border-amber-200/50 dark:border-amber-800/50 p-3 rounded-lg">
            <div class="flex items-start gap-2">
              <i class="pi pi-exclamation-triangle text-amber-500 mt-0.5"></i>
              <span class="text-amber-800 dark:text-amber-200 font-medium leading-tight">
                Warning: Disabling 2FA removes an essential layer of security. Your account will only be protected by your password.
              </span>
            </div>
          </Message>
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" text severity="secondary" @click="disableDialogVisible = false" />
        <Button label="Confirm" icon="pi pi-check" severity="danger" :loading="isDisabling" @click="confirmDisable" />
      </template>
    </Dialog>
    <EnableTwoFactorDialog v-model:visible="enableDialogVisible" />
  </div>
</template>

<style scoped>
@reference "~/assets/css/main.css";
</style>
