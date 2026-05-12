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
  <div class="p-8 sm:p-12">
    <div class="mb-10 text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-500/10 mb-6 group hover:scale-110 transition-transform duration-300">
        <i class="pi pi-shield text-3xl text-primary-600 dark:text-primary-400 group-hover:rotate-12 transition-transform" />
      </div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">
        {{ step === 'credentials' ? 'Welcome Back' : 'Security Check' }}
      </h2>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
        {{ step === 'credentials' ? 'Please enter your details to access your account' : 'A verification code has been sent to your device' }}
      </p>
    </div>

    <Transition
      mode="out-in"
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform -translate-y-2 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform translate-y-2 opacity-0"
    >
      <div :key="step">
        <Message
          v-if="errorMessage && lockoutSecondsLeft <= 0"
          severity="error"
          variant="simple"
          class="mb-6 bg-red-50/50 dark:bg-red-900/20 border border-red-200/50 dark:border-red-800/50 rounded-xl"
        >
          <div class="flex items-center gap-2">
            <i class="pi pi-exclamation-circle text-red-500" />
            <span class="text-sm font-medium text-red-800 dark:text-red-200">{{ errorMessage }}</span>
          </div>
        </Message>

        <Message
          v-if="lockoutSecondsLeft > 0"
          severity="warn"
          variant="simple"
          class="mb-6 bg-amber-50/50 dark:bg-amber-900/20 border border-amber-200/50 dark:border-amber-800/50 rounded-xl"
        >
          <div class="flex items-center gap-2">
            <i class="pi pi-lock text-amber-500" />
            <span class="text-sm font-medium text-amber-800 dark:text-amber-200">
              Account locked. Try again in {{ formatRetry(lockoutSecondsLeft) }}.
            </span>
          </div>
        </Message>

        <form
          v-if="step === 'credentials'"
          class="flex flex-col gap-6"
          novalidate
          @submit="onCredentialsSubmit"
        >
          <div class="flex flex-col gap-2">
            <label for="email" class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">Email Address</label>
            <div class="relative group">
              <i class="pi pi-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary-500 transition-colors z-10" />
              <InputText
                id="email"
                v-model="email"
                v-bind="emailAttrs"
                type="email"
                autocomplete="email"
                placeholder="name@company.com"
                class="w-full pl-12 h-12 bg-gray-50/50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700 rounded-xl focus:ring-primary-500/20 transition-all"
                :invalid="!!credForm.errors.value.email"
              />
            </div>
            <Transition enter-active-class="duration-200 ease-out" enter-from-class="opacity-0 -translate-y-1">
              <small v-if="credForm.errors.value.email" class="text-red-500 text-xs font-medium ml-1">
                {{ credForm.errors.value.email }}
              </small>
            </Transition>
          </div>

          <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between ml-1">
              <label for="password" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Password</label>
              <NuxtLink
                to="/auth/forgot-password"
                class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400"
              >
                Forgot?
              </NuxtLink>
            </div>
            <div class="relative group">
              <i class="pi pi-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary-500 transition-colors z-10" />
              <Password
                input-id="password"
                v-model="password"
                v-bind="passwordAttrs"
                :feedback="false"
                toggle-mask
                class="w-full"
                input-class="w-full pl-12 h-12 bg-gray-50/50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700 rounded-xl focus:ring-primary-500/20 transition-all"
                :input-props="{ autocomplete: 'current-password' }"
                :invalid="!!credForm.errors.value.password"
              />
            </div>
            <Transition enter-active-class="duration-200 ease-out" enter-from-class="opacity-0 -translate-y-1">
              <small v-if="credForm.errors.value.password" class="text-red-500 text-xs font-medium ml-1">
                {{ credForm.errors.value.password }}
              </small>
            </Transition>
          </div>

          <div class="flex items-center gap-2 ml-1">
            <Checkbox
              v-model="remember"
              input-id="remember"
              binary
              class="w-5 h-5"
            />
            <label for="remember" class="text-sm font-medium text-gray-600 dark:text-gray-400 cursor-pointer select-none">
              Keep me logged in
            </label>
          </div>

          <Button
            type="submit"
            class="w-full h-12 rounded-xl text-base font-bold shadow-lg shadow-primary-500/20 hover:shadow-primary-500/40 transition-all active:scale-[0.98]"
            :loading="credForm.isSubmitting.value"
            :disabled="lockoutSecondsLeft > 0"
          >
            <template #default>
              <span class="flex items-center justify-center gap-2">
                Sign In
                <i class="pi pi-arrow-right text-sm" />
              </span>
            </template>
          </Button>
        </form>

        <form
          v-else
          class="flex flex-col gap-6"
          novalidate
          @submit="onTwoFactorSubmit"
        >
          <div class="flex flex-col gap-2">
            <label for="code" class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">Authentication Code</label>
            <div class="relative group">
              <i class="pi pi-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary-500 transition-colors z-10" />
              <InputText
                id="code"
                v-model="code"
                v-bind="codeAttrs"
                autocomplete="one-time-code"
                inputmode="numeric"
                maxlength="20"
                placeholder="123 456"
                class="w-full pl-12 h-14 bg-gray-50/50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700 rounded-xl font-mono tracking-[0.5em] text-xl text-center focus:ring-primary-500/20 transition-all"
                :invalid="!!tfaForm.errors.value.code"
              />
            </div>
            <Transition enter-active-class="duration-200 ease-out" enter-from-class="opacity-0 -translate-y-1">
              <small v-if="tfaForm.errors.value.code" class="text-red-500 text-xs font-medium ml-1">
                {{ tfaForm.errors.value.code }}
              </small>
            </Transition>
          </div>

          <div class="flex flex-col gap-3">
            <Button
              type="submit"
              label="Verify & Continue"
              class="w-full h-12 rounded-xl text-base font-bold shadow-lg shadow-primary-500/20 active:scale-[0.98]"
              :loading="tfaForm.isSubmitting.value"
              :disabled="lockoutSecondsLeft > 0"
            />
            <Button
              type="button"
              label="Back to login"
              severity="secondary"
              variant="text"
              class="w-full h-10 rounded-xl font-semibold opacity-70 hover:opacity-100"
              @click="backToCredentials"
            />
          </div>
        </form>
      </div>
    </Transition>
  </div>
</template>
