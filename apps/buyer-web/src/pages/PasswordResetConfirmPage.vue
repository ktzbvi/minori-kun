<script setup lang="ts">
import { ChevronLeft } from 'lucide-vue-next'
import { UiButton, UiCard } from '@minorikun/ui'
import { useBuyerPasswordResetConfirm } from '@/composables/useBuyerPasswordResetConfirm'
const {
  route,
  router,
  submissionError,
  email,
  token,
  linkIsComplete,
  passwordPolicy,
  schema,
  defineField,
  errors,
  handleSubmit,
  isSubmitting,
  password,
  passwordAttrs,
  passwordConfirmation,
  passwordConfirmationAttrs,
  submit,
} = useBuyerPasswordResetConfirm()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="min-h-screen w-full max-w-[375px] bg-[#fbfcfa] sm:min-h-[728px] sm:shadow-sm">
      <header class="flex h-[65px] items-center gap-3 border-b border-[#e1e8e1] px-5">
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#267c4a]"
          type="button"
          aria-label="Back"
          @click="router.back()"
        >
          <ChevronLeft :size="22" :stroke-width="2.5" aria-hidden="true" />
        </button>
        <h1 class="text-[16px] font-bold text-[#227644]">
          パスワードの変更
        </h1>
      </header>

      <p class="px-5 pt-3 text-[10px] text-[#78867d]">
        ログインパスワードを変更します。
      </p>

      <div class="px-5 pt-2 pb-12">
        <UiCard class="!rounded-[9px] !border-[#dbe5dc] !bg-white !p-3 !shadow-none">
          <form class="grid gap-3" novalidate @submit.prevent="submit">
            <div class="grid gap-1.5">
              <label class="text-[11px] font-bold text-[#24372b]" for="new-password">
                新しいパスワード
                <span class="rounded bg-[#d84444] px-1 py-px text-[9px] text-white">
                  必須
                </span>
              </label>
              <input
                id="new-password"
                v-model="password"
                v-bind="passwordAttrs"
                class="min-h-[34px] rounded-[5px] border border-[#dce5dc] bg-white px-2.5 text-[12px] outline-none placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                type="password"
                autocomplete="new-password"
                placeholder="8文字以上で入力してください。"
                :aria-describedby="errors.password ? 'new-password-error' : undefined"
                :aria-invalid="Boolean(errors.password)"
              />
              <p
                v-if="errors.password"
                id="new-password-error"
                class="m-0 text-[11px] font-medium text-[#b33a2b]"
                role="alert"
              >
                {{ errors.password }}
              </p>
            </div>

            <div class="grid gap-1.5">
              <label class="text-[11px] font-bold text-[#24372b]" for="new-password-confirmation">
                新しいパスワード
                （確認）
                <span class="rounded bg-[#d84444] px-1 py-px text-[9px] text-white">
                  必須
                </span>
              </label>
              <input
                id="new-password-confirmation"
                v-model="passwordConfirmation"
                v-bind="passwordConfirmationAttrs"
                class="min-h-[34px] rounded-[5px] border border-[#dce5dc] bg-white px-2.5 text-[12px] outline-none placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                type="password"
                autocomplete="new-password"
                placeholder="もう一度入力してください。"
                :aria-describedby="
                  errors.password_confirmation ? 'new-password-confirmation-error' : undefined
                "
                :aria-invalid="Boolean(errors.password_confirmation)"
              />
              <p
                v-if="errors.password_confirmation"
                id="new-password-confirmation-error"
                class="m-0 text-[11px] font-medium text-[#b33a2b]"
                role="alert"
              >
                {{ errors.password_confirmation }}
              </p>
            </div>

            <p
              v-if="submissionError"
              class="m-0 text-[11px] font-medium text-[#b33a2b]"
              role="alert"
            >
              {{ submissionError }}
            </p>

            <UiButton
              class="!w-full !min-h-[35px] !rounded-[4px] !text-[12px]"
              type="submit"
              :disabled="isSubmitting"
            >
              <span v-if="isSubmitting">保存中...</span>
              <span v-else>変更内容を保存</span>
            </UiButton>
          </form>
        </UiCard>
      </div>
    </section>
  </main>
</template>
