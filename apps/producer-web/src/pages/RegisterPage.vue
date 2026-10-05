<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { Mail } from 'lucide-vue-next'
import { z } from 'zod'
import { toast, UiButton, UiFormLabel, UiFormMessage, UiInput } from '@minorikun/ui'
import RegistrationBrand from '@/components/registration/RegistrationBrand.vue'
import RegistrationProgress from '@/components/registration/RegistrationProgress.vue'
import RegistrationShell from '@/components/registration/RegistrationShell.vue'
import { getApiErrorPayload } from '@/lib/api-error'
import { useProducerRegistration } from '@/composables/useProducerRegistration'
import { useProducerRegistrationStatusQuery } from '@/services/registration/registration.query'

const route = useRoute()
const statusQuery = useProducerRegistrationStatusQuery()
const registrationActions = useProducerRegistration()
const email = ref('')
const emailError = ref('')
const clockTick = ref(Date.now())
const clockOffset = ref(0)
const cooldownEmail = ref('')
const cooldownUntil = ref(0)
const isSubmitting = registrationActions.isRequestingCode
const cooldownSeconds = computed(() => {
  clockTick.value
  return Math.max(0, Math.ceil((cooldownUntil.value - (Date.now() + clockOffset.value)) / 1000))
})
const currentEmailCoolingDown = computed(() => cooldownSeconds.value > 0 && email.value.trim().toLowerCase() === cooldownEmail.value)
const cooldownText = computed(() => `${Math.floor(cooldownSeconds.value / 60).toString().padStart(2, '0')}:${(cooldownSeconds.value % 60).toString().padStart(2, '0')}`)

const emailSchema = z.string().trim().min(1, 'メールアドレスを入力してください。').email('正しいメールアドレスを入力してください。')

watch(() => statusQuery.data.value?.email, (serverEmail) => {
  if (!email.value && serverEmail && statusQuery.data.value?.state === 'expired') email.value = serverEmail
}, { immediate: true })

watch(() => statusQuery.data.value, (state) => {
  if (!state?.email || !state.resend_available_at) return
  const deadline = Date.parse(state.resend_available_at)
  if (!Number.isFinite(deadline)) return
  cooldownEmail.value = state.email.trim().toLowerCase()
  cooldownUntil.value = deadline
  if (state.server_time) clockOffset.value = Date.parse(state.server_time) - Date.now()
}, { immediate: true })

let clockInterval: ReturnType<typeof setInterval> | undefined
onMounted(() => { clockInterval = setInterval(() => { clockTick.value = Date.now() }, 1000) })
onBeforeUnmount(() => { if (clockInterval) clearInterval(clockInterval) })

const recoveryMessage = computed(() => {
  if (route.query.recovery === 'expired') return '登録情報の有効期限が切れました。メールアドレスを入力して、もう一度確認してください。'
  if (route.query.recovery === 'missing') return 'メールアドレスを入力して、確認を始めてください。'
  return ''
})

async function submit() {
  if (isSubmitting.value) return
  emailError.value = ''
  const result = emailSchema.safeParse(email.value)
  if (!result.success) {
    emailError.value = result.error.issues[0]?.message ?? ''
    return
  }
  if (currentEmailCoolingDown.value) return

  try {
    await registrationActions.requestCode(result.data, statusQuery.data.value?.state)
  } catch (error) {
    const payload = getApiErrorPayload(error)
    if (payload?.code === 'ACCOUNT_EXISTS') {
      emailError.value = registrationActions.errorMessage(error)
      return
    }
    if (payload?.server_time) clockOffset.value = Date.parse(payload.server_time) - Date.now()
    const cooldownDeadline = payload?.resend_available_at
      ? Date.parse(payload.resend_available_at)
      : payload?.retry_after && payload.server_time
        ? Date.parse(payload.server_time) + payload.retry_after * 1000
        : 0
    if (cooldownDeadline > 0 && ['RESEND_COOLDOWN', 'DELIVERY_FAILED'].includes(payload?.code ?? '')) {
      cooldownEmail.value = result.data.toLowerCase()
      cooldownUntil.value = cooldownDeadline
    }
    const message = registrationActions.errorMessage(error)
    if (message) toast.error(message)
    await statusQuery.refetch()
  }
}
</script>

