<script setup lang="ts">
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import { ApiError } from '~/composables/useApi'

definePageMeta({ layout: 'auth' })

interface EnableResponse {
  qr_svg: string
  secret: string
}

interface ConfirmResponse {
  recovery_codes: string[]
}

const schema = toTypedSchema(z.object({
  code: z.string().length(6, 'Must be 6 digits').regex(/^\d+$/, 'Must be digits only'),
}))

const form = useForm({
  validationSchema: schema,
  initialValues: { code: '' },
})

const [code, codeAttrs] = form.defineField('code')

const api = useApi()
const router = useRouter()

const isEnabling = ref(true)
const setupError = ref('')
const submitError = ref('')

const qrSvg = ref('')
const secret = ref('')
const recoveryCodes = ref<string[]>([])

const recoveryCodesShown = computed(() => recoveryCodes.value.length > 0)

onMounted(async () => {
  try {
    const response = await api<EnableResponse>('/auth/two-factor/enable', { method: 'POST' })
    qrSvg.value = response.qr_svg
    secret.value = response.secret
  }
  catch (err) {
    setupError.value = err instanceof ApiError ? err.message : 'Unable to start 2FA setup.'
  }
  finally {
    isEnabling.value = false
  }
})

const onSubmit = form.handleSubmit(async (values) => {
  submitError.value = ''
  try {
    const response = await api<ConfirmResponse>('/auth/two-factor/confirm', {
      method: 'POST',
      body: { code: values.code },
    })
    recoveryCodes.value = response.recovery_codes
  }
  catch (err) {
    submitError.value = err instanceof ApiError ? err.message : 'Could not confirm 2FA code.'
  }
})

function continueToDashboard() {
  router.push('/')
}
</script>

<template>
  <div class="p-8">
    <div class="mb-6">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-50">
        Two-factor authentication
      </h2>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Add an extra layer of security to your account.
      </p>
    </div>

    <Message v-if="setupError" severity="error" :closable="false" class="mb-4">
      {{ setupError }}
    </Message>

    <div v-if="recoveryCodesShown">
      <Message severity="success" :closable="false" class="mb-4">
        Two-factor authentication is now enabled.
      </Message>

      <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
        Save these recovery codes somewhere safe. Each one is single-use and lets you sign in if you lose your authenticator.
      </p>
      <ul class="grid grid-cols-2 gap-2 font-mono text-sm mb-6">
        <li
          v-for="rc in recoveryCodes"
          :key="rc"
          class="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded py-1.5 px-2 text-gray-800 dark:text-gray-200"
        >
          {{ rc }}
        </li>
      </ul>

      <Button
        label="Continue to dashboard"
        class="w-full"
        @click="continueToDashboard"
      />
    </div>

    <div v-else-if="isEnabling" class="flex justify-center py-10">
      <i class="pi pi-spinner pi-spin text-2xl text-gray-400" />
    </div>

    <div v-else-if="!setupError">
      <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
        Scan the QR code with your authenticator app (Google Authenticator, Authy, 1Password).
      </p>

      <div class="flex justify-center mb-4 p-4 bg-white dark:bg-gray-100 border border-gray-200 dark:border-gray-700 rounded">
        <div v-if="qrSvg" class="w-44 h-44" v-html="qrSvg" />
      </div>

      <div v-if="secret" class="text-center text-xs mb-6">
        <span class="text-gray-500 dark:text-gray-400">Or enter this code manually:</span>
        <code class="block mt-1.5 font-mono text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 py-1 px-2 rounded mx-auto w-fit select-all">{{ secret }}</code>
      </div>

      <Message v-if="submitError" severity="error" :closable="false" class="mb-4">
        {{ submitError }}
      </Message>

      <form class="flex flex-col gap-4" novalidate @submit="onSubmit">
        <div class="flex flex-col gap-1.5">
          <label for="code" class="text-sm font-medium text-gray-700 dark:text-gray-200">
            6-digit code from your app
          </label>
          <InputText
            id="code"
            v-model="code"
            v-bind="codeAttrs"
            inputmode="numeric"
            maxlength="6"
            autocomplete="one-time-code"
            placeholder="123456"
            class="w-full font-mono tracking-widest text-center"
            :invalid="!!form.errors.value.code"
          />
          <small v-if="form.errors.value.code" class="text-red-600 dark:text-red-400">
            {{ form.errors.value.code }}
          </small>
        </div>

        <Button
          type="submit"
          label="Verify & enable 2FA"
          class="w-full"
          :loading="form.isSubmitting.value"
        />
      </form>
    </div>
  </div>
</template>
