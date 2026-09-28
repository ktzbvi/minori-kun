<script setup lang="ts">
import axios from 'axios'
import { ChevronLeft, Leaf } from 'lucide-vue-next'
import { computed, onMounted, reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { toast, UiButton, UiCard } from '@minorikun/ui'
import { authApi, buyerRegistrationApi } from '@/lib/api'
import { queryClient } from '@/lib/query'

const router = useRouter()
const route = useRoute()
const email = ref('')
const submitting = ref(false)
const form = reactive({
  name: '',
  name_phonetic: '',
  phone: '',
  postal_code: '',
  prefecture: '',
  city: '',
  address_line1: '',
  address_line2: '',
  password: '',
  password_confirmation: '',
  terms_accepted: false,
})
const canSubmit = computed(() => form.terms_accepted && !submitting.value)
const fields = [
  ['name', 'お名前', true, '山田 太郎'],
  ['name_phonetic', 'フリガナ', true, 'ヤマダ タロウ'],
  ['password', 'パスワード', true, '半角英数字8文字以上'],
  ['password_confirmation', 'パスワード確認', true, 'もう一度入力してください'],
  ['phone', '電話番号', true, '090-0000-0000'],
  ['postal_code', '郵便番号', true, '123-4567'],
  ['prefecture', '都道府県', true, '東京都'],
  ['city', '市区町村', true, '渋谷区'],
  ['address_line1', '住所', true, '神宮前1-2-3'],
  ['address_line2', '建物名・部屋番号', false, '○○ビル101'],
] as const
onMounted(async () => {
  try {
    const { data } = await buyerRegistrationApi.status()
    if (!data.data.verified) throw new Error()
    email.value = data.data.email
  } catch {
    await router.replace({ name: 'register' })
  }
})
async function submit() {
  if (!canSubmit.value) return
  submitting.value = true
  try {
    await authApi.csrf()
    const { data } = await buyerRegistrationApi.complete(form)
    await queryClient.invalidateQueries({ queryKey: ['current-session'] })
    await router.replace(
      typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
        ? route.query.redirect
        : data.data.redirect,
    )
    toast.success('登録しました。')
  } catch (error) {
    toast.error(
      axios.isAxiosError(error) && error.response?.status === 422
        ? '入力内容を確認してください。'
        : '登録に失敗しました。',
    )
  } finally {
    submitting.value = false
  }
}
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
          <ChevronLeft :size="22" />
        </button>
        <h1 class="text-[16px] font-bold text-[#227644]">新規会員登録</h1>
      </header>
      <div class="px-4 pt-3 pb-8">
        <ol class="flex justify-between text-[10px] text-[#8a978e]">
          <li class="text-[#237f4b]">✓ メール入力</li>
          <li class="text-[#237f4b]">✓ メール確認</li>
          <li class="font-bold text-[#24372b]">③ 情報入力</li>
        </ol>
        <UiCard class="mt-3 !rounded-[11px] !border-[#dbe5dc] !bg-white !p-4 !shadow-none"
          ><div class="mb-4 flex items-center justify-center gap-2">
            <span class="grid size-8 place-items-center rounded-full bg-[#e4f3e9] text-[#237d4a]"
              ><Leaf :size="19" /></span
            ><strong class="text-[17px] text-[#217848]">みのりくん</strong>
          </div>
          <form class="grid gap-3" @submit.prevent="submit">
            <div>
              <label class="label">メールアドレス</label>
              <p class="input verified">{{ email }} <b>✓確認済み</b></p>
            </div>
            <div v-for="field in fields" :key="field[0]">
              <label class="label" :for="`registration-${field[0]}`"
                >{{ field[1] }} <span v-if="field[2]" class="required">必須</span></label
              ><input
                :id="`registration-${field[0]}`"
                v-model="form[field[0]]"
                :type="field[0].includes('password') ? 'password' : 'text'"
                :required="field[2]"
                :autocomplete="field[0] === 'password' ? 'new-password' : undefined"
                :placeholder="field[3]"
                class="input"
              />
            </div>
            <label class="flex items-center gap-2 text-[11px]"
              ><input
                v-model="form.terms_accepted"
                type="checkbox"
                class="size-4 accent-[#237f4b]"
              />利用規約に同意する</label
            ><UiButton type="submit" :disabled="!canSubmit">{{
              submitting ? '登録中...' : '登録する'
            }}</UiButton>
          </form></UiCard
        >
        <p class="mt-4 text-center text-[11px] text-[#78867d]">
          すでにアカウントをお持ちの方
          <RouterLink to="/login" class="font-bold text-[#237f4b] underline">ログイン</RouterLink>
        </p>
      </div>
    </section>
  </main>
</template>
<style scoped>
.label {
  display: block;
  margin-bottom: 5px;
  font-size: 11px;
  font-weight: 700;
}
.required {
  border-radius: 3px;
  background: #d84444;
  padding: 1px 4px;
  font-size: 9px;
  color: #fff;
}
.input {
  box-sizing: border-box;
  width: 100%;
  min-height: 33px;
  border: 1px solid #dce5dc;
  border-radius: 5px;
  background: #fff;
  padding: 0 9px;
  font-size: 12px;
  outline: none;
}
.input:focus {
  border-color: #237f4b;
  box-shadow: 0 0 0 3px rgb(35 127 75 / 15%);
}
.verified {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f4f7f2;
  color: #8a978e;
}
.verified b {
  font-size: 10px;
  color: #237f4b;
}
</style>
