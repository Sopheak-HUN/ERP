<script setup lang="ts">
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import { ApiError } from '~/composables/useApi'
import type { LoginResponse } from '~/stores/auth'

definePageMeta({ layout: 'auth' })

const credentialsSchema = toTypedSchema(z.object({
  email: z.string().email('Invalid email format'),
  password: z.string().min(12, 'Must be at least 12 characters'),
  remember: z.boolean().optional(),
}))

const twoFactorSchema = toTypedSchema(z.object({
  code: z.string().regex(/^\d{6}$|^[A-Z0-9-]{10,}$/, 'Enter your 6-digit code or a recovery code'),
}))

const credForm = useForm({
  validationSchema: credentialsSchema,
  initialValues: { email: '', password: '', remember: false },
})

const tfaForm = useForm({
  validationSchema: twoFactorSchema,
  initialValues: { code: '' },
})

const [email, emailAttrs] = credForm.defineField('email')
const [password, passwordAttrs] = credForm.defineField('password')
const [remember] = credForm.defineField('remember')
const [code, codeAttrs] = tfaForm.defineField('code')

const step = ref<'credentials' | 'two_factor'>('credentials')
const errorMessage = ref('')
const lockoutSecondsLeft = ref(0)

let lockoutTimer: ReturnType<typeof setInterval> | null = null

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()
const api = useApi()

function startLockoutCountdown(seconds: number) {
  lockoutSecondsLeft.value = seconds
  if (lockoutTimer) clearInterval(lockoutTimer)
  lockoutTimer = setInterval(() => {
    lockoutSecondsLeft.value -= 1
    if (lockoutSecondsLeft.value <= 0 && lockoutTimer) {
      clearInterval(lockoutTimer)
      lockoutTimer = null
    }
  }, 1000)
}

onBeforeUnmount(() => {
  if (lockoutTimer) clearInterval(lockoutTimer)
})

async function attemptLogin() {
  errorMessage.value = ''
  try {
    const response = await api<LoginResponse>('/auth/login', {
      method: 'POST',
      body: {
        email: credForm.values.email,
        password: credForm.values.password,
        remember: credForm.values.remember,
        ...(tfaForm.values.code ? { two_factor_code: tfaForm.values.code } : {}),
      },
    })
    authStore.setToken(response.access_token)
    authStore.setUser(response.user)
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/'
    await router.push(redirect)
  }
  catch (err) {
    if (err instanceof ApiError) {
      if (err.code === 'TWO_FACTOR_REQUIRED') {
        step.value = 'two_factor'
        errorMessage.value = ''
        return
      }
      if (err.code === 'ACCOUNT_LOCKED') {
        const details = err.details as { retry_after?: number } | undefined
        const retry = details?.retry_after ?? 0
        if (retry > 0) startLockoutCountdown(retry)
        errorMessage.value = err.message
        return
      }
      errorMessage.value = err.message
      return
    }
    errorMessage.value = 'An unexpected error occurred. Please try again.'
  }
}

const onCredentialsSubmit = credForm.handleSubmit(attemptLogin)
const onTwoFactorSubmit = tfaForm.handleSubmit(attemptLogin)

function backToCredentials() {
  step.value = 'credentials'
  tfaForm.resetForm()
  errorMessage.value = ''
}

function formatRetry(seconds: number): string {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return m > 0 ? `${m}m ${s}s` : `${s}s`
}
</script>

<template>
  <div class="p-8">
    <div class="mb-6">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-50">
        {{ step === 'credentials' ? 'Welcome back' : 'Two-factor authentication' }}
      </h2>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        {{ step === 'credentials' ? 'Sign in to your account' : 'Enter the 6-digit code from your authenticator app or a recovery code.' }}
      </p>
    </div>

    <Message
      v-if="errorMessage && lockoutSecondsLeft <= 0"
      severity="error"
      :closable="false"
      class="mb-4"
    >
      {{ errorMessage }}
    </Message>

    <Message
      v-if="lockoutSecondsLeft > 0"
      severity="warn"
      :closable="false"
      class="mb-4"
    >
      Too many failed attempts. Try again in {{ formatRetry(lockoutSecondsLeft) }}.
    </Message>

    <form
      v-if="step === 'credentials'"
      class="flex flex-col gap-4"
      novalidate
      @submit="onCredentialsSubmit"
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
          :invalid="!!credForm.errors.value.email"
        />
        <small v-if="credForm.errors.value.email" class="text-red-600 dark:text-red-400">
          {{ credForm.errors.value.email }}
        </small>
      </div>

      <div class="flex flex-col gap-1.5">
        <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-200">Password</label>
        <Password
          input-id="password"
          v-model="password"
          v-bind="passwordAttrs"
          :feedback="false"
          toggle-mask
          class="w-full"
          input-class="w-full"
          :input-props="{ autocomplete: 'current-password' }"
          :invalid="!!credForm.errors.value.password"
        />
        <small v-if="credForm.errors.value.password" class="text-red-600 dark:text-red-400">
          {{ credForm.errors.value.password }}
        </small>
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Checkbox
            v-model="remember"
            input-id="remember"
            binary
          />
          <label for="remember" class="text-sm text-gray-700 dark:text-gray-200">
            Stay signed in for 30 days
          </label>
        </div>
        <NuxtLink
          to="/auth/forgot-password"
          class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
        >
          Forgot password?
        </NuxtLink>
      </div>

      <Button
        type="submit"
        label="Sign in"
        class="w-full"
        :loading="credForm.isSubmitting.value"
        :disabled="lockoutSecondsLeft > 0"
      />
    </form>

    <form
      v-else
      class="flex flex-col gap-4"
      novalidate
      @submit="onTwoFactorSubmit"
    >
      <div class="flex flex-col gap-1.5">
        <label for="code" class="text-sm font-medium text-gray-700 dark:text-gray-200">Authentication code</label>
        <InputText
          id="code"
          v-model="code"
          v-bind="codeAttrs"
          autocomplete="one-time-code"
          inputmode="numeric"
          maxlength="20"
          placeholder="123456"
          class="w-full font-mono tracking-widest"
          :invalid="!!tfaForm.errors.value.code"
        />
        <small v-if="tfaForm.errors.value.code" class="text-red-600 dark:text-red-400">
          {{ tfaForm.errors.value.code }}
        </small>
      </div>

      <Button
        type="submit"
        label="Verify"
        class="w-full"
        :loading="tfaForm.isSubmitting.value"
        :disabled="lockoutSecondsLeft > 0"
      />
      <Button
        type="button"
        label="Back to login"
        severity="secondary"
        text
        class="w-full"
        @click="backToCredentials"
      />
    </form>
  </div>
</template>
