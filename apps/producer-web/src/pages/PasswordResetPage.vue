<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { z } from 'zod'
import { CircleCheck, Eye, EyeOff, LockKeyhole, LoaderCircle, Mail } from 'lucide-vue-next'
import { UiButton, UiCard, UiInput, UiFormLabel, UiFormMessage } from '@minorikun/ui'
import RegistrationShell from '@/components/registration/RegistrationShell.vue'
import {
  useCompletePasswordResetMutation,
  useValidatePasswordResetMutation,
  useStartPasswordResetMutation,
} from '@/services/password-reset/password-reset.mutation'
import { getApiErrorPayload } from '@/lib/api-error'
import { queryClient } from '@/lib/query'

const resetEmailSchema = z
  .string()
  .trim()
  .email('正しいメールアドレスを入力してください。')
  .max(255)
const resetPasswordSchema = z
  .object({
    password: z
      .string()
      .refine((value) => {
        const length = Array.from(value).length
        return length >= 8 && length <= 64
      }, '8〜64文字で入力してください。')
      .refine(
        (value) =>
          /[A-Z]/.test(value) &&
          /[a-z]/.test(value) &&
          /[0-9]/.test(value) &&
          /[^\p{L}\p{N}\s]/u.test(value),
        '大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。',
      ),
    password_confirmation: z.string().min(1, '確認用パスワードを入力してください。'),
  })
  .refine((value) => value.password === value.password_confirmation, {
    path: ['password_confirmation'],
    message: 'パスワードが一致しません。',
  })

const route = useRoute()
const router = useRouter()
const email = ref('')
const password = ref('')
const confirmation = ref('')
const visible = ref(false)
const confirmationVisible = ref(false)
const state = ref<'request' | 'sent' | 'checking' | 'form' | 'invalid' | 'error' | 'success'>(
  'request',
)
const errors = ref<Record<string, string>>({})
const error = ref('')
const start = useStartPasswordResetMutation()
const validate = useValidatePasswordResetMutation()
const complete = useCompletePasswordResetMutation()
const pending = ref(false)
let credentials = { email: '', token: '' }
let generation = 0
let redirectTimer: ReturnType<typeof globalThis.setTimeout> | undefined

async function initialize() {
  globalThis.clearTimeout(redirectTimer)
  const current = ++generation
  password.value = ''
  confirmation.value = ''
  errors.value = {}
  error.value = ''
  if (route.name !== 'password-reset-confirm') {
    state.value = 'request'
    credentials = { email: '', token: '' }
    return
  }
  const fragment = new globalThis.URLSearchParams(globalThis.location.hash.slice(1))
  credentials = { email: fragment.get('email') ?? '', token: fragment.get('token') ?? '' }
  // Credentials remain in memory; reopen the email link after a reload.
  globalThis.history.replaceState(globalThis.history.state, '', globalThis.location.pathname)
  if (
    !resetEmailSchema.safeParse(credentials.email).success ||
    !/^[A-Za-z0-9]{64}$/.test(credentials.token)
  ) {
    state.value = 'invalid'
    return
  }
  await check(current)
}
async function check(current = generation) {
  state.value = 'checking'
  try {
    await validate.mutateAsync(credentials)
    if (current === generation) state.value = 'form'
  } catch (cause) {
    if (current !== generation) return
    const code = getApiErrorPayload(cause)?.code
    state.value =
      code === 'PASSWORD_RESET_TOKEN_INVALID' || code === 'VALIDATION_ERROR' ? 'invalid' : 'error'
  } finally {
    validate.reset()
  }
}
async function submit() {
  if (pending.value) return
  errors.value = {}
  error.value = ''
  const requesting = state.value === 'request'
  const current = generation
  const result = requesting
    ? resetEmailSchema.safeParse(email.value)
    : resetPasswordSchema.safeParse({
        password: password.value,
        password_confirmation: confirmation.value,
      })
  if (!result.success) {
    for (const issue of result.error.issues)
      errors.value[requesting ? 'email' : String(issue.path[0])] ??= issue.message
    return
  }
  pending.value = true
  try {
    if (requesting && typeof result.data === 'string') {
      await start.mutateAsync({ email: result.data })
      if (current === generation) state.value = 'sent'
    } else if (typeof result.data !== 'string') {
      await complete.mutateAsync({ ...credentials, ...result.data })
      password.value = ''
      confirmation.value = ''
      credentials = { email: '', token: '' }
      queryClient.clear()
      if (current === generation) {
        state.value = 'success'
        redirectTimer = globalThis.setTimeout(() => {
          if (current === generation) void router.replace('/login')
        }, 5000)
      }
    }
  } catch (cause) {
    if (current !== generation) return
    const payload = getApiErrorPayload(cause)
    if (payload?.code === 'PASSWORD_RESET_TOKEN_INVALID') state.value = 'invalid'
    else {
      for (const [key, messages] of Object.entries(payload?.errors ?? {}))
        errors.value[key] = messages[0] ?? ''
      error.value = Object.keys(errors.value).length
        ? ''
        : '処理を完了できませんでした。時間をおいてもう一度お試しください。'
    }
  } finally {
    pending.value = false
    start.reset()
    complete.reset()
  }
}
onMounted(initialize)
watch(() => route.name, initialize)
onBeforeUnmount(() => {
  generation++
  globalThis.clearTimeout(redirectTimer)
  password.value = ''
  confirmation.value = ''
  credentials = { email: '', token: '' }
})
</script>

