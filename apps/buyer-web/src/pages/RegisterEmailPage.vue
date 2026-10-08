<script setup lang="ts">
import { ChevronLeft, Leaf } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { UiButton, UiCard } from '@minorikun/ui'
import { useBuyerRegisterEmail } from '@/composables/useBuyerRegisterEmail'
const {
  router,
  route,
  registrationSchema,
  defineField,
  errors,
  handleSubmit,
  isSubmitting,
  email,
  emailAttrs,
  submit,
  goBack,
  registrationRedirectQuery,
} = useBuyerRegisterEmail()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] py-0 text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="min-h-screen w-full max-w-[375px] bg-[#fbfcfa] sm:min-h-[728px] sm:shadow-sm">
      <header class="flex h-[65px] items-center gap-3 border-b border-[#e1e8e1] px-5">
        <button
          class="grid size-9 place-items-center rounded-full border-0 bg-transparent text-[#267c4a]"
          type="button"
          aria-label="Back"
          @click="goBack"
        >
          <ChevronLeft :size="22" :stroke-width="2.5" aria-hidden="true" />
        </button>
        <h1 class="m-0 text-[16px] font-bold text-[#227644]">
          新規会員登録
        </h1>
      </header>

      <div class="px-[33px] pt-3 pb-12">
        <ol
          class="m-0 flex list-none items-center justify-between px-0 text-[10px] text-[#8a978e]"
          aria-label="Registration progress"
        >
          <li class="font-bold text-[#237f4b]">
            ① メール入力
          </li>
          <li aria-hidden="true">›</li>
          <li>② メール確認</li>
          <li aria-hidden="true">›</li>
          <li>③ 情報入力</li>
        </ol>

        <UiCard class="mt-3 !rounded-[11px] !border-[#dbe5dc] !bg-white !p-4.5 !shadow-none">
          <div class="mb-6 flex items-center justify-center gap-2.5">
            <span
              class="grid size-9 place-items-center rounded-full bg-[#e4f3e9] text-[#237d4a]"
              aria-hidden="true"
            >
              <Leaf :size="22" :stroke-width="2.5" />
            </span>
            <p class="m-0 text-[20px] font-extrabold tracking-[0.06em] text-[#217848]">
              みのりくん
            </p>
          </div>

          <form class="grid gap-4" novalidate @submit.prevent="submit">
            <div class="grid gap-1.5">
              <label class="text-[13px] font-bold text-[#24372b]" for="registration-email">
                メールアドレス
                <span class="rounded bg-[#d84444] px-1 py-px text-[10px] text-white">
                  必須
                </span>
              </label>
              <input
                id="registration-email"
                v-model="email"
                v-bind="emailAttrs"
                class="min-h-[39px] rounded-[6px] border border-[#dce5dc] bg-white px-2.5 text-[13px] outline-none placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                type="email"
                autocomplete="email"
                inputmode="email"
                placeholder="メールアドレスを入力してください。"
                :aria-invalid="Boolean(errors.email)"
                :aria-describedby="errors.email ? 'registration-email-error' : undefined"
              />
              <p
                v-if="errors.email"
                id="registration-email-error"
                class="m-0 text-xs font-medium text-[#b33a2b]"
                role="alert"
              >
                {{ errors.email }}
              </p>
            </div>

            <UiButton
              class="!min-h-[37px] !rounded-[5px] !px-4 !text-[14px]"
              type="submit"
              :disabled="isSubmitting"
            >
              <span v-if="isSubmitting">送信中...</span>
              <span v-else>認証コードを送信</span>
            </UiButton>
          </form>
        </UiCard>

        <p class="mt-5 text-center text-[12px] text-[#78867d]">
          すでにアカウントをお持ちの方
          <RouterLink
            class="ml-1 font-bold text-[#237f4b] underline underline-offset-2"
            to="/login"
          >
            ログイン
          </RouterLink>
        </p>
      </div>
    </section>
  </main>
</template>
