<script setup lang="ts">
import { ChevronLeft, ChevronRight, Leaf, LoaderCircle, ArrowRight, Check } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { UiButton, UiCard, UiInput, UiFormLabel, UiFormMessage } from '@minorikun/ui'
import { useBuyerRegisterOtp } from '@/composables/useBuyerRegisterOtp'

const {
  email,
  errors,
  isSubmitting,
  isResending,
  code,
  codeAttrs,
  canResend,
  resendLabel,
  submit,
  resend,
  changeEmail,
  goBack,
} = useBuyerRegisterOtp()
</script>

<template>
  <main
    class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6 lg:bg-[#f3f6f3] lg:p-10"
  >
    <section
      class="min-h-screen w-full max-w-md bg-[#fbfcfa] sm:min-h-[728px] sm:shadow-sm lg:grid lg:min-h-[680px] lg:max-w-6xl lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:overflow-hidden lg:rounded-3xl lg:border lg:border-[#d7e3da] lg:bg-white lg:shadow-xl lg:shadow-[#14382a]/5"
    >
      <aside
        class="relative hidden overflow-hidden bg-[#173e2c] p-12 text-white lg:flex lg:flex-col"
        aria-label="みのりくん"
      >
        <RouterLink
          to="/"
          class="relative z-10 flex w-fit items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"
        >
          <span class="grid size-11 place-items-center rounded-full bg-white/10">
            <Leaf class="size-6 text-[#c5e2a3]" aria-hidden="true" />
          </span>
          <span class="text-2xl font-extrabold tracking-widest">みのりくん</span>
        </RouterLink>
        <div class="relative z-10 mt-20 max-w-sm">
          <p class="mb-5 text-sm font-medium tracking-widest text-[#b9d7c0]">
            みのりくんへ、ようこそ。
          </p>
          <h2 class="text-3xl leading-relaxed font-bold xl:text-4xl">
            あと少しで、
            <br />
            旬との出会いへ。
          </h2>
          <p class="mt-6 text-base leading-8 text-[#c0d5c6]">
            メールに届いたコードを確認。
            <br />
            確認後、お客様情報の入力へ進みます。
          </p>
        </div>
        <svg
          class="pointer-events-none absolute inset-x-0 bottom-0 h-64 w-full"
          viewBox="0 0 560 260"
          preserveAspectRatio="xMidYMax slice"
          aria-hidden="true"
        >
          <circle cx="438" cy="71" r="35" fill="#d9dfa2" opacity=".9" />
          <path d="M0 138Q90 62 225 137T560 117V260H0Z" fill="#356449" />
          <path d="M0 180Q135 115 270 176T560 153V260H0Z" fill="#568664" />
          <path d="M0 220Q120 164 275 215T560 194V260H0Z" fill="#81a879" />
          <g fill="none" stroke="#c8ddb2" stroke-width="2" opacity=".4">
            <path
              d="M0 260Q105 196 230 183M75 260Q182 215 310 207M180 260Q283 225 408 225M341 260Q425 220 560 213"
            />
            <path d="M90 185v-45l30-28 30 28v45m-50 0v-40h40v40m-59 0h78" />
          </g>
        </svg>
        <RouterLink
          to="/"
          class="relative z-10 mt-auto flex w-fit items-center gap-2 pt-36 text-sm font-medium text-[#dce8d5] underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"
        >
          商品を見に行く
          <ArrowRight class="size-4" aria-hidden="true" />
        </RouterLink>
      </aside>

      <div class="min-w-0 lg:flex lg:flex-col">
        <header
          class="flex h-[65px] items-center gap-3 border-b border-[#e1e8e1] px-5 lg:h-auto lg:border-0 lg:px-10 lg:pt-8"
        >
          <UiButton
            variant="ghost"
            class="size-9 min-h-9 rounded-full p-0 text-[#267c4a] lg:w-auto lg:gap-1 lg:px-2 lg:text-sm"
            aria-label="戻る"
            @click="goBack"
          >
            <ChevronLeft class="size-5" :stroke-width="2.5" aria-hidden="true" />
            <span class="hidden lg:inline">戻る</span>
          </UiButton>
          <span class="text-sm font-semibold text-[#227644] lg:hidden">メール確認</span>
        </header>
        <div class="px-6 pt-8 pb-12 lg:mx-auto lg:w-full lg:max-w-md lg:px-8 lg:pt-10 lg:pb-10">
          <div class="mb-7 lg:hidden">
            <div class="mb-7 flex items-center gap-2">
              <span class="grid size-10 place-items-center rounded-full bg-[#e4f3e9]">
                <Leaf class="size-6 text-[#237f4b]" aria-hidden="true" />
              </span>
              <span class="text-xl font-extrabold tracking-widest text-[#217848]">みのりくん</span>
            </div>
            <h1 class="text-2xl leading-snug font-bold text-[#24372b]">メールをご確認ください</h1>
            <p class="mt-3 text-sm leading-7 text-[#687a70]">
              メールに届いた6桁の認証コードを入力すると、
              <br />
              お客様情報の入力へ進めます。
            </p>
          </div>
          <div class="lg:hidden" aria-label="会員登録の進捗">
            <div class="mb-3 flex items-center justify-between text-sm">
              <span class="font-semibold text-[#237f4b]">ステップ 2 / 3</span>
              <span class="text-[#687a70]">メール確認</span>
            </div>
            <div
              class="flex gap-2"
              role="progressbar"
              aria-label="会員登録の進捗"
              aria-valuemin="1"
              aria-valuemax="3"
              aria-valuenow="2"
              aria-valuetext="ステップ 2 / 3：メール確認"
            >
              <span class="h-1 flex-1 rounded-full bg-[#237f4b]"></span>
              <span class="h-1 flex-1 rounded-full bg-[#237f4b]"></span>
              <span class="h-1 flex-1 rounded-full bg-[#dce8df]"></span>
            </div>
          </div>
          <ol
            class="hidden list-none items-center justify-between gap-1 text-xs text-[#8a978e] lg:mb-8 lg:flex lg:gap-2"
            aria-label="会員登録の進捗"
          >
            <li class="font-bold text-[#237f4b]">
              <span class="flex items-center gap-1 lg:hidden">
                <Check class="size-3" aria-hidden="true" />
                <span class="sr-only">完了：</span>
                メール入力
              </span>
              <span class="hidden items-center gap-2 lg:flex">
                <span
                  class="grid size-7 place-items-center rounded-full bg-[#237f4b] text-xs text-white"
                >
                  <Check class="size-4" aria-hidden="true" />
                  <span class="sr-only">完了</span>
                </span>
                メール入力
              </span>
            </li>
            <li aria-hidden="true"><ChevronRight class="size-3" /></li>
            <li class="font-bold text-[#237f4b]" aria-current="step">
              <span class="lg:hidden">② メール確認</span>
              <span class="hidden items-center gap-2 lg:flex">
                <span
                  class="grid size-7 place-items-center rounded-full bg-[#237f4b] text-xs text-white"
                >
                  2
                </span>
                メール確認
              </span>
            </li>
            <li aria-hidden="true"><ChevronRight class="size-3" /></li>
            <li>
              <span class="lg:hidden">③ 情報入力</span>
              <span class="hidden items-center gap-2 lg:flex">
                <span
                  class="grid size-7 place-items-center rounded-full border border-[#d7e3da] text-xs"
                >
                  3
                </span>
                情報入力
              </span>
            </li>
          </ol>
          <div class="mb-8 hidden lg:block">
            <p class="text-sm font-medium text-[#237f4b]">STEP 2 / 3</p>
            <h1 class="mt-2 text-2xl font-bold text-[#1f2a24]">メール確認</h1>
            <p class="mt-3 text-sm leading-6 text-[#687a70]">
              メールに記載された6桁の認証コードを入力してください。
              <br />
              確認が完了すると、お客様情報の入力へ進みます。
            </p>
          </div>
          <UiCard class="mt-8 rounded-none border-0 bg-transparent p-0 shadow-none lg:mt-0">
            <div class="rounded-xl bg-[#edf4ef] p-4 text-left lg:bg-[#f3f6f3]">
              <p class="text-sm leading-6 text-[#69776e]">
                <span class="break-all font-semibold text-[#24372b]">{{ email }}</span>
                <br />
                に確認コードを送信しました。
              </p>
            </div>
            <form
              class="mt-4 grid gap-4 lg:mt-6 lg:gap-5"
              novalidate
              :aria-busy="isSubmitting"
              @submit.prevent="submit"
            >
              <div class="grid gap-1.5 lg:gap-2">
                <UiFormLabel
                  class="text-sm leading-normal font-bold text-[#24372b]"
                  for="registration-otp"
                >
                  確認コード (6桁)
                  <span class="rounded bg-[#d84444] px-1 py-px text-xs text-white">必須</span>
                </UiFormLabel>
                <UiInput
                  id="registration-otp"
                  v-model="code"
                  v-bind="codeAttrs"
                  class="h-14 rounded-xl border-[#dce5dc] px-4 text-center text-xl tracking-widest md:text-xl lg:rounded-md lg:text-xl placeholder:tracking-normal lg:placeholder:text-base"
                  type="text"
                  name="code"
                  inputmode="numeric"
                  autocomplete="one-time-code"
                  maxlength="6"
                  placeholder="6桁の数字"
                  required
                  :disabled="isSubmitting || isResending"
                  :aria-invalid="Boolean(errors.code)"
                  :aria-describedby="
                    errors.code
                      ? 'registration-otp-guidance registration-otp-error'
                      : 'registration-otp-guidance'
                  "
                />
                <UiFormMessage v-if="errors.code" id="registration-otp-error" role="alert">
                  {{ errors.code }}
                </UiFormMessage>
                <p id="registration-otp-guidance" class="text-xs leading-6 text-[#687a70]">
                  コードの有効期限は発行から3分です。
                </p>
              </div>
              <UiButton
                class="min-h-13 rounded-xl text-base lg:min-h-12 lg:rounded-md"
                type="submit"
                :disabled="isSubmitting || isResending"
              >
                <LoaderCircle v-if="isSubmitting" class="size-4 animate-spin" aria-hidden="true" />
                {{ isSubmitting ? '確認中...' : '確認して次へ進む' }}
              </UiButton>
            </form>
          </UiCard>
          <div
            class="mt-5 grid justify-items-center gap-3 text-xs lg:mt-6 lg:gap-4 lg:border-t lg:border-[#e1e8e1] lg:pt-6 lg:text-sm"
          >
            <p class="text-center text-sm leading-6 text-[#687a70] lg:text-xs">
              メールが届かない場合は、迷惑メールフォルダをご確認ください。
            </p>
            <UiButton
              variant="ghost"
              class="min-h-11 h-auto whitespace-normal p-0 text-sm font-bold text-[#237f4b] underline underline-offset-2 disabled:text-[#87958c] lg:min-h-11 lg:text-sm"
              :disabled="!canResend || isSubmitting || isResending"
              :aria-busy="isResending"
              @click="resend"
            >
              <LoaderCircle v-if="isResending" class="size-4 animate-spin" aria-hidden="true" />
              {{ isResending ? '送信中...' : resendLabel }}
            </UiButton>
            <UiButton
              variant="ghost"
              class="min-h-11 h-auto p-0 text-sm font-medium text-[#6e7e74] underline underline-offset-2 lg:text-sm"
              :disabled="isSubmitting || isResending"
              @click="changeEmail"
            >
              メールアドレスを変更する
            </UiButton>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>