<template>
  <RegistrationShell compact-mobile>
    <UiCard
      class="w-full max-w-[440px] rounded-2xl border-[#d5e2da] bg-white p-7 shadow-sm min-[761px]:p-8"
    >
      <template v-if="state === 'checking'">
        <LoaderCircle class="mb-5 size-10 animate-spin text-[#237b4d]" aria-hidden="true" />
        <p role="status">再設定用リンクを確認しています…</p>
      </template>
      <template v-else-if="state === 'success'">
        <CircleCheck class="mb-5 size-10 text-[#237b4d]" aria-hidden="true" />
        <div role="status" aria-live="polite">
          <h1 class="text-2xl font-bold text-[#1e3f30]">パスワードを再設定しました</h1>
          <p class="mt-4 text-sm leading-7 text-[#708278]">5秒後にログイン画面へ移動します。</p>
        </div>
        <UiButton class="mt-6 min-h-12 w-full bg-[#237b4d]" @click="router.replace('/login')">
          ログインする
        </UiButton>
      </template>
      <template v-else-if="state === 'sent'">
        <CircleCheck class="mb-5 size-10 text-[#237b4d]" aria-hidden="true" />
        <div role="status" aria-live="polite">
          <h1 class="text-2xl font-bold text-[#1e3f30]">メールを送信しました</h1>
          <p class="mt-4 text-sm leading-7 text-[#708278]">
            該当するアカウントがある場合、パスワード再設定用メールを送信しました。メール内のリンクから再設定してください。
          </p>
          <p class="mt-3 text-sm leading-7 text-[#708278]">
            届かない場合は、迷惑メールフォルダと入力したメールアドレスをご確認ください。
          </p>
        </div>
        <UiButton variant="outline" class="mt-6 w-full" @click="state = 'request'">
          もう一度メールを送信する
        </UiButton>
      </template>
      <template v-else-if="state === 'invalid'">
        <h1 class="text-2xl font-bold text-[#1e3f30]">リンクをご確認ください</h1>
        <p class="mt-4 text-sm leading-7 text-[#708278]" role="alert">
          再設定用リンクが無効、使用済み、または有効期限切れです。もう一度メールを送信してください。
        </p>
        <UiButton
          class="mt-6 min-h-12 w-full bg-[#237b4d]"
          @click="router.replace('/password-reset')"
        >
          再設定リンクを再送する
        </UiButton>
      </template>
      <template v-else-if="state === 'error'">
        <p role="alert">リンクを確認できませんでした。時間をおいてもう一度お試しください。</p>
        <UiButton class="mt-6 w-full" @click="check()">もう一度試す</UiButton>
      </template>
      <template v-else>
        <Mail v-if="state === 'request'" class="mb-5 size-10 text-[#237b4d]" aria-hidden="true" />
        <LockKeyhole v-else class="mb-5 size-10 text-[#237b4d]" aria-hidden="true" />
        <h1 class="text-2xl font-bold text-[#1e3f30]">
          {{ state === 'request' ? 'パスワード再設定' : '新しいパスワードを設定' }}
        </h1>
        <p v-if="state === 'request'" class="mt-3 text-sm leading-7 text-[#708278]">
          登録したメールアドレスに、パスワード再設定用のリンクを送信します。
        </p>
        <p v-else id="password-guidance" class="mt-3 text-xs leading-6 text-[#708278]">
          8〜64文字で、大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。
        </p>
        <form class="mt-6 grid gap-5" novalidate @submit.prevent="submit">
          <div v-if="state === 'request'" class="grid gap-2">
            <UiFormLabel for="reset-email">メールアドレス</UiFormLabel>
            <UiInput
              id="reset-email"
              v-model="email"
              type="email"
              autocomplete="email"
              required
              class="h-12"
              :disabled="pending"
              :aria-invalid="Boolean(errors.email)"
              :aria-describedby="errors.email ? 'reset-email-error' : undefined"
            />
            <UiFormMessage v-if="errors.email" id="reset-email-error" role="alert">
              {{ errors.email }}
            </UiFormMessage>
          </div>
          <template v-else>
            <div class="grid gap-2">
              <UiFormLabel for="new-password">
                新しいパスワード
                <span class="text-red-600">*</span>
              </UiFormLabel>
              <div class="relative">
                <UiInput
                  id="new-password"
                  v-model="password"
                  :type="visible ? 'text' : 'password'"
                  autocomplete="new-password"
                  required
                  class="h-12 pr-12"
                  :disabled="pending"
                  :aria-invalid="Boolean(errors.password)"
                  :aria-describedby="
                    errors.password ? 'password-guidance new-password-error' : 'password-guidance'
                  "
                />
                <UiButton
                  variant="ghost"
                  class="absolute top-1/2 right-2 -translate-y-1/2 p-2"
                  :aria-label="visible ? 'パスワードを隠す' : 'パスワードを表示する'"
                  :aria-pressed="visible"
                  @click="visible = !visible"
                >
                  <Eye v-if="visible" class="size-5" aria-hidden="true" />
                  <EyeOff v-else class="size-5" aria-hidden="true" />
                </UiButton>
              </div>
              <UiFormMessage v-if="errors.password" id="new-password-error" role="alert">
                {{ errors.password }}
              </UiFormMessage>
            </div>
            <div class="grid gap-2">
              <UiFormLabel for="confirm-password">
                パスワード（確認）
                <span class="text-red-600">*</span>
              </UiFormLabel>
              <div class="relative">
                <UiInput
                  id="confirm-password"
                  v-model="confirmation"
                  :type="confirmationVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  required
                  class="h-12 pr-12"
                  :disabled="pending"
                  :aria-invalid="Boolean(errors.password_confirmation)"
                  :aria-describedby="
                    errors.password_confirmation ? 'confirm-password-error' : undefined
                  "
                />
                <UiButton
                  variant="ghost"
                  class="absolute top-1/2 right-2 -translate-y-1/2 p-2"
                  :aria-label="
                    confirmationVisible ? '確認用パスワードを隠す' : '確認用パスワードを表示する'
                  "
                  :aria-pressed="confirmationVisible"
                  @click="confirmationVisible = !confirmationVisible"
                >
                  <Eye v-if="confirmationVisible" class="size-5" aria-hidden="true" />
                  <EyeOff v-else class="size-5" aria-hidden="true" />
                </UiButton>
              </div>
              <UiFormMessage
                v-if="errors.password_confirmation"
                id="confirm-password-error"
                role="alert"
              >
                {{ errors.password_confirmation }}
              </UiFormMessage>
            </div>
          </template>
          <UiFormMessage v-if="error" role="alert">{{ error }}</UiFormMessage>
          <UiButton type="submit" class="min-h-12 w-full bg-[#237b4d]" :disabled="pending">
            {{
              pending
                ? '処理中…'
                : state === 'request'
                  ? '再設定リンクを送信する'
                  : 'パスワードを再設定する'
            }}
          </UiButton>
        </form>
      </template>
      <RouterLink
        v-if="state !== 'success'"
        to="/login"
        class="mt-6 block text-center text-sm font-medium text-[#368357] underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4"
      >
        ログインに戻る
      </RouterLink>
    </UiCard>
  </RegistrationShell>
</template>
