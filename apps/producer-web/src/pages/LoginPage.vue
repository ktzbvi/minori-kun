<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { Eye, EyeOff, Leaf, LockKeyhole, Mail } from 'lucide-vue-next'
import { z } from 'zod'
import { UiButton, UiCard, UiInput, UiFormLabel, UiFormMessage } from '@minorikun/ui'
import { useProducerLogin } from '@/composables/useProducerLogin'

const email = ref('')
const password = ref('')
const route = useRoute()
const passwordVisible = ref(false)
const errors = ref<{ email?: string; password?: string }>({})
const { isLoggingIn, login } = useProducerLogin(() => {
  password.value = ''
})

const recoveryNotice = computed(() => {
  if (route.query.recovery === 'registration') return '登録結果を確認できませんでした。登録時のメールアドレスとパスワードでログインしてください。'
  if (route.query.recovery === 'registration-complete') return '登録は完了しています。設定したメールアドレスとパスワードでログインしてください。'
  return ''
})

// SCR-P-001 / P01-01: login must not enforce new-account password rules.
const loginSchema = z.object({
  email: z
    .string()
    .trim()
    .min(1, 'メールアドレスを入力してください。')
    .email('正しいメールアドレスを入力してください。'),
  password: z.string().min(1, 'パスワードを入力してください。'),
})

function submit() {
  if (isLoggingIn.value) return
  const result = loginSchema.safeParse({ email: email.value, password: password.value })
  errors.value = {}
  if (!result.success) {
    const fields = result.error.flatten().fieldErrors
    errors.value = { email: fields.email?.[0], password: fields.password?.[0] }
    return
  }
  login(result.data.email, result.data.password)
}
</script>

