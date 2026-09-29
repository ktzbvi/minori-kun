<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { onBeforeRouteLeave } from 'vue-router'
import { Mail } from 'lucide-vue-next'
import { z } from 'zod'
import { useQuery } from '@tanstack/vue-query'
import { UiButton, UiCard, UiFormLabel, UiFormMessage, UiInput } from '@minorikun/ui'
import RegistrationBrand from '@/components/registration/RegistrationBrand.vue'
import RegistrationProgress from '@/components/registration/RegistrationProgress.vue'
import RegistrationShell from '@/components/registration/RegistrationShell.vue'
import { getApiErrorPayload } from '@/lib/api-error'
import { useProducerRegistration } from '@/composables/useProducerRegistration'
import { producerRegistrationStatusQuery } from '@/services/registration/registration.query'

const statusQuery = useQuery(producerRegistrationStatusQuery)
const registrationActions = useProducerRegistration()
const code = ref('')
const codeError = ref('')
const feedback = ref('')
const clockOffset = ref(0)
const clockTick = ref(Date.now())
const codeSchema = z.string().regex(/^\d{6}$/, '6桁の数字を入力してください。')
const isBusy = computed(() => registrationActions.isVerifyingCode.value || registrationActions.isResendingCode.value || registrationActions.isResettingRegistration.value)
const state = computed(() => statusQuery.data.value)
const destinationEmail = computed(() => state.value?.email ?? '')
const deliveryStatus = computed(() => state.value?.delivery_succeeded)
const mayHaveCode = computed(() => deliveryStatus.value !== false)

watch(() => state.value?.server_time, (serverTime) => {
  if (serverTime) clockOffset.value = Date.parse(serverTime) - Date.now()
}, { immediate: true })

function secondsUntil(timestamp: string | null | undefined) {
  if (!timestamp) return 0
  const deadline = Date.parse(timestamp)
  if (!Number.isFinite(deadline)) return 0
  return Math.max(0, Math.ceil((deadline - (Date.now() + clockOffset.value)) / 1000))
}

const otpSeconds = computed(() => {
  clockTick.value
  return secondsUntil(state.value?.otp_expires_at)
})
const resendSeconds = computed(() => {
  clockTick.value
  return secondsUntil(state.value?.resend_available_at)
})
const otpExpired = computed(() => state.value?.state === 'expired' || otpSeconds.value === 0)
const formattedResendCountdown = computed(() => {
  const minutes = Math.floor(resendSeconds.value / 60).toString().padStart(2, '0')
  const seconds = (resendSeconds.value % 60).toString().padStart(2, '0')
  return `${minutes}:${seconds}`
})

let clockInterval: ReturnType<typeof setInterval> | undefined
onMounted(() => { clockInterval = setInterval(() => { clockTick.value = Date.now() }, 1000) })
onBeforeUnmount(() => { if (clockInterval) clearInterval(clockInterval) })

async function submit() {
  if (isBusy.value || !mayHaveCode.value || otpExpired.value) return
  codeError.value = ''
  feedback.value = ''
  const result = codeSchema.safeParse(code.value)
  if (!result.success) {
    codeError.value = result.error.issues[0]?.message ?? ''
    return
  }

  try {
    const registration = await registrationActions.verifyCode(result.data)
    if (registration.state !== 'verified') {
      await statusQuery.refetch()
      return
    }
    code.value = ''
    registrationActions.resetVerificationState()
  } catch (error) {
    const codeValue = getApiErrorPayload(error)?.code
    if (codeValue === 'OTP_EXPIRED') {
      codeError.value = registrationActions.errorMessage(error)
      await statusQuery.refetch()
    } else if (codeValue === 'OTP_INVALID') {
      codeError.value = registrationActions.errorMessage(error)
    } else {
      feedback.value = registrationActions.errorMessage(error)
    }
    registrationActions.resetVerificationState()
  }
}

async function resend() {
  if (isBusy.value || resendSeconds.value > 0) return
  codeError.value = ''
  feedback.value = ''
  try {
    await registrationActions.resendCode()
    code.value = ''
  } catch (error) {
    feedback.value = registrationActions.errorMessage(error)
    await statusQuery.refetch()
    registrationActions.resetVerificationState()
  }
}

async function changeEmail() {
  if (isBusy.value) return
  try {
    await registrationActions.changeEmail()
    code.value = ''
  } catch (error) {
    feedback.value = registrationActions.errorMessage(error)
  }
}

onBeforeRouteLeave(() => {
  code.value = ''
  registrationActions.resetVerificationState()
})
</script>

