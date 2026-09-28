<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import axios from 'axios'
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { Eye, EyeOff } from 'lucide-vue-next'
import {
  toast,
  UiButton,
  UiFormControl,
  UiFormItem,
  UiFormLabel,
  UiFormMessage,
  UiInput,
} from '@minorikun/ui'
import { authApi } from '@/lib/api'
import { queryClient } from '@/lib/query'

const errorMessage = ref('')
const assistanceMessage = ref(false)
const passwordVisible = ref(false)
const route = useRoute()
const router = useRouter()

const authenticationError = 'メールアドレスまたはパスワードを確認してください。'
const rateLimitError = '試行回数が多すぎます。しばらくしてからもう一度お試しください。'
const serviceError =
  '現在ログインできません。通信環境を確認し、しばらくしてからもう一度お試しください。'

const loginSchema = toTypedSchema(
  z.object({
    email: z
      .string()
      .trim()
      .min(1, 'メールアドレスを入力してください。')
      .email('正しいメールアドレスを入力してください。')
      .max(255, 'メールアドレスは255文字以内で入力してください。'),
    password: z
      .string()
      .min(1, 'パスワードを入力してください。')
      .max(4096, 'パスワードは4096文字以内で入力してください。'),
  }),
)

const { defineField, errors, handleSubmit, isSubmitting, setFieldValue } = useForm({
  validationSchema: loginSchema,
  initialValues: { email: '', password: '' },
})

const [email, emailAttrs] = defineField('email', (state) => ({
  validateOnBlur: false,
  validateOnChange: false,
  validateOnInput: false,
  validateOnModelUpdate: state.errors.length > 0,
}))
const [password, passwordAttrs] = defineField('password', (state) => ({
  validateOnBlur: false,
  validateOnChange: false,
  validateOnInput: false,
  validateOnModelUpdate: state.errors.length > 0,
}))

const submit = handleSubmit(
  async (values) => {
    errorMessage.value = ''
    try {
      await authApi.csrf()
      await authApi.login(values.email, values.password)
      await queryClient.invalidateQueries({ queryKey: ['current-session'] })
      const redirect =
        typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
          ? route.query.redirect
          : '/'
      await router.replace(redirect)
      toast.success('成功')
    } catch (error: unknown) {
      if (axios.isAxiosError(error) && error.response?.status === 422) {
        errorMessage.value = authenticationError
      } else if (axios.isAxiosError(error) && error.response?.status === 429) {
        errorMessage.value = rateLimitError
      } else {
        errorMessage.value = serviceError
      }
      setFieldValue('password', '', false)
    }
  },
  () => {
    errorMessage.value = ''
  },
)

function showAccountAssistance() {
  assistanceMessage.value = true
}
</script>

