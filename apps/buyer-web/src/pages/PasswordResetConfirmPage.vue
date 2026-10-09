<script setup lang="ts">
import { ChevronLeft, Leaf, LoaderCircle, ArrowRight } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { UiButton, UiCard, UiInput, UiFormLabel, UiFormMessage } from '@minorikun/ui'
import BuyerBrand from '@/components/BuyerBrand.vue'
import { useBuyerPasswordResetConfirm } from '@/composables/useBuyerPasswordResetConfirm'
const {
  router,
  submissionError,
  linkIsComplete,
  passwordPolicy,
  errors,
  isSubmitting,
  password,
  passwordAttrs,
  passwordConfirmation,
  passwordConfirmationAttrs,
  submit,
} = useBuyerPasswordResetConfirm()
</script>
<template>
  <main
    class="min-h-dvh bg-[#fbfcfa] text-[#26362c] lg:grid lg:place-items-center lg:bg-[#f3f6f3] lg:p-10"
  >
    <section
      class="min-h-dvh w-full bg-[#fbfcfa] lg:grid lg:min-h-[680px] lg:max-w-6xl lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:overflow-hidden lg:rounded-3xl lg:border lg:border-[#d7e3da] lg:bg-white lg:shadow-xl lg:shadow-[#14382a]/5"
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
            新しいパスワードを設定して、
            <br />
            また、みのりくんをお楽しみください。
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
            @click="router.back()"
          >
            <ChevronLeft class="size-5" :stroke-width="2.5" aria-hidden="true" />
            <span class="hidden lg:inline">戻る</span>
          </UiButton>
          <span class="text-sm font-semibold text-[#227644] lg:hidden">パスワードの再設定</span>
        </header>
        <div class="px-6 pt-8 pb-12 lg:mx-auto lg:w-full lg:max-w-md lg:px-8 lg:pt-10 lg:pb-10">
          <div class="mb-7 lg:hidden">
            <BuyerBrand size="large" />
          </div>
          <template v-if="!linkIsComplete">
            <h1 class="text-2xl font-bold text-[#1f2a24]">リンクをご確認ください</h1>
            <p class="mt-4 text-sm leading-7 text-[#687a70]" role="alert">
              再設定用リンクが無効です。メール内のリンクを開くか、新しいリンクをリクエストしてください。
            </p>
            <UiButton
              as-child
              class="mt-6 min-h-13 w-full rounded-xl text-base lg:min-h-12 lg:rounded-md"
            >
              <RouterLink to="/password-reset">再設定リンクをリクエスト</RouterLink>
            </UiButton>
          </template>
          <template v-else>
            <div class="mb-8">
              <h1 class="text-2xl leading-snug font-bold text-[#1f2a24]">新しいパスワードを設定</h1>
              <p class="mt-3 text-sm leading-7 text-[#687a70]">
                新しいパスワードを入力してください。
                <br />
                再設定後、ログイン画面へ移動します。
              </p>
            </div>
            <UiCard class="rounded-none border-0 bg-transparent p-0 shadow-none">
              <form
                class="grid gap-5"
                novalidate
                :aria-busy="isSubmitting"
                @submit.prevent="submit"
              >
                <div class="grid gap-2">
                  <UiFormLabel
                    for="new-password"
                    class="text-sm leading-normal font-bold text-[#24372b]"
                  >
                    新しいパスワード
                    <span class="rounded bg-[#d84444] px-1 py-px text-xs text-white">必須</span>
                  </UiFormLabel>
                  <UiInput
                    id="new-password"
                    v-model="password"
                    v-bind="passwordAttrs"
                    class="h-12 rounded-xl border-[#dce5dc] px-4 text-base md:text-base lg:rounded-md"
                    type="password"
                    name="password"
                    autocomplete="new-password"
                    placeholder="新しいパスワードを入力"
                    required
                    :disabled="isSubmitting"
                    :aria-invalid="Boolean(errors.password)"
                    :aria-describedby="
                      errors.password ? 'password-policy new-password-error' : 'password-policy'
                    "
                  />
                  <UiFormMessage v-if="errors.password" id="new-password-error" role="alert">
                    {{ errors.password }}
                  </UiFormMessage>
                </div>
                <div class="grid gap-2">
                  <UiFormLabel
                    for="new-password-confirmation"
                    class="text-sm leading-normal font-bold text-[#24372b]"
                  >
                    新しいパスワード（確認）
                    <span class="rounded bg-[#d84444] px-1 py-px text-xs text-white">必須</span>
                  </UiFormLabel>
                  <UiInput
                    id="new-password-confirmation"
                    v-model="passwordConfirmation"
                    v-bind="passwordConfirmationAttrs"
                    class="h-12 rounded-xl border-[#dce5dc] px-4 text-base md:text-base lg:rounded-md"
                    type="password"
                    name="password_confirmation"
                    autocomplete="new-password"
                    placeholder="もう一度入力してください"
                    required
                    :disabled="isSubmitting"
                    :aria-invalid="Boolean(errors.password_confirmation)"
                    :aria-describedby="
                      errors.password_confirmation ? 'new-password-confirmation-error' : undefined
                    "
                  />
                  <UiFormMessage
                    v-if="errors.password_confirmation"
                    id="new-password-confirmation-error"
                    role="alert"
                  >
                    {{ errors.password_confirmation }}
                  </UiFormMessage>
                </div>
                <p
                  id="password-policy"
                  class="rounded-xl bg-[#f3f6f3] p-4 text-xs leading-6 text-[#687a70]"
                >
                  {{ passwordPolicy }}
                </p>
                <UiFormMessage
                  v-if="submissionError"
                  class="rounded-lg bg-red-50 p-3 text-sm leading-6"
                  role="alert"
                >
                  {{ submissionError }}
                </UiFormMessage>
                <UiButton
                  type="submit"
                  class="min-h-13 w-full rounded-xl text-base lg:min-h-12 lg:rounded-md"
                  :disabled="isSubmitting"
                >
                  <LoaderCircle
                    v-if="isSubmitting"
                    class="size-4 animate-spin"
                    aria-hidden="true"
                  />
                  {{ isSubmitting ? '保存中...' : 'パスワードを再設定' }}
                </UiButton>
              </form>
            </UiCard>
            <RouterLink
              to="/password-reset"
              class="mt-6 flex min-h-11 items-center justify-center text-sm font-medium text-[#687a70] underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4"
            >
              リンクが使えない場合は、再設定メールを再リクエスト
            </RouterLink>
          </template>
          <RouterLink
            to="/login"
            class="mt-4 flex min-h-11 items-center justify-center gap-1 text-sm font-semibold text-[#237f4b] focus-visible:outline-2 focus-visible:outline-offset-4"
          >
            <ChevronLeft class="size-4" aria-hidden="true" />
            ログインに戻る
          </RouterLink>
        </div>
      </div>
    </section>
  </main>
</template>
