<script setup lang="ts">
import axios from 'axios'
import { ChevronLeft, Leaf } from 'lucide-vue-next'
import { ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { UiButton, UiCard } from '@minorikun/ui'
import { authApi } from '@/lib/api'
import { queryClient } from '@/lib/query'

const email = ref('')
const password = ref('')
const errorMessage = ref('')
const isSubmitting = ref(false)
const route = useRoute()
const router = useRouter()

const requiredFieldsError = 'メールアドレスとパスワードを入力してください。'
const authenticationError = 'メールアドレスまたはパスワードを確認してください。'
const rateLimitError = '試行回数が多すぎます。しばらくしてからもう一度お試しください。'
const serviceError = '現在ログインできません。時間をおいてからもう一度お試しください。'

async function submit() {
  errorMessage.value = ''
  if (email.value.trim().length === 0 || password.value.length === 0) {
    errorMessage.value = requiredFieldsError
    return
  }

  isSubmitting.value = true
  try {
    await authApi.csrf()
    await authApi.login(email.value.trim(), password.value)
    await queryClient.invalidateQueries({ queryKey: ['current-session'] })

    const redirect =
      typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
        ? route.query.redirect
        : '/'
    await router.replace(redirect)
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.status === 429) {
      errorMessage.value = rateLimitError
    } else if (axios.isAxiosError(error) && error.response?.status !== 422) {
      errorMessage.value = serviceError
    } else {
      errorMessage.value = authenticationError
    }
    password.value = ''
  } finally {
    isSubmitting.value = false
  }
}

function goBack() {
  router.back()
}
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] py-0 text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="min-h-screen w-full max-w-[375px] bg-[#fbfcfa] sm:min-h-[728px] sm:shadow-sm">
      <header class="flex h-[65px] items-center gap-3 border-b border-[#e1e8e1] px-5">
        <button
          class="grid size-9 place-items-center rounded-full border-0 bg-transparent text-[#267c4a]"
          type="button"
          aria-label="戻る"
          @click="goBack"
        >
          <ChevronLeft :size="22" :stroke-width="2.5" aria-hidden="true" />
        </button>
        <h1 class="m-0 text-[16px] font-bold text-[#227644]">ログイン</h1>
      </header>

      <div class="px-[33px] pt-[75px] pb-12">
        <UiCard class="!rounded-[11px] !border-[#dbe5dc] !bg-white !p-4.5 !shadow-none">
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
              <label class="text-[13px] font-bold text-[#24372b]" for="buyer-email">
                メールアドレス
                <span class="rounded bg-[#d84444] px-1 py-px text-[10px] text-white">必須</span>
              </label>
              <input
                id="buyer-email"
                v-model="email"
                class="min-h-[39px] rounded-[6px] border border-[#dce5dc] bg-white px-2.5 text-[13px] outline-none placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                type="email"
                autocomplete="username"
                inputmode="email"
                placeholder="example@farm-ec.jp"
                required
              />
            </div>

            <div class="grid gap-1.5">
              <label class="text-[13px] font-bold text-[#24372b]" for="buyer-password">
                パスワード
                <span class="rounded bg-[#d84444] px-1 py-px text-[10px] text-white">必須</span>
              </label>
              <input
                id="buyer-password"
                v-model="password"
                class="min-h-[39px] rounded-[6px] border border-[#dce5dc] bg-white px-2.5 text-[13px] outline-none placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                type="password"
                autocomplete="current-password"
                placeholder="半角英数字8文字以上"
                required
              />
            </div>

            <p v-if="errorMessage" class="m-0 text-xs font-medium text-[#b33a2b]" role="alert">
              {{ errorMessage }}
            </p>

            <UiButton
              class="mt-0.5 !min-h-[37px] !rounded-[5px] !px-4 !text-[14px] disabled:cursor-not-allowed disabled:!border-[#9cbca7] disabled:!bg-[#9cbca7]"
              type="submit"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'ログイン中...' : 'ログイン' }}
            </UiButton>
          </form>
        </UiCard>

        <nav class="mt-7 grid justify-items-center gap-4 text-[12px]" aria-label="ログイン支援">
          <div class="grid justify-items-center gap-1">
            <span class="text-[#78867d]">アカウントをお持ちでない方</span>
            <RouterLink
              class="font-bold text-[#237f4b] underline underline-offset-2"
              to="/register"
            >
              新規会員登録
            </RouterLink>
          </div>
          <RouterLink
            class="font-medium text-[#6e7e74] underline underline-offset-2"
            to="/password-reset"
          >
            パスワードをお忘れですか？
          </RouterLink>
        </nav>
      </div>
    </section>
  </main>
</template>