<template>
  <main
    class="grid min-h-screen min-w-80 bg-[#f5f7f5] text-[var(--color-text)] md:grid-cols-[46.25%_1fr]"
  >
    <section
      class="flex min-h-[240px] items-center bg-[#123c2b] px-7 py-12 text-white md:min-h-screen md:px-[clamp(48px,5vw,96px)] md:py-16"
      aria-label="みのりくん管理ポータル"
    >
      <div class="max-w-[430px]">
        <p
          class="m-0 text-[32px] leading-tight font-extrabold tracking-[0.04em] text-[#eff6df] md:text-[38px]"
        >
          みのりくん
        </p>
        <p class="mt-6 mb-0 text-lg font-bold text-white/70 md:text-xl">管理者向けログイン</p>
        <p class="mt-5 mb-0 text-sm leading-7 text-white/60 md:text-base">
          登録済みの管理者アカウントでログインしてください。
        </p>
      </div>
    </section>

    <section class="grid place-items-center px-6 py-12 md:px-12 md:py-16">
      <form
        class="w-full max-w-[520px] md:w-[min(78%,720px)] md:max-w-none"
        novalidate
        @submit.prevent="submit"
      >
        <header class="mb-7">
          <h1 class="m-0 text-[30px] leading-tight font-bold text-[var(--color-text)] md:text-[32px]">
            管理者ログイン
          </h1>
          <p class="mt-4 mb-0 text-sm leading-6 text-[var(--color-muted)] md:text-base">
            メールアドレスとパスワードを入力してください。
          </p>
        </header>

        <UiFormItem class="mb-5 gap-2.5">
          <UiFormLabel class="text-[15px] font-medium text-[#66776e]" for="admin-email">
            メールアドレス
          </UiFormLabel>
          <UiFormControl>
            <UiInput
              id="admin-email"
              v-model="email"
              v-bind="emailAttrs"
              class="h-14 rounded-[10px] px-4 text-base shadow-none md:text-base"
              type="email"
              autocomplete="username"
              inputmode="email"
              placeholder="admin@example.jp"
              :aria-invalid="Boolean(errors.email)"
              :aria-describedby="errors.email ? 'admin-email-error' : undefined"
            />
          </UiFormControl>
          <UiFormMessage
            v-if="errors.email"
            id="admin-email-error"
            class="m-0 text-[12px]"
            role="alert"
          >
            {{ errors.email }}
          </UiFormMessage>
        </UiFormItem>

        <UiFormItem class="gap-2.5">
          <UiFormLabel class="text-[15px] font-medium text-[#66776e]" for="admin-password">
            パスワード
          </UiFormLabel>
          <UiFormControl>
            <div class="relative">
              <UiInput
                id="admin-password"
                v-model="password"
                v-bind="passwordAttrs"
                class="h-14 rounded-[10px] py-3 pr-12 pl-4 text-base shadow-none md:text-base"
                :type="passwordVisible ? 'text' : 'password'"
                autocomplete="current-password"
                placeholder="パスワードを入力"
                :aria-invalid="Boolean(errors.password)"
                :aria-describedby="errors.password ? 'admin-password-error' : undefined"
              />
              <button
                class="absolute inset-y-0 right-0 grid w-12 place-items-center border-0 bg-transparent text-[#66776e] hover:text-[var(--color-primary)] focus-visible:outline-2 focus-visible:outline-offset-[-4px] focus-visible:outline-[var(--color-primary)]"
                type="button"
                :aria-label="passwordVisible ? 'パスワードを隠す' : 'パスワードを表示'"
                :aria-pressed="passwordVisible"
                @click="passwordVisible = !passwordVisible"
              >
                <EyeOff v-if="passwordVisible" aria-hidden="true" class="h-5 w-5" />
                <Eye v-else aria-hidden="true" class="h-5 w-5" />
              </button>
            </div>
          </UiFormControl>
          <UiFormMessage
            v-if="errors.password"
            id="admin-password-error"
            class="m-0 text-[12px]"
            role="alert"
          >
            {{ errors.password }}
          </UiFormMessage>
        </UiFormItem>

        <button
          class="mt-5 block border-0 bg-transparent p-0 text-left text-[15px] font-medium text-[var(--color-primary)] underline-offset-4 hover:underline"
          type="button"
          @click="showAccountAssistance"
        >
          パスワードをお忘れですか？
        </button>

        <p v-if="errorMessage" class="mt-4 mb-0 text-[13px] font-bold text-[#b33a2b]" role="alert">
          {{ errorMessage }}
        </p>
        <p
          v-if="assistanceMessage"
          class="mt-4 mb-0 text-[13px] leading-5 text-[var(--color-muted)]"
          role="status"
        >
          管理者アカウントの発行・パスワード再設定については、システム管理担当者にお問い合わせください。
        </p>
        <UiButton
          class="mt-6 min-h-14 w-full rounded-[10px] text-base disabled:cursor-not-allowed disabled:border-[#9db7a7] disabled:bg-[#9db7a7]"
          type="submit"
          :disabled="isSubmitting"
          >{{ isSubmitting ? 'ログイン中…' : 'ログインする' }}</UiButton
        >

        <p
          class="mt-5 mb-0 flex flex-wrap items-center gap-x-4 gap-y-2 text-[15px] text-[#728078]"
        >
          <span>アカウントをお持ちでない方</span>
          <button
            class="border-0 bg-transparent p-0 font-medium text-[var(--color-primary)] underline underline-offset-2"
            type="button"
            @click="showAccountAssistance"
          >
            管理者登録
          </button>
        </p>
      </form>
    </section>
  </main>
</template>
