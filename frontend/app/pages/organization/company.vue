<script setup lang="ts">
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import { ApiError } from '~/composables/useApi'
import { ref, onMounted } from 'vue'

useHead({ title: 'Company Settings | ERP System' })

interface CompanyData {
  id?: number
  name: string
  logo_path: string | null
  registration_number: string | null
  tax_id: string | null
  email: string | null
  phone: string | null
  address: string | null
  website: string | null
  currency: string
  timezone: string
}

const api = useApi()

const isLoading = ref(true)
const isSaving = ref(false)
const isUploadingLogo = ref(false)
const saveSuccess = ref('')
const saveError = ref('')

const schema = toTypedSchema(z.object({
  name: z.string().min(2, 'Name must be at least 2 characters'),
  registration_number: z.string().nullable(),
  tax_id: z.string().nullable(),
  email: z.string().email('Invalid email').nullable().or(z.literal('')),
  phone: z.string().nullable(),
  website: z.string().url('Invalid URL').nullable().or(z.literal('')),
  address: z.string().nullable(),
  currency: z.string().length(3),
  timezone: z.string(),
}))

const form = useForm({
  validationSchema: schema,
  initialValues: {
    name: '',
    logo_path: null as string | null,
    registration_number: '',
    tax_id: '',
    email: '',
    phone: '',
    website: '',
    address: '',
    currency: 'USD',
    timezone: 'UTC',
  },
})

const [name, nameAttrs] = form.defineField('name')
const [registration_number, regAttrs] = form.defineField('registration_number')
const [tax_id, taxAttrs] = form.defineField('tax_id')
const [email, emailAttrs] = form.defineField('email')
const [phone, phoneAttrs] = form.defineField('phone')
const [website, websiteAttrs] = form.defineField('website')
const [address, addressAttrs] = form.defineField('address')
const [currency, currencyAttrs] = form.defineField('currency')
const [timezone, timezoneAttrs] = form.defineField('timezone')

onMounted(async () => {
  try {
    const data = await api<CompanyData>('/organization/company')
    form.setValues({
      name: data.name,
      logo_path: data.logo_path || null,
      registration_number: data.registration_number || '',
      tax_id: data.tax_id || '',
      email: data.email || '',
      phone: data.phone || '',
      website: data.website || '',
      address: data.address || '',
      currency: data.currency || 'USD',
      timezone: data.timezone || 'UTC',
    })
  } catch (err: any) {
    if (err.status !== 404) {
      saveError.value = 'Failed to load company details.'
    }
  } finally {
    isLoading.value = false
  }
})

const onSubmit = form.handleSubmit(async (values) => {
  isSaving.value = true
  saveError.value = ''
  saveSuccess.value = ''

  try {
    await api('/organization/company', {
      method: 'PATCH',
      body: values,
    })
    saveSuccess.value = 'Company settings saved successfully.'
  } catch (err: any) {
    saveError.value = err instanceof ApiError ? err.message : 'Failed to save settings.'
  } finally {
    isSaving.value = false
  }
})

