<script setup lang="ts">
import axios from 'axios'
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast, UiButton } from '@minorikun/ui'
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

onMounted(async () => {
  try {
    const { data } = await buyerRegistrationApi.status()
    if (!data.data.verified) throw new Error('unverified')
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
    const redirect =
      typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
        ? route.query.redirect
        : data.data.redirect
    await router.replace(redirect)
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
      <header class="flex h-[65px] items-center border-b border-[#e1e8e1] px-5">
        <h1 class="text-[16px] font-bold text-[#227644]">新規会員登録</h1>
      </header>
      <form class="grid gap-3 px-5 py-4" @submit.prevent="submit">
        <p class="text-xs text-[#66756b]">{{ email }}（確認済み）</p>
        <input v-model="form.name" required placeholder="お名前" class="field" /><input
          v-model="form.name_phonetic"
          placeholder="フリガナ"
          class="field"
        /><input
          v-model="form.phone"
          required
          inputmode="tel"
          placeholder="電話番号"
          class="field"
        /><input v-model="form.postal_code" required placeholder="郵便番号" class="field" /><input
          v-model="form.prefecture"
          required
          placeholder="都道府県"
          class="field"
        /><input v-model="form.city" required placeholder="市区町村" class="field" /><input
          v-model="form.address_line1"
          required
          placeholder="番地"
          class="field"
        /><input v-model="form.address_line2" placeholder="建物名・部屋番号" class="field" /><input
          v-model="form.password"
          required
          type="password"
          autocomplete="new-password"
          placeholder="パスワード"
          class="field"
        /><input
          v-model="form.password_confirmation"
          required
          type="password"
          autocomplete="new-password"
          placeholder="パスワード確認"
          class="field"
        /><label class="flex items-center gap-2 text-xs"
          ><input v-model="form.terms_accepted" type="checkbox" />利用規約に同意する</label
        ><UiButton type="submit" :disabled="!canSubmit">{{
          submitting ? '登録中...' : '登録する'
        }}</UiButton>
      </form>
    </section>
  </main>
</template>

<style scoped>
.field {
  min-height: 40px;
  border: 1px solid #dce5dc;
  border-radius: 6px;
  background: white;
  padding: 0 10px;
  font-size: 13px;
  outline: none;
}
.field:focus {
  border-color: #237f4b;
  box-shadow: 0 0 0 3px rgb(35 127 75 / 15%);
}
</style>
