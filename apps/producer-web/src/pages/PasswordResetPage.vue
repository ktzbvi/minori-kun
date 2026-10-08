<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { z } from 'zod'
import {
  ChevronLeft,
  CircleCheck,
  CircleAlert,
  MailCheck,
  Eye,
  EyeOff,
  LockKeyhole,
  LoaderCircle,
  Mail,
} from 'lucide-vue-next'
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
  <RegistrationShell compact-mobile scenic>
    <UiCard
      class="w-full max-w-[560px] rounded-3xl border-0 bg-white px-6 py-8 shadow-[0_20px_50px_rgb(33_75_54/12%)] min-[761px]:p-10"
    >
      <header class="mb-6 flex items-center gap-3">
        <svg class="size-12 shrink-0" viewBox="0 0 88 88" aria-hidden="true">
          <circle cx="44" cy="44" r="44" fill="#e8f4ed" />
          <path
            d="M44 17c-15 0-26 15-26 32 0 15 10 25 26 25s26-10 26-25c0-17-11-32-26-32Z"
            fill="#20794d"
          />
          <path d="M51 23c1-10 9-16 17-15-2 8-8 15-17 15Z" fill="#68ae7b" />
          <path
            d="M44 20c-5 15-5 32 0 48"
            fill="none"
            stroke="#acd1b5"
            stroke-width="4"
            stroke-linecap="round"
          />
          <g fill="white">
            <circle cx="36" cy="46" r="3.5" />
            <circle cx="54" cy="46" r="3.5" />
          </g>
          <path
            d="M38 58q6 8 12 0"
            fill="none"
            stroke="white"
            stroke-width="3"
            stroke-linecap="round"
          />
        </svg>
        <span class="text-2xl font-extrabold tracking-widest text-[#20794d]">みのりくん</span>
      </header>
      <template v-if="state === 'checking'">
        <div class="mb-5 grid size-14 place-items-center rounded-full bg-[#e8f4ed] text-[#237b4d]">
          <LoaderCircle class="size-7 animate-spin" aria-hidden="true" />
        </div>
        <div role="status" aria-live="polite">
          <h1 class="text-xl font-bold text-[#1e3f30] min-[761px]:text-2xl">
            リンクを確認しています
          </h1>
          <p class="mt-3 text-sm leading-7 text-[#708278]">
            確認が完了するまで、しばらくお待ちください。
          </p>
        </div>
      </template>
      <template v-else-if="state === 'success'">
        <div class="mb-5 grid size-14 place-items-center rounded-full bg-[#e8f4ed] text-[#237b4d]">
          <CircleCheck class="size-7" aria-hidden="true" />
        </div>
        <div role="status" aria-live="polite">
          <h1 class="text-xl min-[761px]:text-2xl font-bold text-[#1e3f30]">
            パスワードを再設定しました
          </h1>
          <p class="mt-4 text-sm leading-7 text-[#708278]">5秒後にログイン画面へ移動します。</p>
        </div>
        <UiButton
          class="mt-6 min-h-14 w-full rounded-xl border-0 bg-linear-to-r from-[#2b8c57] to-[#17623e] text-base font-bold text-white shadow-lg"
          @click="router.replace('/login')"
        >
          ログインする
        </UiButton>
      </template>
      <template v-else-if="state === 'sent'">
        <div class="mb-5 grid size-14 place-items-center rounded-full bg-[#e8f4ed] text-[#237b4d]">
          <MailCheck class="size-7" aria-hidden="true" />
        </div>
        <div role="status" aria-live="polite">
          <h1 class="text-xl min-[761px]:text-2xl font-bold text-[#1e3f30]">
            メールを送信しました
          </h1>
          <p class="mt-4 text-sm leading-7 text-[#708278]">
            該当するアカウントがある場合、パスワード再設定用メールを送信しました。メール内のリンクから再設定してください。
          </p>
          <p class="mt-5 rounded-xl bg-[#f6faf8] px-4 py-4 text-sm leading-6 text-[#687b70]">
            届かない場合は、迷惑メールフォルダと入力したメールアドレスをご確認ください。
          </p>
        </div>
        <UiButton
          class="mt-6 min-h-14 w-full rounded-xl border-0 bg-linear-to-r from-[#2b8c57] to-[#17623e] text-base font-bold text-white shadow-lg"
          @click="state = 'request'"
        >
          もう一度メールを送信する
        </UiButton>
      </template>
      <template v-else-if="state === 'invalid'">
        <div class="mb-5 grid size-14 place-items-center rounded-full bg-amber-50 text-amber-700">
          <CircleAlert class="size-7" aria-hidden="true" />
        </div>
        <h1 class="text-xl min-[761px]:text-2xl font-bold text-[#1e3f30]">
          リンクをご確認ください
        </h1>
        <p class="mt-4 text-sm leading-7 text-[#708278]" role="alert">
          再設定用リンクが無効、使用済み、または有効期限切れです。もう一度メールを送信してください。
        </p>
        <UiButton
          class="mt-6 min-h-14 w-full rounded-xl border-0 bg-linear-to-r from-[#2b8c57] to-[#17623e] text-base font-bold text-white shadow-lg"
          @click="router.replace('/password-reset')"
        >
          再設定リンクを再送する
        </UiButton>
      </template>
      <template v-else-if="state === 'error'">
        <div class="mb-5 grid size-14 place-items-center rounded-full bg-amber-50 text-amber-700">
          <CircleAlert class="size-7" aria-hidden="true" />
        </div>
        <h1 class="text-xl font-bold text-[#1e3f30] min-[761px]:text-2xl">
          リンクを確認できませんでした
        </h1>
        <p class="mt-3 text-sm leading-7 text-[#708278]" role="alert">
          時間をおいてもう一度お試しください。
        </p>
        <UiButton
          class="mt-6 min-h-14 w-full rounded-xl border-0 bg-linear-to-r from-[#2b8c57] to-[#17623e] text-base font-bold text-white shadow-lg"
          @click="check()"
        >
          もう一度試す
        </UiButton>
      </template>
      <template v-else>
        <h1 class="text-xl min-[761px]:text-2xl font-bold text-[#1e3f30]">
          {{ state === 'request' ? 'パスワードを再設定' : '新しいパスワードを設定' }}
        </h1>
        <p v-if="state === 'request'" class="mt-3 text-sm leading-7 text-[#708278]">
          登録時のメールアドレスを入力してください。
        </p>
        <p v-else id="password-guidance" class="mt-3 text-xs leading-6 text-[#708278]">
          8〜64文字で、大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。
        </p>
        <form class="mt-8 grid gap-4" :aria-busy="pending" novalidate @submit.prevent="submit">
          <div v-if="state === 'request'" class="grid gap-2">
            <UiFormLabel for="reset-email" class="text-base font-bold text-[#183c2c]">
              メールアドレス
            </UiFormLabel>
            <div class="relative">
              <Mail
                class="pointer-events-none absolute top-1/2 left-4 z-10 size-5 -translate-y-1/2 text-[#708278]"
                aria-hidden="true"
              />
              <UiInput
                id="reset-email"
                v-model="email"
                type="email"
                inputmode="email"
                placeholder="メールアドレスを入力"
                autocomplete="email"
                required
                class="h-14 rounded-xl border-[#cbded2] pl-12 text-base placeholder:text-[#708278]"
                :disabled="pending"
                :aria-invalid="Boolean(errors.email)"
                :aria-describedby="errors.email ? 'reset-email-error' : undefined"
              />
            </div>
            <UiFormMessage v-if="errors.email" id="reset-email-error" role="alert">
              {{ errors.email }}
            </UiFormMessage>
          </div>
          <template v-else>
            <div class="grid gap-2">
              <UiFormLabel for="new-password" class="text-base font-bold text-[#183c2c]">
                新しいパスワード
                <span class="text-red-600">*</span>
              </UiFormLabel>
              <div class="relative">
                <LockKeyhole
                  class="pointer-events-none absolute top-1/2 left-4 z-10 size-5 -translate-y-1/2 text-[#708278]"
                  aria-hidden="true"
                />
                <UiInput
                  id="new-password"
                  v-model="password"
                  placeholder="新しいパスワードを入力"
                  :type="visible ? 'text' : 'password'"
                  autocomplete="new-password"
                  required
                  class="h-14 rounded-xl border-[#cbded2] pr-12 pl-12 text-base placeholder:text-[#708278]"
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
              <UiFormLabel for="confirm-password" class="text-base font-bold text-[#183c2c]">
                パスワード（確認）
                <span class="text-red-600">*</span>
              </UiFormLabel>
              <div class="relative">
                <LockKeyhole
                  class="pointer-events-none absolute top-1/2 left-4 z-10 size-5 -translate-y-1/2 text-[#708278]"
                  aria-hidden="true"
                />
                <UiInput
                  id="confirm-password"
                  v-model="confirmation"
                  placeholder="新しいパスワードを再入力"
                  :type="confirmationVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  required
                  class="h-14 rounded-xl border-[#cbded2] pr-12 pl-12 text-base placeholder:text-[#708278]"
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
          <UiButton
            type="submit"
            class="min-h-14 w-full rounded-xl border-0 bg-linear-to-r from-[#2b8c57] to-[#17623e] text-base font-bold text-white shadow-lg"
            :disabled="pending"
          >
            {{
              pending
                ? '処理中…'
                : state === 'request'
                  ? '再設定リンクを送信'
                  : 'パスワードを再設定する'
            }}
          </UiButton>
        </form>
        <p
          v-if="state === 'request'"
          class="mt-3 rounded-xl bg-[#f6faf8] px-4 py-4 text-xs leading-5 text-[#687b70]"
        >
          入力内容に該当するアカウントがある場合、
          <br />
          再設定メールを送信します。
        </p>
      </template>
      <RouterLink
        v-if="state !== 'success'"
        to="/login"
        class="mt-6 flex items-center text-sm font-bold text-[#20794d] focus-visible:outline-2 focus-visible:outline-offset-4"
      >
        <ChevronLeft class="size-4 shrink-0" aria-hidden="true" />
        生産者ログインに戻る
      </RouterLink>
    </UiCard>
  </RegistrationShell>
</template>
