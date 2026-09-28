<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Mail } from 'lucide-vue-next'
import { z } from 'zod'
import { UiButton, UiCard, UiFormLabel, UiFormMessage, UiInput } from '@minorikun/ui'
import RegistrationProgress from '@/components/registration/RegistrationProgress.vue'
import RegistrationShell from '@/components/registration/RegistrationShell.vue'
import { useProducerRegistrationStore } from '@/stores/producerRegistration'

const router = useRouter()
const registration = useProducerRegistrationStore()
const code = ref(registration.verificationCode)
const codeError = ref('')
const feedback = ref('')
const isSubmitting = ref(false)
const destinationEmail = computed(() => registration.email || 'name@example.jp')
const codeSchema = z.string().regex(/^\d{6}$/, '6桁の数字を入力してください。')

async function submit() {
  codeError.value = ''
  feedback.value = ''
  const result = codeSchema.safeParse(code.value)
  if (!result.success) {
    codeError.value = result.error.issues[0]?.message ?? ''
    return
  }

  isSubmitting.value = true
  try {
    await registration.verifyEmail(result.data)
    await router.push({ name: 'register-details' })
  } finally {
    isSubmitting.value = false
  }
}

async function resend() {
  await registration.resendVerificationCode()
  code.value = ''
  codeError.value = ''
  feedback.value = '確認コードを再送しました。'
}

async function changeEmail() {
  registration.changeEmail()
  await router.push({ name: 'register' })
}
</script>

<template>
  <RegistrationShell>
    <div class="w-full max-w-[620px]">
      <RegistrationProgress :current-step="2" class="mb-7" />

      <UiCard class="rounded-2xl border-[#cfdfd5] bg-white p-7 shadow-none min-[761px]:p-12">
        <div class="grid size-[70px] place-items-center rounded-full bg-[#e5f1d8] text-[#247d4c]">
          <Mail class="size-9" :stroke-width="2" aria-hidden="true" />
        </div>
        <h1 class="mt-6 text-[28px] font-bold text-[#1e2923] min-[761px]:text-4xl">確認コードを入力</h1>
        <p class="mt-5 text-[15px] leading-[1.65] text-[#303a34] min-[761px]:text-lg">
          <strong class="font-bold">{{ destinationEmail }}</strong> に確認コードを送信しました。<br />
          メールに記載された6桁のコードを入力してください。
        </p>

        <form class="mt-6 grid gap-5" novalidate @submit.prevent="submit">
          <div class="grid gap-2">
            <UiFormLabel for="verification-code" class="text-[15px] text-[#687b70] min-[761px]:text-lg">確認コード（6桁）</UiFormLabel>
            <UiInput
              id="verification-code"
              v-model="code"
              name="verification-code"
              type="text"
              inputmode="numeric"
              autocomplete="one-time-code"
              maxlength="6"
              placeholder="6桁の数字"
              class="h-[58px] rounded-xl px-5 text-lg shadow-none min-[761px]:h-[72px] min-[761px]:text-xl"
              :disabled="isSubmitting"
              :aria-invalid="Boolean(codeError)"
              :aria-describedby="codeError ? 'verification-code-error' : undefined"
            />
            <UiFormMessage v-if="codeError" id="verification-code-error" role="alert">{{ codeError }}</UiFormMessage>
          </div>

          <UiButton type="submit" class="min-h-[58px] w-full rounded-xl text-[17px] min-[761px]:min-h-[68px] min-[761px]:text-xl" :disabled="isSubmitting">
            {{ isSubmitting ? '確認中…' : '確認して次へ進む' }}
          </UiButton>
        </form>

        <p class="mt-6 text-sm leading-relaxed text-[#87968d] min-[761px]:text-base">メールが届かない場合は、迷惑メールフォルダもご確認ください。</p>
        <UiButton variant="outline" class="mt-5 min-h-[56px] w-full rounded-xl text-base min-[761px]:text-lg" @click="resend">確認コードを再送する</UiButton>
        <p v-if="feedback" class="mt-3 text-sm font-medium text-[#247d4c]" role="status">{{ feedback }}</p>
        <button type="button" class="mt-5 text-sm font-medium text-[#258451] underline underline-offset-4 min-[761px]:text-base" @click="changeEmail">メールアドレスを変更する</button>
      </UiCard>
    </div>
  </RegistrationShell>
</template>
