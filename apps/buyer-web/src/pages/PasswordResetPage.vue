<script setup lang="ts">
import { ChevronLeft, Leaf, LoaderCircle, ArrowRight, Mail } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { UiButton, UiCard, UiInput, UiFormLabel, UiFormMessage } from '@minorikun/ui'
import { useBuyerPasswordReset } from '@/composables/useBuyerPasswordReset'
const {
  router,
  requestSent,
  requestAnotherLink,
  serviceError,
  errors,
  isSubmitting,
  email,
  emailAttrs,
  submit,
} = useBuyerPasswordReset()
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
            安心して、またはじめよう。
          </p>
          <h2 class="text-3xl leading-relaxed font-bold xl:text-4xl">
            パスワードを、
            <br />
            もう一度。
          </h2>
          <p class="mt-6 text-base leading-8 text-[#c0d5c6]">
            登録したメールアドレスに、
            <br />
            パスワード再設定用リンクをお送りします。
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
            type="button"
            aria-label="戻る"
            @click="router.back()"
          >
            <ChevronLeft class="size-5" :stroke-width="2.5" aria-hidden="true" />
            <span class="hidden lg:inline">戻る</span>
          </UiButton>
          <span class="text-sm font-semibold text-[#227644] lg:hidden">パスワードの再設定</span>
        </header>

        <div class="px-6 pt-8 pb-12 lg:mx-auto lg:w-full lg:max-w-md lg:px-8 lg:pt-10 lg:pb-10">
          <div class="mb-7 flex items-center gap-2 lg:hidden">
            <span class="grid size-10 place-items-center rounded-full bg-[#e4f3e9]">
              <Leaf class="size-6 text-[#237f4b]" aria-hidden="true" />
            </span>
            <span class="text-xl font-extrabold tracking-widest text-[#217848]">みのりくん</span>
          </div>
          <section v-if="requestSent" aria-labelledby="reset-sent-title">
            <div role="status" aria-live="polite">
              <h1 id="reset-sent-title" class="text-xl font-bold text-[#1f2a24] lg:text-2xl">
                メールをご確認ください
              </h1>
              <p class="mt-4 text-sm leading-7 text-[#687a70]">
                該当するアカウントがある場合、パスワード再設定用メールを送信しました。メール内のリンクから新しいパスワードを設定してください。
              </p>
            </div>
            <p class="mt-5 rounded-xl bg-[#f3f6f3] p-4 text-sm leading-7 text-[#687a70]">
              届かない場合は、迷惑メールフォルダと入力したメールアドレスをご確認ください。
            </p>
            <UiButton
              variant="outline"
              class="mt-6 min-h-13 w-full whitespace-normal rounded-xl px-4 py-3 text-sm lg:min-h-12 lg:rounded-lg lg:text-base"
              @click="requestAnotherLink"
            >
              再設定リンクをもう一度リクエスト
            </UiButton>
          </section>
          <template v-else>
            <div class="mb-8">
              <div
                class="mb-5 hidden size-12 lg:grid place-items-center rounded-full bg-[#e8f2eb] text-[#237f4b]"
              >
                <Mail class="size-6" aria-hidden="true" />
              </div>
              <h1 class="text-2xl font-bold text-[#1f2a24]">パスワードの再設定</h1>
              <p class="mt-3 text-sm leading-6 text-[#687a70]">
                登録時のメールアドレスを入力してください。
                <br />
                パスワード再設定用リンクをお送りします。
              </p>
            </div>
            <UiCard class="rounded-none border-0 bg-transparent p-0 shadow-none">
              <form
                class="grid gap-4 lg:gap-5"
                :aria-busy="isSubmitting"
                novalidate
                @submit.prevent="submit"
              >
                <div class="grid gap-1.5">
                  <UiFormLabel
                    class="text-sm leading-normal font-bold text-[#24372b]"
                    for="password-reset-email"
                  >
                    メールアドレス
                    <span class="rounded bg-[#d84444] px-1 py-px text-xs text-white">必須</span>
                  </UiFormLabel>
                  <UiInput
                    id="password-reset-email"
                    v-model="email"
                    v-bind="emailAttrs"
                    class="h-12 rounded-xl border-[#dce5dc] px-4 text-base md:text-base lg:rounded-md"
                    type="email"
                    name="email"
                    :disabled="isSubmitting"
                    autocomplete="email"
                    inputmode="email"
                    placeholder="メールアドレスを入力してください。"
                    :aria-describedby="errors.email ? 'password-reset-email-error' : undefined"
                    :aria-invalid="Boolean(errors.email)"
                  />
                  <UiFormMessage v-if="errors.email" id="password-reset-email-error" role="alert">
                    {{ errors.email }}
                  </UiFormMessage>
                </div>

                <UiButton
                  class="w-full min-h-13 rounded-xl text-base lg:min-h-12 lg:rounded-md"
                  type="submit"
                  :disabled="isSubmitting"
                >
                  <LoaderCircle
                    v-if="isSubmitting"
                    class="size-4 animate-spin"
                    aria-hidden="true"
                  />
                  <span v-if="isSubmitting">送信中...</span>
                  <span v-else>再設定メールを送信</span>
                </UiButton>
              </form>
            </UiCard>

            <p class="mt-5 rounded-xl bg-[#f3f6f3] p-4 text-sm leading-7 text-[#78867d] lg:mt-4">
              該当するアカウントがある場合、入力したメールアドレスに再設定用リンクを送信します。
            </p>
            <p
              v-if="serviceError"
              class="mt-3 text-sm font-medium leading-6 text-[#b33a2b] lg:mt-2"
              role="alert"
            >
              {{ serviceError }}
            </p>
          </template>
          <RouterLink
            to="/login"
            class="mt-6 flex min-h-11 items-center justify-center gap-1 text-sm font-semibold text-[#237f4b] focus-visible:outline-2 focus-visible:outline-offset-4 lg:flex"
          >
            <ChevronLeft class="size-4" aria-hidden="true" />
            ログインに戻る
          </RouterLink>
        </div>
      </div>
    </section>
  </main>
</template>