<template>
  <RegistrationShell>
    <div class="w-full max-w-[724px] min-[761px]:max-w-[496px]">
      <RegistrationProgress :current-step="2" class="mb-10 px-1 min-[761px]:mb-5 min-[761px]:px-0" />

      <UiCard class="rounded-[28px] border-0 bg-white px-6 py-9 shadow-[0_18px_50px_rgb(37_91_61/10%)] min-[761px]:rounded-xl min-[761px]:border min-[761px]:border-[#cfdfd5] min-[761px]:p-8 min-[761px]:shadow-none">
        <RegistrationBrand />

        <div class="mt-7 hidden size-14 place-items-center rounded-full bg-[#e5f1d8] text-[#247d4c] min-[761px]:grid">
          <Mail class="size-7" :stroke-width="2" aria-hidden="true" />
        </div>
        <h1 class="mt-8 text-[24px] font-bold text-[#173b2c] min-[761px]:mt-5 min-[761px]:text-2xl min-[761px]:text-[#1e2923]">
          <span class="min-[761px]:hidden">メール確認</span>
          <span class="hidden min-[761px]:inline">確認コードを入力</span>
        </h1>

        <div v-if="statusQuery.isPending.value" class="mt-5 rounded-lg border border-[#cfdfd5] p-4 text-sm text-[#687b70]" role="status">
          登録状態を確認しています…
        </div>
        <div v-else-if="statusQuery.isError.value" class="mt-5 rounded-lg border border-[#e4c9c3] p-4">
          <p class="text-sm leading-relaxed text-[#7b3329]" role="alert">{{ registrationActions.errorMessage(statusQuery.error.value) }}</p>
          <UiButton class="mt-4" variant="outline" :disabled="statusQuery.isFetching.value" @click="statusQuery.refetch()">
            {{ statusQuery.isFetching.value ? '確認中…' : '再試行' }}
          </UiButton>
        </div>
        <template v-else>
          <p v-if="deliveryStatus === true" class="mt-2 text-[16px] leading-[1.65] text-[#687b70] min-[761px]:mt-4 min-[761px]:text-base min-[761px]:text-[#303a34]">
            <strong class="font-bold text-[#303a34]">{{ destinationEmail }}</strong>
            に確認コードを送信しました。<br class="hidden min-[761px]:block" />
            <span class="hidden min-[761px]:inline">メールに記載された6桁のコードを入力してください。</span>
          </p>
          <p v-else-if="deliveryStatus === null" class="mt-3 rounded-lg bg-[#fff8e8] px-4 py-3 text-sm leading-relaxed text-[#614c22]" role="status">
            <strong>{{ destinationEmail }}</strong> 宛てに確認コードを送信した場合は、メールに記載されたコードを入力してください。届いていない場合は、再送可能時刻以降に再送してください。
          </p>
          <p v-else class="mt-3 rounded-lg bg-[#fff8e8] px-4 py-3 text-sm leading-relaxed text-[#614c22]" role="status">
            確認コードを送信できませんでした。再送可能時刻にもう一度お試しください。
          </p>

          <div v-if="mayHaveCode && otpExpired" class="mt-5 rounded-lg bg-[#fff8e8] px-4 py-3 text-sm leading-relaxed text-[#614c22]" role="status">
            確認コードの有効期限が切れました。新しいコードを再送してください。
          </div>

          <form v-if="mayHaveCode" class="mt-10 grid gap-5 min-[761px]:mt-5 min-[761px]:gap-4" novalidate :aria-busy="isBusy" @submit.prevent="submit">
            <div class="grid gap-2">
              <UiFormLabel for="verification-code" class="text-[16px] font-bold text-[#173b2c] min-[761px]:text-sm min-[761px]:font-medium min-[761px]:text-[#687b70]">
                確認コード（6桁）
                <span class="ml-1 rounded bg-[#b74646] px-1 py-0.5 text-xs text-white min-[761px]:hidden">必須</span>
                <span class="hidden text-[#b74646] min-[761px]:inline"> *</span>
              </UiFormLabel>
              <UiInput
                id="verification-code"
                v-model="code"
                name="verification-code"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                required
                class="h-[58px] rounded-xl px-4 text-base shadow-none min-[761px]:h-12 min-[761px]:text-base"
                :disabled="isBusy || otpExpired"
                :aria-invalid="Boolean(codeError)"
                :aria-describedby="codeError ? 'verification-code-error' : undefined"
              />
              <UiFormMessage v-if="codeError" id="verification-code-error" role="alert">{{ codeError }}</UiFormMessage>
            </div>

            <UiButton type="submit" class="min-h-[58px] w-full rounded-xl border-0 bg-linear-to-r from-[#2d965a] to-[#17653d] text-[17px] shadow-[0_10px_20px_rgb(26_98_58/20%)] min-[761px]:min-h-12 min-[761px]:bg-[#237b4d] min-[761px]:bg-none min-[761px]:text-base min-[761px]:shadow-none" :disabled="isBusy || otpExpired">
              {{ registrationActions.isVerifyingCode.value ? '確認中…' : '確認して次へ進む' }}
            </UiButton>
          </form>

          <p v-if="mayHaveCode" class="mt-6 text-sm leading-relaxed text-[#87968d] min-[761px]:mt-4">メールが届かない場合は、迷惑メールフォルダもご確認ください。</p>
          <UiButton variant="outline" class="mt-5 min-h-[56px] w-full rounded-xl text-base min-[761px]:mt-4 min-[761px]:min-h-12 min-[761px]:text-base" :disabled="isBusy || resendSeconds > 0" @click="resend">
            <template v-if="isBusy">送信中…</template>
            <template v-else-if="resendSeconds > 0">再送可能まであと {{ formattedResendCountdown }}</template>
            <template v-else>確認コードを再送する</template>
          </UiButton>
          <p v-if="feedback" class="mt-3 text-sm leading-relaxed text-[#247d4c]" role="status">{{ feedback }}</p>
          <div class="text-center min-[761px]:text-left">
            <UiButton type="button" variant="ghost" class="mt-7 min-h-0 border-0 p-0 text-sm font-medium text-[#258451] underline underline-offset-4 min-[761px]:mt-5 min-[761px]:text-base" :disabled="isBusy" @click="changeEmail">
              メールアドレスを変更する
            </UiButton>
          </div>
        </template>
      </UiCard>
    </div>
  </RegistrationShell>
</template>