<template>
  <main class="min-h-svh bg-[#f2f6f3] min-[761px]:grid min-[761px]:grid-cols-[46.3%_minmax(0,1fr)]">
    <aside
      class="hidden items-center bg-[#123729] px-[clamp(40px,3.8vw,64px)] py-10 text-[#eaf0dd] min-[761px]:flex"
    >
      <div class="w-full max-w-[700px]">
        <p class="mb-3 text-[28px] font-extrabold tracking-[.04em] italic">
          みのりくん
        </p>
        <h2 class="mb-3 text-lg font-semibold text-[#a8c2ae]">
          生産者向けログイン
        </h2>
        <p class="text-sm leading-[1.7] text-[#9bb5a5]">
          登録済みの生産者アカウントでログインしてください。
        </p>
      </div>
    </aside>

    <section
      class="relative isolate grid min-h-svh min-w-0 place-items-center overflow-hidden bg-linear-[145deg,#e5f3ed_0%,#f4f8ef_68%,#f4f7ec_100%] px-4 pt-7 pb-[175px] min-[761px]:bg-[#f2f6f3] min-[761px]:bg-none min-[761px]:px-[clamp(32px,4vw,64px)] min-[761px]:py-10"
    >
      <div
        class="absolute right-[12%] bottom-[14%] -z-10 size-[120px] rounded-full bg-[#eee6b7] opacity-50 min-[761px]:hidden"
        aria-hidden="true"
      ></div>
      <svg
        class="absolute inset-x-0 bottom-0 -z-10 h-[190px] w-full min-[761px]:hidden"
        viewBox="0 0 1440 360"
        preserveAspectRatio="none"
        aria-hidden="true"
      >
        <path
          d="M0 110C190 10 310 42 470 112c210 92 300 18 490-18 192-37 294-67 480-28v294H0Z"
          fill="#dcefe6"
        />
        <path
          d="M0 178c180-88 292-70 440-4 210 93 304 44 494-12 220-66 336-78 506-20v218H0Z"
          fill="#bfe0ce"
        />
        <path
          d="M0 252c171-95 316-83 470-26 204 75 288 54 480-15 214-77 334-81 490-45v210H0Z"
          fill="#91c8a9"
        />
        <g fill="none" stroke="#428c67" stroke-linecap="round" stroke-width="7" opacity=".43">
          <path
            d="M-20 340c156-126 301-151 470-174M100 360c166-120 300-140 501-175m-258 175c147-106 299-145 478-161m96 161c114-78 245-111 416-132"
          />
          <path d="m170 310 0-86 76-68 76 68v86m-132 0v-87h112v87m-145 0h176" />
        </g>
        <g fill="#ecd990" opacity=".85">
          <circle cx="602" cy="120" r="11" />
          <circle cx="642" cy="101" r="8" />
          <circle cx="684" cy="124" r="9" />
        </g>
      </svg>
      <UiCard
        class="w-full max-w-[724px] rounded-[30px] border-0 bg-white px-6 pt-[34px] pb-8 shadow-[0_25px_60px_rgb(33_75_54/11%)] max-[360px]:px-4.5 min-[761px]:max-w-[496px] min-[761px]:-translate-y-[4vh] min-[761px]:rounded-none min-[761px]:bg-transparent min-[761px]:p-0 min-[761px]:shadow-none"
      >
        <header class="flex items-center gap-3.5 min-[761px]:hidden">
          <span
            class="grid size-[62px] shrink-0 place-items-center rounded-full bg-[#e8f4ed] text-white"
            aria-hidden="true"
          >
            <Leaf class="size-[42px] rounded-full bg-[#20794d] p-[7px]" :stroke-width="2.2" />
          </span>
          <span class="text-[29px] font-extrabold tracking-[.08em] text-[#20794d]">みのりくん</span>
        </header>
        <section class="mt-7 min-[761px]:mt-0 min-[761px]:mb-5">
          <h1
            class="text-[22px] leading-[1.4] font-bold text-[#183c2c] min-[761px]:text-2xl min-[761px]:text-[#1e2923]"
          >
            <span class="hidden min-[761px]:inline">生産者ログイン</span>
            <span class="min-[761px]:hidden">生産者ポータル</span>
          </h1>
          <p class="mt-2.5 hidden text-base text-[#87968d] min-[761px]:block">
            メールアドレスとパスワードを入力してください。
          </p>
          <p class="mt-[7px] text-[15px] text-[#687b70] min-[761px]:hidden">
            生産者アカウントでログインしてください。
          </p>
          <div v-if="recoveryNotice" class="mt-4 rounded-lg bg-[#fff8e8] px-4 py-3 text-sm leading-relaxed text-[#614c22]" role="status">
            {{ recoveryNotice }}
          </div>
        </section>

        <form
          class="mt-[34px] grid gap-5 min-[761px]:mt-0 min-[761px]:gap-4"
          novalidate
          :aria-busy="isLoggingIn"
          @submit.prevent="submit"
        >
          <div class="grid gap-[9px] min-[761px]:gap-2">
            <UiFormLabel
              for="producer-email"
              class="text-base font-bold text-[#1e3f30] min-[761px]:text-sm min-[761px]:font-medium min-[761px]:text-[#87968d]"
            >
              メールアドレス
            </UiFormLabel>
            <div class="relative">
              <Mail
                class="pointer-events-none absolute top-1/2 left-3.5 z-10 size-[21px] -translate-y-1/2 text-[#708278] min-[761px]:hidden"
                aria-hidden="true"
              />
              <UiInput
                id="producer-email"
                v-model="email"
                type="email"
                name="email"
                autocomplete="username"
                inputmode="email"
                placeholder="メールアドレスを入力"
                required
                class="h-[58px] rounded-2xl border-[1.5px] border-[#cbded2] pr-4 pl-12 text-base min-[761px]:h-12 min-[761px]:rounded-lg min-[761px]:border-[#d5e2da] min-[761px]:px-4 min-[761px]:text-base"
                :disabled="isLoggingIn"
                :aria-invalid="Boolean(errors.email)"
                :aria-describedby="errors.email ? 'producer-email-error' : undefined"
              />
            </div>
            <UiFormMessage v-if="errors.email" id="producer-email-error" role="alert">
              {{ errors.email }}
            </UiFormMessage>
          </div>
          <div class="grid gap-[9px] min-[761px]:gap-2">
            <UiFormLabel
              for="producer-password"
              class="text-base font-bold text-[#1e3f30] min-[761px]:text-sm min-[761px]:font-medium min-[761px]:text-[#87968d]"
            >
              パスワード
            </UiFormLabel>
            <div class="relative">
              <LockKeyhole
                class="pointer-events-none absolute top-1/2 left-3.5 z-10 size-[21px] -translate-y-1/2 text-[#708278] min-[761px]:hidden"
                aria-hidden="true"
              />
              <UiInput
                id="producer-password"
                v-model="password"
                :type="passwordVisible ? 'text' : 'password'"
                name="password"
                autocomplete="current-password"
                placeholder="パスワードを入力"
                required
                class="h-[58px] rounded-2xl border-[1.5px] border-[#cbded2] pr-14 pl-12 text-base min-[761px]:h-12 min-[761px]:rounded-lg min-[761px]:border-[#d5e2da] min-[761px]:pr-11 min-[761px]:pl-4 min-[761px]:text-base"
                :disabled="isLoggingIn"
                :aria-invalid="Boolean(errors.password)"
                :aria-describedby="errors.password ? 'producer-password-error' : undefined"
              />
              <UiButton
                variant="ghost"
                class="absolute top-1/2 right-3 min-h-9 -translate-y-1/2 rounded-md border-0 p-1.5 text-[#71857a]"
                :aria-label="passwordVisible ? 'パスワードを隠す' : 'パスワードを表示する'"
                :aria-pressed="passwordVisible"
                @click="passwordVisible = !passwordVisible"
              >
                <Eye
                  v-if="passwordVisible"
                  class="size-[21px] min-[761px]:size-5"
                  aria-hidden="true"
                />
                <EyeOff v-else class="size-[21px] min-[761px]:size-5" aria-hidden="true" />
              </UiButton>
            </div>
            <UiFormMessage v-if="errors.password" id="producer-password-error" role="alert">
              {{ errors.password }}
            </UiFormMessage>
          </div>
          <div class="flex justify-end min-[761px]:justify-start">
            <RouterLink
              to="/password-reset"
              class="text-sm font-medium text-[#368357] focus-visible:outline-2 focus-visible:outline-offset-4"
            >
              パスワードをお忘れですか？
            </RouterLink>
          </div>
          <UiButton
            type="submit"
            :disabled="isLoggingIn"
            class="mt-0.5 min-h-[62px] w-full rounded-2xl border-0 bg-linear-to-r from-[#2b9257] to-[#17643e] text-lg shadow-[0_12px_22px_rgb(29_98_59/22%)] min-[761px]:min-h-12 min-[761px]:rounded-lg min-[761px]:bg-[#237b4d] min-[761px]:bg-none min-[761px]:text-base min-[761px]:shadow-none"
          >
            {{ isLoggingIn ? 'ログイン中…' : 'ログインする' }}
          </UiButton>
        </form>
        <footer
          class="mt-[30px] flex flex-wrap justify-center gap-x-3 gap-y-[5px] text-center text-sm text-[#87968d] min-[761px]:mt-5 min-[761px]:justify-start min-[761px]:gap-x-4"
        >
          <span>アカウントをお持ちでない方</span>
          <RouterLink
            to="/register"
            class="font-medium text-[#368357] underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4"
          >
            生産者登録
          </RouterLink>
        </footer>
      </UiCard>
    </section>
  </main>
</template>
