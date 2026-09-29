<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { Mail } from 'lucide-vue-next'
import { z } from 'zod'
import { UiButton, UiFormLabel, UiFormMessage, UiInput } from '@minorikun/ui'
import RegistrationBrand from '@/components/registration/RegistrationBrand.vue'
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
    <div class="w-full max-w-[724px] min-[761px]:max-w-[496px]">
      <RegistrationProgress :current-step="1" class="mb-10 px-1 min-[761px]:mb-4 min-[761px]:px-0" />

      <div class="rounded-[28px] bg-white px-6 py-9 shadow-[0_18px_50px_rgb(37_91_61/10%)] min-[761px]:rounded-none min-[761px]:bg-transparent min-[761px]:p-0 min-[761px]:shadow-none">
        <RegistrationBrand />

        <header class="mt-8 min-[761px]:mt-0">
          <h1 class="text-[24px] leading-tight font-bold text-[#173b2c] min-[761px]:text-2xl min-[761px]:text-[#1e2923]">生産者登録</h1>
          <p class="mt-2 text-[16px] leading-[1.7] text-[#687b70] min-[761px]:mt-2.5 min-[761px]:text-base min-[761px]:text-[#87968d]">
            <span class="min-[761px]:hidden">確認コードをメールでお送りします。</span>
            <span class="hidden min-[761px]:inline">
              登録に使用するメールアドレスを入力してください。<br />
              確認コードをメールでお送りします。
            </span>
          </p>
        </header>

        <form class="mt-10 grid gap-5 min-[761px]:mt-7" novalidate @submit.prevent="submit">
          <div class="grid gap-2.5">
            <UiFormLabel for="registration-email" class="text-[16px] font-bold text-[#173b2c] min-[761px]:text-sm min-[761px]:font-medium min-[761px]:text-[#687b70]">
              メールアドレス
              <span class="ml-1 rounded bg-[#d33d3d] px-1 py-0.5 text-xs text-white min-[761px]:hidden">必須</span>
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
                placeholder="メールアドレスを入力"
                class="h-[58px] rounded-xl pr-4 pl-12 text-base shadow-none min-[761px]:h-12 min-[761px]:px-4 min-[761px]:text-base min-[761px]:placeholder:text-[#9aa69f]"
                :disabled="isSubmitting"
                :aria-invalid="Boolean(emailError)"
                :aria-describedby="emailError ? 'registration-email-error' : undefined"
              />
            </div>
            <UiFormMessage v-if="emailError" id="registration-email-error" role="alert">{{ emailError }}</UiFormMessage>
          </div>

          <UiButton type="submit" class="min-h-[58px] w-full rounded-xl border-0 bg-linear-to-r from-[#2d965a] to-[#17653d] text-[17px] shadow-[0_10px_20px_rgb(26_98_58/20%)] min-[761px]:min-h-12 min-[761px]:bg-[#237b4d] min-[761px]:bg-none min-[761px]:text-base min-[761px]:shadow-none" :disabled="isSubmitting">
            {{ isSubmitting ? '送信中…' : '確認コードを送信する' }}
          </UiButton>
        </form>

        <p class="mt-7 text-center text-[15px] text-[#87968d] min-[761px]:mt-6 min-[761px]:text-left min-[761px]:text-sm">
          アカウントをお持ちですか？
          <RouterLink to="/login" class="ml-2 font-medium text-[#237f4b] underline underline-offset-4">ログイン</RouterLink>
        </p>
      </div>
    </div>
  </RegistrationShell>
</template>
