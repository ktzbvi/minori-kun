<script setup lang="ts">
import { ChevronLeft } from 'lucide-vue-next'
import { UiButton, UiCard } from '@minorikun/ui'
import { useBuyerRegisterOtp } from '@/composables/useBuyerRegisterOtp'
const {
  router,
  route,
  email,
  resendAvailableAt,
  now,
  timer,
  otpSchema,
  defineField,
  errors,
  handleSubmit,
  isSubmitting,
  setFieldValue,
  code,
  codeAttrs,
  secondsUntilResend,
  canResend,
  resendLabel,
  setStatus,
  loadStatus,
  submit,
  resend,
  changeEmail,
  goBack,
  registrationRedirectQuery,
} = useBuyerRegisterOtp()
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
          メール確認
        </h1>
      </header>

      <div class="px-[33px] pt-3 pb-12">
        <ol
          class="m-0 flex list-none items-center justify-between px-0 text-[10px] text-[#8a978e]"
          aria-label="Registration progress"
        >
          <li class="font-bold text-[#237f4b]">
            ✓ メール入力
          </li>
          <li aria-hidden="true">›</li>
          <li class="font-bold text-[#237f4b]">
            ② メール確認
          </li>
          <li aria-hidden="true">›</li>
          <li>③ 情報入力</li>
        </ol>

        <UiCard class="mt-3 !rounded-[11px] !border-[#dbe5dc] !bg-white !p-4.5 !shadow-none">
          <p class="m-0 text-center text-[12px] leading-5 text-[#69776e]">
            <span class="break-all">{{ email }}</span>
            に確認コードを送信しました。
          </p>

          <form class="mt-4 grid gap-4" novalidate @submit.prevent="submit">
            <div class="grid gap-1.5">
              <label class="text-[13px] font-bold text-[#24372b]" for="registration-otp">
                確認コード (6桁)
                <span class="rounded bg-[#d84444] px-1 py-px text-[10px] text-white">
                  必須
                </span>
              </label>
              <input
                id="registration-otp"
                v-model="code"
                v-bind="codeAttrs"
                class="min-h-[39px] rounded-[6px] border border-[#dce5dc] bg-white px-2.5 text-left text-[16px] tracking-normal outline-none placeholder:text-left placeholder:tracking-normal placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                placeholder="6桁の数字"
                :aria-invalid="Boolean(errors.code)"
                :aria-describedby="errors.code ? 'registration-otp-error' : undefined"
              />
              <p
                v-if="errors.code"
                id="registration-otp-error"
                class="m-0 text-xs font-medium text-[#b33a2b]"
                role="alert"
              >
                {{ errors.code }}
              </p>
            </div>

            <UiButton
              class="!min-h-[37px] !rounded-[5px] !px-4 !text-[14px]"
              type="submit"
              :disabled="isSubmitting"
            >
              <span v-if="isSubmitting">確認中...</span>
              <span v-else>確認して次へ進む</span>
            </UiButton>
          </form>
        </UiCard>

        <div class="mt-5 grid justify-items-center gap-3 text-[12px]">
          <button
            class="border-0 bg-transparent p-0 font-bold text-[#237f4b] underline underline-offset-2 disabled:text-[#87958c]"
            type="button"
            :disabled="!canResend"
            @click="resend"
          >
            {{ resendLabel }}
          </button>
          <button
            class="border-0 bg-transparent p-0 font-medium text-[#6e7e74] underline underline-offset-2"
            type="button"
            @click="changeEmail"
          >
            メールアドレスを変更する
          </button>
        </div>
      </div>
    </section>
  </main>
</template>