<template>
  <RegistrationShell>
    <div class="w-full max-w-[724px] min-[761px]:max-w-[496px]">
      <RegistrationProgress :current-step="1" class="mb-10 px-1 min-[761px]:mb-4 min-[761px]:px-0 max-[760px]:mb-5" />

      <div class="rounded-[28px] bg-white px-6 py-9 shadow-[0_18px_50px_rgb(37_91_61/10%)] min-[761px]:rounded-none min-[761px]:bg-transparent min-[761px]:p-0 min-[761px]:shadow-none max-[760px]:rounded-2xl max-[760px]:px-5 max-[760px]:py-6">
        <RegistrationBrand />

        <header class="mt-8 min-[761px]:mt-0 max-[760px]:mt-5">
          <h1 class="text-xl leading-tight font-bold text-[#173b2c] min-[761px]:text-2xl min-[761px]:text-[#1e2923]">生産者登録</h1>
          <p class="mt-2 text-base leading-[1.7] text-[#687b70] min-[761px]:mt-2.5 min-[761px]:text-base min-[761px]:text-[#87968d]">
            <span class="min-[761px]:hidden">確認コードをメールでお送りします。</span>
            <span class="hidden min-[761px]:inline">
              登録に使用するメールアドレスを入力してください。<br />
              確認コードをメールでお送りします。
            </span>
          </p>
        </header>

        <p v-if="recoveryMessage" class="mt-5 rounded-lg bg-[#fff8e8] px-4 py-3 text-sm leading-relaxed text-[#614c22]" role="status">
          {{ recoveryMessage }}
        </p>

        <div v-if="statusQuery.isPending.value" class="mt-8 rounded-xl border border-[#cfdfd5] bg-white p-5 text-sm text-[#687b70] max-[760px]:mt-5" role="status">
          登録状態を確認しています…
        </div>
        <form v-else class="mt-10 grid gap-5 min-[761px]:mt-7 max-[760px]:mt-6" novalidate :aria-busy="isSubmitting" @submit.prevent="submit">
          <div class="grid gap-2.5">
            <UiFormLabel for="registration-email" class="text-sm font-bold text-[#173b2c] min-[761px]:font-medium min-[761px]:text-[#687b70]">
              メールアドレス
              <span class="ml-1 rounded bg-[#b74646] px-1 py-0.5 text-xs text-white min-[761px]:hidden">必須</span>
              <span class="hidden text-[#b74646] min-[761px]:inline"> *</span>
            </UiFormLabel>
            <div class="relative">
              <Mail class="absolute top-1/2 left-4 size-5 -translate-y-1/2 text-[#7b8c82] min-[761px]:hidden" :stroke-width="2" aria-hidden="true" />
              <UiInput
                id="registration-email"
                v-model="email"
                name="email"
                type="email"
                autocomplete="email"
                inputmode="email"
                required
                class="h-[58px] rounded-xl pr-4 pl-12 text-base shadow-none min-[761px]:h-12 min-[761px]:px-4 min-[761px]:text-base max-[760px]:h-12"
                :disabled="isSubmitting"
                :aria-invalid="Boolean(emailError)"
                :aria-describedby="emailError ? 'registration-email-error' : currentEmailCoolingDown ? 'registration-cooldown' : undefined"
              />
            </div>
            <UiFormMessage v-if="emailError" id="registration-email-error" role="alert">{{ emailError }}</UiFormMessage>
          </div>

          <p v-if="currentEmailCoolingDown" id="registration-cooldown" class="text-sm leading-relaxed text-[#614c22]" role="status">
            確認コードはあと {{ cooldownText }} 後に再送できます。
          </p>
          <UiButton type="submit" class="min-h-[58px] w-full rounded-xl border-0 bg-linear-to-r from-[#2d965a] to-[#17653d] text-base shadow-[0_10px_20px_rgb(26_98_58/20%)] min-[761px]:min-h-12 min-[761px]:bg-[#237b4d] min-[761px]:bg-none min-[761px]:text-base min-[761px]:shadow-none max-[760px]:min-h-12 max-[760px]:text-base" :disabled="isSubmitting || currentEmailCoolingDown">
            {{ isSubmitting ? '送信中…' : '確認コードを送信する' }}
          </UiButton>
        </form>

        <p class="mt-7 text-center text-sm text-[#87968d] min-[761px]:mt-6 min-[761px]:text-left min-[761px]:text-sm max-[760px]:mt-5">
          アカウントをお持ちですか？
          <RouterLink to="/login" class="ml-2 font-medium text-[#237f4b] underline underline-offset-4">ログイン</RouterLink>
        </p>
      </div>
    </div>
  </RegistrationShell>
</template>
