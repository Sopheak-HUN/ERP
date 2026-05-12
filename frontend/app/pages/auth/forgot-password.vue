<script setup lang="ts">
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import { ApiError } from '~/composables/useApi'

definePageMeta({ layout: 'auth' })

const schema = toTypedSchema(z.object({
  email: z.string().email('Invalid email format'),
}))

const form = useForm({
  validationSchema: schema,
  initialValues: { email: '' },
})

const [email, emailAttrs] = form.defineField('email')

const errorMessage = ref('')
const successMessage = ref('')

const api = useApi()

const onSubmit = form.handleSubmit(async (values) => {
  errorMessage.value = ''
  successMessage.value = ''
  try {
    await api('/auth/forgot-password', { method: 'POST', body: values })
    successMessage.value = 'If that email exists, we have sent a password reset link.'
  }
  catch (err) {
    errorMessage.value = err instanceof ApiError ? err.message : 'An unexpected error occurred.'
  }
})
</script>

<template>
  <div class="p-8">
    <div class="mb-6">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-50">
        Reset password
      </h2>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Enter your email to receive a reset link.
      </p>
    </div>

    <Message v-if="errorMessage" severity="error" :closable="false" class="mb-4">
      {{ errorMessage }}
    </Message>
    <Message v-if="successMessage" severity="success" :closable="false" class="mb-4">
      {{ successMessage }}
    </Message>

    <form
      v-if="!successMessage"
      class="flex flex-col gap-4"
      novalidate
      @submit="onSubmit"
    >
      <div class="flex flex-col gap-1.5">
        <label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
        <InputText
          id="email"
          v-model="email"
          v-bind="emailAttrs"
          type="email"
          autocomplete="email"
          placeholder="you@example.com"
          class="w-full"
          :invalid="!!form.errors.value.email"
        />
        <small v-if="form.errors.value.email" class="text-red-600 dark:text-red-400">
          {{ form.errors.value.email }}
        </small>
      </div>

      <Button
        type="submit"
        label="Send reset link"
        class="w-full"
        :loading="form.isSubmitting.value"
      />
    </form>

    <div class="mt-6 text-center">
      <NuxtLink
        to="/auth/login"
        class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
      >
        Back to login
      </NuxtLink>
    </div>
  </div>
</template>
