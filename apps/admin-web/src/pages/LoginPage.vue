<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import axios from 'axios'
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { toast, UiButton } from '@minorikun/ui'
import { authApi } from '@/lib/api'
import { queryClient } from '@/lib/query'

const errorMessage = ref('')
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
</script>

<template>
  <main
    class="grid min-h-screen min-w-80 bg-[#f6f8f6] text-[var(--color-text)] md:grid-cols-[minmax(360px,44%)_1fr]"
  >
    <section
      class="relative flex min-h-[220px] items-center overflow-hidden bg-[#123c2b] px-7 py-10 text-white md:min-h-screen md:px-[clamp(40px,7vw,104px)] md:py-16"
      aria-label="みのりくん管理ポータル"
    >
      <span
        class="pointer-events-none absolute -right-45 -bottom-[90px] size-[360px] rounded-full border border-white/10"
        aria-hidden="true"
      />
      <span
        class="pointer-events-none absolute right-[-80px] bottom-0 size-45 rounded-full border border-white/10"
        aria-hidden="true"
      />
      <div class="relative z-10">
        <p class="m-0 text-[32px] font-extrabold tracking-[0.04em] md:text-[clamp(34px,4vw,50px)]">
          みのりくん
        </p>
        <p class="mt-[18px] mb-0 text-lg font-bold text-white/80">管理ポータル</p>
        <span class="mt-7 block h-[3px] w-12 rounded-sm bg-[#7dc69a]" aria-hidden="true" />
        <p class="mt-[18px] mb-0 text-sm text-white/60">運営管理者専用</p>
      </div>
    </section>

    <section class="grid place-items-center px-6 py-10 md:px-6 md:py-12">
      <form class="grid w-full max-w-[440px] gap-[22px]" novalidate @submit.prevent="submit">
        <header class="mb-2.5">
          <p class="mb-3 text-[11px] font-extrabold tracking-[0.16em] text-[var(--color-primary)]">
            MINORI-KUN ADMIN
          </p>
          <h1 class="m-0 text-3xl leading-[1.35] font-bold text-[var(--color-text)]">
            管理者ログイン
          </h1>
          <p class="mt-2.5 mb-0 text-sm text-[var(--color-muted)]">
            管理者アカウントでログインしてください。
          </p>
        </header>

        <div class="grid gap-2">
          <label class="text-[13px] font-bold text-[#35483d]" for="admin-email">
            管理者メールアドレス
          </label>
          <input
            id="admin-email"
            v-model="email"
            v-bind="emailAttrs"
            class="min-h-12 w-full rounded-[9px] border border-[#cad8ce] bg-white px-3.5 text-[15px] outline-none placeholder:text-[#98a69d] focus:border-[var(--color-primary)] focus:ring-3 focus:ring-[#237f4b]/15"
            type="email"
            autocomplete="username"
            inputmode="email"
            placeholder="メールアドレスを入力"
            :aria-invalid="Boolean(errors.email)"
            :aria-describedby="errors.email ? 'admin-email-error' : undefined"
          />
          <p
            v-if="errors.email"
            id="admin-email-error"
            class="m-0 text-[12px] font-medium text-[#b33a2b]"
            role="alert"
          >
            {{ errors.email }}
          </p>
        </div>

        <div class="grid gap-2">
          <label class="text-[13px] font-bold text-[#35483d]" for="admin-password">
            パスワード
          </label>
          <input
            id="admin-password"
            v-model="password"
            v-bind="passwordAttrs"
            class="min-h-12 w-full rounded-[9px] border border-[#cad8ce] bg-white px-3.5 text-[15px] outline-none placeholder:text-[#98a69d] focus:border-[var(--color-primary)] focus:ring-3 focus:ring-[#237f4b]/15"
            type="password"
            autocomplete="current-password"
            placeholder="パスワードを入力"
            :aria-invalid="Boolean(errors.password)"
            :aria-describedby="errors.password ? 'admin-password-error' : undefined"
          />
          <p
            v-if="errors.password"
            id="admin-password-error"
            class="m-0 text-[12px] font-medium text-[#b33a2b]"
            role="alert"
          >
            {{ errors.password }}
          </p>
        </div>

        <p v-if="errorMessage" class="-mt-1 mb-0 text-[13px] font-bold text-[#b33a2b]" role="alert">
          {{ errorMessage }}
        </p>
        <UiButton
          class="mt-0.5 w-full disabled:cursor-not-allowed disabled:border-[#9db7a7] disabled:bg-[#9db7a7]"
          type="submit"
          :disabled="isSubmitting"
          >{{ isSubmitting ? 'ログイン中…' : 'ログインする' }}</UiButton
        >
      </form>
    </section>
  </main>
</template>
