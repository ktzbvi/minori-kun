<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { ChevronRight } from 'lucide-vue-next'
import { z } from 'zod'
import { UiButton, UiInput, UiFormLabel, UiFormMessage } from '@minorikun/ui'

const email = ref('')
const emailError = ref('')
const unavailableMessage = ref('')
const emailSchema = z
  .string()
  .trim()
  .min(1, 'メールアドレスを入力してください。')
  .email('正しいメールアドレスを入力してください。')

// SCR-P-002 / P02-01: do not claim an OTP was sent without a server response.
function submit() {
  emailError.value = ''
  unavailableMessage.value = ''
  const result = emailSchema.safeParse(email.value)
  if (!result.success) {
    emailError.value = result.error.issues[0]?.message ?? ''
    return
  }
  unavailableMessage.value = '新規登録の受付は現在準備中です。'
}
</script>

<template>
  <main
    class="flex min-h-svh flex-col bg-[#f3f7f4] min-[761px]:grid min-[761px]:grid-cols-[35%_minmax(0,1fr)]"
  >
    <aside
      class="flex min-h-[145px] items-center bg-[#14382a] px-6 py-7 text-[#eaf0dd] min-[761px]:px-[clamp(32px,3.8vw,96px)] min-[761px]:py-12"
      aria-label="生産者ポータル"
    >
      <div class="w-full">
        <p
          class="mb-[5px] text-[32px] font-extrabold tracking-[.04em] italic min-[761px]:mb-[19px] min-[761px]:text-[clamp(36px,3vw,52px)]"
        >
          みのりくん
        </p>
        <p
          class="text-[17px] font-semibold text-[#a8c2ae] min-[761px]:text-[clamp(20px,1.55vw,28px)]"
        >
          生産者ポータル
        </p>
      </div>
    </aside>
    <section
      class="grid min-w-0 flex-1 items-start justify-items-center px-6 pt-11 pb-14 max-[360px]:px-4.5 min-[761px]:min-h-svh min-[761px]:place-items-center min-[761px]:px-[clamp(40px,7.6vw,160px)] min-[761px]:py-12"
      aria-labelledby="registration-title"
    >
      <div class="w-full max-w-[560px] min-[761px]:max-w-[772px]">
        <nav class="mb-6 min-[761px]:mb-[19px]" aria-label="登録の進行状況">
          <ol
            class="flex items-center justify-between gap-2 text-[13px] font-medium text-[#89988f] max-[360px]:gap-[5px] max-[360px]:text-[11px] min-[761px]:justify-start min-[761px]:gap-x-4.5 min-[761px]:text-[clamp(14px,1vw,18px)]"
          >
            <li class="whitespace-nowrap font-bold text-[#25332b]" aria-current="step">
              <span aria-hidden="true">①</span>
              メール入力
            </li>
            <li aria-hidden="true"><ChevronRight class="size-3.5" :stroke-width="2" /></li>
            <li class="whitespace-nowrap">
              <span aria-hidden="true">②</span>
              メール確認
            </li>
            <li aria-hidden="true"><ChevronRight class="size-3.5" :stroke-width="2" /></li>
            <li class="whitespace-nowrap">
              <span aria-hidden="true">③</span>
              情報入力
            </li>
          </ol>
        </nav>
        <header>
          <h1
            id="registration-title"
            class="text-[26px] leading-[1.35] font-bold text-[#1e2923] min-[761px]:text-[clamp(28px,2vw,36px)]"
          >
            生産者登録
          </h1>
          <p
            class="mt-3.5 text-[15px] leading-[1.7] text-[#87968d] min-[761px]:text-[clamp(16px,1.2vw,20px)]"
          >
            登録に使用するメールアドレスを入力してください。
            <br class="hidden min-[761px]:block" />
            確認コードをメールでお送りします。
          </p>
        </header>
        <form
          class="mt-7 grid gap-6 min-[761px]:mt-[34px] min-[761px]:gap-9"
          novalidate
          @submit.prevent="submit"
        >
          <div class="grid gap-[9px] min-[761px]:gap-2.5">
            <UiFormLabel
              for="registration-email"
              class="text-[15px] font-semibold text-[#687b70] min-[761px]:text-lg min-[761px]:font-medium min-[761px]:text-[#87968d]"
            >
              メールアドレス
            </UiFormLabel>
            <UiInput
              id="registration-email"
              v-model="email"
              name="email"
              type="email"
              autocomplete="email"
              inputmode="email"
              placeholder="name@example.jp"
              required
              class="h-[58px] rounded-[10px] border-[1.5px] border-[#d5e2da] px-4 text-base shadow-none min-[761px]:h-[76px] min-[761px]:rounded-xl min-[761px]:px-6 min-[761px]:text-xl"
              :aria-invalid="Boolean(emailError)"
              :aria-describedby="emailError ? 'registration-email-error' : undefined"
            />
            <UiFormMessage v-if="emailError" id="registration-email-error" role="alert">
              {{ emailError }}
            </UiFormMessage>
          </div>
          <UiFormMessage v-if="unavailableMessage" role="alert">
            {{ unavailableMessage }}
          </UiFormMessage>
          <UiButton
            type="submit"
            class="min-h-[58px] w-full rounded-[10px] border-0 bg-[#237b4d] text-[17px] hover:bg-[#1e7045] min-[761px]:min-h-[68px] min-[761px]:rounded-xl min-[761px]:text-[21px]"
          >
            確認コードを送信する
          </UiButton>
        </form>
        <p class="mt-[26px] text-[15px] text-[#87968d] min-[761px]:mt-[34px] min-[761px]:text-lg">
          アカウントをお持ちですか？
          <RouterLink
            to="/login"
            class="font-medium text-[#368357] underline underline-offset-[3px] focus-visible:outline-2 focus-visible:outline-offset-4"
          >
            ログイン
          </RouterLink>
        </p>
      </div>
    </section>
  </main>
</template>
