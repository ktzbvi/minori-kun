<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Mail } from 'lucide-vue-next'
import { z } from 'zod'
import { UiButton, UiCard, UiFormLabel, UiFormMessage, UiInput } from '@minorikun/ui'
import RegistrationBrand from '@/components/registration/RegistrationBrand.vue'
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
        <p class="mt-2 text-[16px] leading-[1.65] text-[#687b70] min-[761px]:mt-4 min-[761px]:text-base min-[761px]:text-[#303a34]">
          <strong class="font-bold min-[761px]:text-[#303a34]">{{ destinationEmail }}</strong>
          に確認コードを送信しました。<br class="hidden min-[761px]:block" />
          <span class="hidden min-[761px]:inline">メールに記載された6桁のコードを入力してください。</span>
        </p>

        <form class="mt-10 grid gap-5 min-[761px]:mt-5 min-[761px]:gap-4" novalidate @submit.prevent="submit">
          <div class="grid gap-2">
            <UiFormLabel for="verification-code" class="text-[16px] font-bold text-[#173b2c] min-[761px]:text-sm min-[761px]:font-medium min-[761px]:text-[#687b70]">
              確認コード（6桁）
              <span class="ml-1 rounded bg-[#d33d3d] px-1 py-0.5 text-xs text-white min-[761px]:hidden">必須</span>
            </UiFormLabel>
            <UiInput
              id="verification-code"
              v-model="code"
              name="verification-code"
              type="text"
              inputmode="numeric"
              autocomplete="one-time-code"
              maxlength="6"
              placeholder="6桁の数字"
              class="h-[58px] rounded-xl px-4 text-base shadow-none min-[761px]:h-12 min-[761px]:text-base"
              :disabled="isSubmitting"
              :aria-invalid="Boolean(codeError)"
              :aria-describedby="codeError ? 'verification-code-error' : undefined"
            />
            <UiFormMessage v-if="codeError" id="verification-code-error" role="alert">{{ codeError }}</UiFormMessage>
          </div>

          <UiButton type="submit" class="min-h-[58px] w-full rounded-xl border-0 bg-linear-to-r from-[#2d965a] to-[#17653d] text-[17px] shadow-[0_10px_20px_rgb(26_98_58/20%)] min-[761px]:min-h-12 min-[761px]:bg-[#237b4d] min-[761px]:bg-none min-[761px]:text-base min-[761px]:shadow-none" :disabled="isSubmitting">
            {{ isSubmitting ? '確認中…' : '確認して次へ進む' }}
          </UiButton>
        </form>

        <p class="mt-6 text-sm leading-relaxed text-[#87968d] min-[761px]:mt-4">メールが届かない場合は、迷惑メールフォルダもご確認ください。</p>
        <UiButton variant="outline" class="mt-5 min-h-[56px] w-full rounded-xl text-base min-[761px]:mt-4 min-[761px]:min-h-12 min-[761px]:text-base" @click="resend">確認コードを再送する</UiButton>
        <p v-if="feedback" class="mt-3 text-sm font-medium text-[#247d4c]" role="status">{{ feedback }}</p>
        <div class="text-center min-[761px]:text-left">
          <button type="button" class="mt-7 text-sm font-medium text-[#258451] underline underline-offset-4 min-[761px]:mt-5 min-[761px]:text-base" @click="changeEmail">メールアドレスを変更する</button>
        </div>
      </UiCard>
    </div>
  </RegistrationShell>
</template>
