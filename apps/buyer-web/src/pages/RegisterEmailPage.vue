<script setup lang="ts">
import { ChevronLeft, ChevronRight, Leaf, LoaderCircle, ArrowRight, Mail } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { UiButton, UiCard, UiInput, UiFormLabel, UiFormMessage } from '@minorikun/ui'
import { useBuyerRegisterEmail } from '@/composables/useBuyerRegisterEmail'

const { errors, isSubmitting, email, emailAttrs, submit, goBack } = useBuyerRegisterEmail()
</script>

<template>
  <main
    class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6 lg:bg-[#f3f6f3] lg:p-10"
  >
    <section
      class="min-h-screen w-full max-w-[375px] bg-[#fbfcfa] sm:min-h-[728px] sm:shadow-sm lg:grid lg:min-h-[680px] lg:max-w-6xl lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:overflow-hidden lg:rounded-3xl lg:border lg:border-[#d7e3da] lg:bg-white lg:shadow-xl lg:shadow-[#14382a]/5"
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
            旬との出会いを、
            <br />
            ここから。
          </h2>
          <p class="mt-6 text-base leading-8 text-[#c0d5c6]">
            まずはメールアドレスを確認。
            <br />
            そのあと、お客様情報を入力して登録できます。
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
          <h1 class="text-base font-bold text-[#227644] lg:hidden">新規会員登録</h1>
        </header>
        <div
          class="px-[33px] pt-3 pb-12 lg:mx-auto lg:w-full lg:max-w-md lg:px-8 lg:pt-10 lg:pb-10"
        >
          <ol
            class="flex list-none items-center justify-between gap-1 text-xs text-[#8a978e] lg:mb-8 lg:gap-2"
            aria-label="会員登録の進捗"
          >
            <li class="font-bold text-[#237f4b]" aria-current="step">
              <span class="lg:hidden">① メール入力</span>
              <span class="hidden items-center gap-2 lg:flex">
                <span
                  class="grid size-7 place-items-center rounded-full bg-[#237f4b] text-xs text-white"
                >
                  1
                </span>
                メール入力
              </span>
            </li>
            <li aria-hidden="true"><ChevronRight class="size-3" /></li>
            <li>
              <span class="lg:hidden">② メール確認</span>
              <span class="hidden items-center gap-2 lg:flex">
                <span
                  class="grid size-7 place-items-center rounded-full border border-[#d7e3da] text-xs"
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
            <p class="text-sm font-medium text-[#237f4b]">STEP 1 / 3</p>
            <h1 class="mt-2 text-2xl font-bold text-[#1f2a24]">新規会員登録</h1>
            <p class="mt-3 text-sm leading-6 text-[#687a70]">
              登録に使用するメールアドレスを入力してください。
              <br />
              メールで6桁の認証コードをお送りします。
            </p>
          </div>
          <UiCard
            class="mt-3 rounded-[11px] border-[#dbe5dc] bg-white p-4.5 shadow-none lg:mt-0 lg:rounded-none lg:border-0 lg:p-0"
          >
            <div class="mb-6 flex items-center justify-center gap-2.5 lg:hidden">
              <span
                class="grid size-9 place-items-center rounded-full bg-[#e4f3e9] text-[#237d4a]"
                aria-hidden="true"
              >
                <Leaf class="size-5" :stroke-width="2.5" />
              </span>
              <p class="text-xl font-extrabold tracking-[0.06em] text-[#217848]">みのりくん</p>
            </div>
            <form
              class="grid gap-4 lg:gap-5"
              novalidate
              :aria-busy="isSubmitting"
              @submit.prevent="submit"
            >
              <div class="grid gap-1.5 lg:gap-2">
                <UiFormLabel
                  class="text-sm leading-normal font-bold text-[#24372b]"
                  for="registration-email"
                >
                  メールアドレス
                  <span class="rounded bg-[#d84444] px-1 py-px text-xs text-white">必須</span>
                </UiFormLabel>
                <UiInput
                  id="registration-email"
                  v-model="email"
                  v-bind="emailAttrs"
                  class="h-10 rounded-md border-[#dce5dc] px-2.5 text-sm lg:h-12 lg:px-4 lg:text-base"
                  type="email"
                  name="email"
                  autocomplete="email"
                  inputmode="email"
                  placeholder="メールアドレスを入力してください。"
                  required
                  :disabled="isSubmitting"
                  :aria-invalid="Boolean(errors.email)"
                  :aria-describedby="errors.email ? 'registration-email-error' : undefined"
                />
                <UiFormMessage v-if="errors.email" id="registration-email-error" role="alert">
                  {{ errors.email }}
                </UiFormMessage>
              </div>
              <UiButton
                class="min-h-10 rounded-md text-sm lg:min-h-12 lg:text-base"
                type="submit"
                :disabled="isSubmitting"
              >
                <LoaderCircle v-if="isSubmitting" class="size-4 animate-spin" aria-hidden="true" />
                {{ isSubmitting ? '送信中...' : '認証コードを送信' }}
              </UiButton>
            </form>
            <div class="mt-5 hidden items-start gap-3 rounded-xl bg-[#f3f6f3] p-4 lg:flex">
              <Mail class="mt-0.5 size-5 shrink-0 text-[#237f4b]" aria-hidden="true" />
              <p class="text-xs leading-6 text-[#687a70]">
                メール確認後、お客様情報の入力へ進みます。
                <br />
                この時点では会員登録は完了しません。
              </p>
            </div>
          </UiCard>
          <p
            class="mt-5 text-center text-xs text-[#78867d] lg:mt-8 lg:border-t lg:border-[#e1e8e1] lg:pt-6 lg:text-sm"
          >
            すでにアカウントをお持ちの方
            <RouterLink
              class="ml-1 font-bold text-[#237f4b] underline underline-offset-2 focus-visible:outline-2 focus-visible:outline-offset-4"
              to="/login"
            >
              ログイン
            </RouterLink>
          </p>
        </div>
      </div>
    </section>
  </main>
</template>
