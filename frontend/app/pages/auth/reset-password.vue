<script setup lang="ts">
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import { ApiError } from '~/composables/useApi'

definePageMeta({ layout: 'auth' })

const route = useRoute()
const router = useRouter()

const schema = toTypedSchema(z.object({
  email: z.string().email('Invalid email format'),
  password: z.string().min(12, 'Must be at least 12 characters'),
  password_confirmation: z.string(),
}).refine(data => data.password === data.password_confirmation, {
  message: 'Passwords don\'t match',
  path: ['password_confirmation'],
}))

const form = useForm({
  validationSchema: schema,
  initialValues: {
    email: (route.query.email as string | undefined) ?? '',
    password: '',
    password_confirmation: '',
  },
})

const [email, emailAttrs] = form.defineField('email')
const [password, passwordAttrs] = form.defineField('password')
const [passwordConfirmation, passwordConfirmationAttrs] = form.defineField('password_confirmation')

const token = computed(() => (route.query.token as string | undefined) ?? '')

const errorMessage = ref('')
const successMessage = ref('')

const api = useApi()

const onSubmit = form.handleSubmit(async (values) => {
  errorMessage.value = ''
  try {
    await api('/auth/reset-password', {
      method: 'POST',
      body: { ...values, token: token.value },
    })
    successMessage.value = 'Password reset. Redirecting to sign in...'
    setTimeout(() => router.push('/auth/login'), 2000)
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
        Create new password
      </h2>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Choose a strong password (at least 12 characters).
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
          readonly
          class="w-full"
          :invalid="!!form.errors.value.email"
        />
        <small v-if="form.errors.value.email" class="text-red-600 dark:text-red-400">
          {{ form.errors.value.email }}
        </small>
      </div>

      <div class="flex flex-col gap-1.5">
        <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-200">New password</label>
        <Password
          input-id="password"
          v-model="password"
          v-bind="passwordAttrs"
          :feedback="false"
          toggle-mask
          class="w-full"
          input-class="w-full"
          :input-props="{ autocomplete: 'new-password' }"
          :invalid="!!form.errors.value.password"
        />
        <small v-if="form.errors.value.password" class="text-red-600 dark:text-red-400">
          {{ form.errors.value.password }}
        </small>
      </div>

      <div class="flex flex-col gap-1.5">
        <label for="password_confirmation" class="text-sm font-medium text-gray-700 dark:text-gray-200">Confirm password</label>
        <Password
          input-id="password_confirmation"
          v-model="passwordConfirmation"
          v-bind="passwordConfirmationAttrs"
          :feedback="false"
          toggle-mask
          class="w-full"
          input-class="w-full"
          :input-props="{ autocomplete: 'new-password' }"
          :invalid="!!form.errors.value.password_confirmation"
        />
        <small v-if="form.errors.value.password_confirmation" class="text-red-600 dark:text-red-400">
          {{ form.errors.value.password_confirmation }}
        </small>
      </div>

      <Button
        type="submit"
        label="Reset password"
        class="w-full"
        :loading="form.isSubmitting.value"
      />
    </form>
  </div>
</template>
