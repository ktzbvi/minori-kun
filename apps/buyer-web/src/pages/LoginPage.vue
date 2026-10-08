<script setup lang="ts">
import { ChevronLeft, Leaf, LoaderCircle, ArrowRight } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { UiButton, UiCard, UiInput, UiFormLabel, UiFormMessage } from '@minorikun/ui'
import { useBuyerLogin } from '@/composables/useBuyerLogin'

const {
  errorMessage,
  errors,
  isSubmitting,
  email,
  emailAttrs,
  password,
  passwordAttrs,
  submit,
  goBack,
} = useBuyerLogin()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6 lg:bg-[#f3f6f3] lg:p-10">
    <section
      class="min-h-screen w-full max-w-[375px] bg-[#fbfcfa] sm:min-h-[728px] sm:shadow-sm lg:grid lg:min-h-[680px] lg:max-w-6xl lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:overflow-hidden lg:rounded-3xl lg:border lg:border-[#d7e3da] lg:bg-white lg:shadow-xl lg:shadow-[#14382a]/5">
      <aside class="relative hidden overflow-hidden bg-[#173e2c] p-12 text-white lg:flex lg:flex-col"
        aria-label="みのりくん">
        <RouterLink to="/"
          class="relative z-10 flex w-fit items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
          <span class="grid size-11 place-items-center rounded-full bg-white/10">
            <Leaf class="size-6 text-[#c5e2a3]" aria-hidden="true" />
          </span>
          <span class="text-2xl font-extrabold tracking-widest">みのりくん</span>
        </RouterLink>
        <div class="relative z-10 mt-20 max-w-sm">
          <p class="mb-5 text-sm font-medium tracking-widest text-[#b9d7c0]">
            畑から、あなたの食卓へ。
          </p>
          <h2 class="text-3xl leading-relaxed font-bold xl:text-4xl">
            毎日の食卓に、
            <br />
            みのりを。
          </h2>
          <p class="mt-6 text-base leading-8 text-[#c0d5c6]">
            旬の農産物と、生産者の想い。
            <br />
            みのりくんで、お気に入りを見つけましょう。
          </p>
        </div>
        <svg class="pointer-events-none absolute inset-x-0 bottom-0 h-64 w-full" viewBox="0 0 560 260"
          preserveAspectRatio="xMidYMax slice" aria-hidden="true">
          <circle cx="438" cy="71" r="35" fill="#d9dfa2" opacity=".9" />
          <path d="M0 138Q90 62 225 137T560 117V260H0Z" fill="#356449" />
          <path d="M0 180Q135 115 270 176T560 153V260H0Z" fill="#568664" />
          <path d="M0 220Q120 164 275 215T560 194V260H0Z" fill="#81a879" />
          <g fill="none" stroke="#c8ddb2" stroke-width="2" opacity=".4">
            <path d="M0 260Q105 196 230 183M75 260Q182 215 310 207M180 260Q283 225 408 225M341 260Q425 220 560 213" />
            <path d="M90 185v-45l30-28 30 28v45m-50 0v-40h40v40m-59 0h78" />
          </g>
        </svg>
        <RouterLink to="/"
          class="relative z-10 mt-auto flex w-fit items-center gap-2 pt-36 text-sm font-medium text-[#dce8d5] underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
          商品を見に行く
          <ArrowRight class="size-4" aria-hidden="true" />
        </RouterLink>
      </aside>

      <div class="min-w-0 lg:flex lg:flex-col">
        <header
          class="flex h-[65px] items-center gap-3 border-b border-[#e1e8e1] px-5 lg:h-auto lg:border-0 lg:px-10 lg:pt-8">
          <UiButton variant="ghost"
            class="size-9 min-h-9 rounded-full p-0 text-[#267c4a] lg:w-auto lg:gap-1 lg:px-2 lg:text-sm" aria-label="戻る"
            @click="goBack">
            <ChevronLeft class="size-5" :stroke-width="2.5" aria-hidden="true" />
            <span class="hidden lg:inline">戻る</span>
          </UiButton>
          <h1 class="text-base font-bold text-[#227644] lg:hidden">ログイン</h1>
        </header>

        <div class="px-[33px] pt-[75px] pb-12 lg:mx-auto lg:w-full lg:max-w-md lg:px-8 lg:pt-10 lg:pb-10">
          <div class="mb-8 hidden lg:block">
            <p class="text-sm font-medium text-[#237f4b]">おかえりなさい</p>
            <h1 class="mt-2 text-2xl font-bold text-[#1f2a24]">ログイン</h1>
            <p class="mt-3 text-sm leading-6 text-[#687a70]">
              メールアドレスとパスワードを入力してください。
            </p>
          </div>
          <UiCard class="rounded-[11px] border-[#dbe5dc] bg-white p-4.5 shadow-none lg:rounded-none lg:border-0 lg:p-0">
            <div class="mb-6 flex items-center justify-center gap-2.5 lg:hidden">
              <span class="grid size-9 place-items-center rounded-full bg-[#e4f3e9] text-[#237d4a]" aria-hidden="true">
                <Leaf class="size-5" :stroke-width="2.5" />
              </span>
              <p class="text-xl font-extrabold tracking-[0.06em] text-[#217848]">みのりくん</p>
            </div>
            <form class="grid gap-4 lg:gap-5" novalidate :aria-busy="isSubmitting" @submit.prevent="submit">
              <div class="grid gap-1.5 lg:gap-2">
                <UiFormLabel class="text-sm leading-normal font-bold text-[#24372b]" for="buyer-email">
                  メールアドレス
                  <span class="rounded bg-[#d84444] px-1 py-px text-xs text-white">必須</span>
                </UiFormLabel>
                <UiInput id="buyer-email" v-model="email" v-bind="emailAttrs"
                  class="h-10 rounded-md border-[#dce5dc] px-2.5 text-sm lg:h-12 lg:px-4 lg:text-base" type="email"
                  name="email" autocomplete="username" inputmode="email" placeholder="example@farm-ec.jp" required
                  :disabled="isSubmitting" :aria-invalid="Boolean(errors.email)"
                  :aria-describedby="errors.email ? 'buyer-email-error' : undefined" />
                <UiFormMessage v-if="errors.email" id="buyer-email-error" role="alert">
                  {{ errors.email }}
                </UiFormMessage>
              </div>
              <div class="grid gap-1.5 lg:gap-2">
                <UiFormLabel class="text-sm leading-normal font-bold text-[#24372b]" for="buyer-password">
                  パスワード
                  <span class="rounded bg-[#d84444] px-1 py-px text-xs text-white">必須</span>
                </UiFormLabel>
                <UiInput id="buyer-password" v-model="password" v-bind="passwordAttrs"
                  class="h-10 rounded-md border-[#dce5dc] px-2.5 text-sm lg:h-12 lg:px-4 lg:text-base" type="password"
                  name="password" autocomplete="current-password" placeholder="パスワードを入力" required
                  :disabled="isSubmitting" :aria-invalid="Boolean(errors.password)"
                  :aria-describedby="errors.password ? 'buyer-password-error' : undefined" />
                <UiFormMessage v-if="errors.password" id="buyer-password-error" role="alert">
                  {{ errors.password }}
                </UiFormMessage>
              </div>
              <UiFormMessage v-if="errorMessage" class="rounded-lg bg-red-50 px-3 py-3 leading-6" role="alert">
                {{ errorMessage }}
              </UiFormMessage>
              <UiButton class="mt-0.5 min-h-10 rounded-md text-sm lg:min-h-12 lg:text-base" type="submit"
                :disabled="isSubmitting">
                <LoaderCircle v-if="isSubmitting" class="size-4 animate-spin" aria-hidden="true" />
                {{ isSubmitting ? 'ログイン中...' : 'ログイン' }}
              </UiButton>
            </form>
          </UiCard>
          <nav class="mt-7 grid justify-items-center gap-4 text-xs lg:mt-6 lg:gap-6 lg:text-sm" aria-label="ログイン支援">
            <div
              class="grid justify-items-center gap-1 lg:order-2 lg:w-full lg:gap-3 lg:border-t lg:border-[#e1e8e1] lg:pt-6">
              <span class="text-[#78867d]">アカウントをお持ちでない方</span>
              <RouterLink
                class="font-bold text-[#237f4b] underline underline-offset-2 focus-visible:outline-2 focus-visible:outline-offset-4 lg:flex lg:min-h-12 lg:w-full lg:items-center lg:justify-center lg:rounded-lg lg:border lg:border-[#c6d9cb] lg:no-underline lg:hover:bg-[#f3f6f3]"
                to="/register">
                新規会員登録
              </RouterLink>
            </div>
            <RouterLink
              class="font-medium text-[#6e7e74] underline underline-offset-2 focus-visible:outline-2 focus-visible:outline-offset-4 lg:order-1"
              to="/password-reset">
              パスワードをお忘れですか？
            </RouterLink>
          </nav>
        </div>
      </div>
    </section>
  </main>
</template>