async function onLogoChange(event: Event) {
  const target = event.target as HTMLInputElement
  if (!target.files?.length) return

  const file = target.files[0]
  const formData = new FormData()
  formData.append('logo', file)

  isUploadingLogo.value = true
  saveError.value = ''
  saveSuccess.value = ''
  try {
    const res = await api<{logo_path: string}>('/organization/company/logo', {
      method: 'POST',
      body: formData,
    })
    form.setFieldValue('logo_path', res.logo_path)
    saveSuccess.value = 'Logo uploaded successfully.'
  } catch (err: any) {
    saveError.value = err instanceof ApiError ? err.message : 'Failed to upload logo.'
  } finally {
    isUploadingLogo.value = false
    target.value = '' // reset input
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">Company Settings</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Manage your organization's core profile and contact information.
        </p>
      </div>
    </div>

    <div v-if="isLoading" class="flex justify-center py-20">
      <i class="pi pi-spinner pi-spin text-3xl text-primary-500" />
    </div>

    <div v-else class="bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
      <form @submit="onSubmit">
        <div class="px-4 py-6 sm:p-8 flex flex-col gap-6">
          <Message v-if="saveError" severity="error" :closable="true">{{ saveError }}</Message>
          <Message v-if="saveSuccess" severity="success" :closable="true">{{ saveSuccess }}</Message>

          <div class="flex items-center gap-6 pb-6 border-b border-gray-200 dark:border-gray-800">
            <div class="relative w-24 h-24 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-center overflow-hidden hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer group" :class="{ 'opacity-50 pointer-events-none': isUploadingLogo }">
              <i v-if="isUploadingLogo" class="pi pi-spinner pi-spin text-2xl text-primary-500 absolute z-10"></i>
              <img v-else-if="form.values.logo_path" :src="form.values.logo_path" alt="Company Logo" class="w-full h-full object-cover" />
              <div v-else class="text-center text-gray-400">
                <i class="pi pi-camera text-2xl mb-1 group-hover:scale-110 transition-transform"></i>
                <span class="block text-xs font-medium uppercase">Logo</span>
              </div>
              <input type="file" accept="image/*" @change="onLogoChange" class="absolute inset-0 opacity-0 cursor-pointer" title="Upload Company Logo" :disabled="isUploadingLogo" />
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Company Logo</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mt-1">Recommended size 256x256px. Max 2MB (JPG, PNG, WebP).</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            <!-- Basic Info -->
            <div class="flex flex-col gap-2">
              <label for="name" class="text-sm font-medium text-gray-900 dark:text-gray-200">Company Name <span class="text-red-500">*</span></label>
              <InputText id="name" v-model="name" v-bind="nameAttrs" class="w-full" :invalid="!!form.errors.value.name" />
              <small v-if="form.errors.value.name" class="text-red-500">{{ form.errors.value.name }}</small>
            </div>

            <div class="flex flex-col gap-2">
              <label for="email" class="text-sm font-medium text-gray-900 dark:text-gray-200">Contact Email</label>
              <InputText id="email" type="email" v-model="email" v-bind="emailAttrs" class="w-full" :invalid="!!form.errors.value.email" />
              <small v-if="form.errors.value.email" class="text-red-500">{{ form.errors.value.email }}</small>
            </div>

            <div class="flex flex-col gap-2">
              <label for="phone" class="text-sm font-medium text-gray-900 dark:text-gray-200">Phone Number</label>
              <InputText id="phone" v-model="phone" v-bind="phoneAttrs" class="w-full" :invalid="!!form.errors.value.phone" />
              <small v-if="form.errors.value.phone" class="text-red-500">{{ form.errors.value.phone }}</small>
            </div>

            <div class="flex flex-col gap-2">
              <label for="website" class="text-sm font-medium text-gray-900 dark:text-gray-200">Website</label>
              <InputText id="website" type="url" v-model="website" v-bind="websiteAttrs" placeholder="https://" class="w-full" :invalid="!!form.errors.value.website" />
              <small v-if="form.errors.value.website" class="text-red-500">{{ form.errors.value.website }}</small>
            </div>

            <!-- Tax & Legal -->
            <div class="flex flex-col gap-2">
              <label for="reg_num" class="text-sm font-medium text-gray-900 dark:text-gray-200">Registration Number</label>
              <InputText id="reg_num" v-model="registration_number" v-bind="regAttrs" class="w-full" />
            </div>

            <div class="flex flex-col gap-2">
              <label for="tax_id" class="text-sm font-medium text-gray-900 dark:text-gray-200">Tax ID / VAT</label>
              <InputText id="tax_id" v-model="tax_id" v-bind="taxAttrs" class="w-full" />
            </div>

            <!-- Localization -->
            <div class="flex flex-col gap-2">
              <label for="currency" class="text-sm font-medium text-gray-900 dark:text-gray-200">Currency Code</label>
              <InputText id="currency" v-model="currency" v-bind="currencyAttrs" class="w-full uppercase" maxlength="3" :invalid="!!form.errors.value.currency" />
              <small v-if="form.errors.value.currency" class="text-red-500">{{ form.errors.value.currency }}</small>
            </div>

            <div class="flex flex-col gap-2">
              <label for="timezone" class="text-sm font-medium text-gray-900 dark:text-gray-200">Timezone</label>
              <InputText id="timezone" v-model="timezone" v-bind="timezoneAttrs" class="w-full" :invalid="!!form.errors.value.timezone" />
              <small v-if="form.errors.value.timezone" class="text-red-500">{{ form.errors.value.timezone }}</small>
            </div>

            <!-- Address spans 2 columns -->
            <div class="flex flex-col gap-2 md:col-span-2">
              <label for="address" class="text-sm font-medium text-gray-900 dark:text-gray-200">Physical Address</label>
              <Textarea id="address" v-model="address" v-bind="addressAttrs" rows="3" class="w-full" autoResize />
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-x-4 border-t border-gray-900/10 dark:border-gray-800 px-4 py-4 sm:px-8 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
          <Button type="submit" label="Save Changes" icon="pi pi-check" :loading="isSaving" severity="primary" />
        </div>
      </form>
    </div>
  </div>
</template>
