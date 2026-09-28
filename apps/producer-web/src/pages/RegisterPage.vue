<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { z } from 'zod'
import { UiButton, UiFormLabel, UiFormMessage, UiInput } from '@minorikun/ui'
import RegistrationProgress from '@/components/registration/RegistrationProgress.vue'
import RegistrationShell from '@/components/registration/RegistrationShell.vue'
import { useProducerRegistrationStore } from '@/stores/producerRegistration'

const router = useRouter()
const registration = useProducerRegistrationStore()
const email = ref(registration.email)
const emailError = ref('')
const isSubmitting = ref(false)

const emailSchema = z.string().trim().min(1, 'メールアドレスを入力してください。').email('正しいメールアドレスを入力してください。')

async function submit() {
  emailError.value = ''
  const result = emailSchema.safeParse(email.value)
  if (!result.success) {
    emailError.value = result.error.issues[0]?.message ?? ''
    return
  }

  isSubmitting.value = true
  try {
    await registration.requestVerificationCode(result.data)
    await router.push({ name: 'register-verify' })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <RegistrationShell>
    <div class="w-full max-w-[560px] min-[761px]:max-w-[772px]">
      <RegistrationProgress :current-step="1" class="mb-5" />

      <header>
        <h1 class="text-[26px] leading-tight font-bold text-[#1e2923] min-[761px]:text-4xl">生産者登録</h1>
        <p class="mt-3.5 text-[15px] leading-[1.7] text-[#87968d] min-[761px]:text-xl">
          登録に使用するメールアドレスを入力してください。<br class="hidden min-[761px]:block" />
          確認コードをメールでお送りします。
        </p>
      </header>

      <form class="mt-7 grid gap-9 min-[761px]:mt-9" novalidate @submit.prevent="submit">
        <div class="grid gap-2.5">
          <UiFormLabel for="registration-email" class="text-[15px] text-[#687b70] min-[761px]:text-lg">メールアドレス</UiFormLabel>
          <UiInput
            id="registration-email"
            v-model="email"
            name="email"
            type="email"
            autocomplete="email"
            inputmode="email"
            placeholder="name@example.jp"
            class="h-[58px] rounded-[10px] px-4 text-base shadow-none min-[761px]:h-[76px] min-[761px]:rounded-xl min-[761px]:px-6 min-[761px]:text-xl"
            :disabled="isSubmitting"
            :aria-invalid="Boolean(emailError)"
            :aria-describedby="emailError ? 'registration-email-error' : undefined"
          />
          <UiFormMessage v-if="emailError" id="registration-email-error" role="alert">{{ emailError }}</UiFormMessage>
        </div>

        <UiButton type="submit" class="min-h-[58px] w-full rounded-[10px] text-[17px] min-[761px]:min-h-[68px] min-[761px]:rounded-xl min-[761px]:text-xl" :disabled="isSubmitting">
          {{ isSubmitting ? '送信中…' : '確認コードを送信する' }}
        </UiButton>
      </form>

      <p class="mt-8 text-[15px] text-[#87968d] min-[761px]:text-lg">
        アカウントをお持ちですか？
        <RouterLink to="/login" class="font-medium text-[#368357] underline underline-offset-4">ログイン</RouterLink>
      </p>
    </div>
  </RegistrationShell>
</template>
