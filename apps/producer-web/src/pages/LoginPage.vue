<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { UiButton, UiCard } from '@minorikun/ui'
import { authApi } from '@/lib/api'
import { queryClient } from '@/lib/query'

const email = ref('')
const password = ref('')
const error = ref('')
const router = useRouter()

async function submit() {
  error.value = ''
  try {
    await authApi.csrf()
    await authApi.login(email.value, password.value)
    await queryClient.invalidateQueries({ queryKey: ['current-session'] })
    await router.push('/')
  } catch {
    error.value = 'メールアドレスまたはパスワードを確認してください。'
  }
}
</script>

<template>
  <main class="login"><UiCard><h1>ログイン</h1><form @submit.prevent="submit">
    <label>メールアドレス<input v-model="email" type="email" autocomplete="email" required /></label>
    <label>パスワード<input v-model="password" type="password" autocomplete="current-password" required /></label>
    <p v-if="error" role="alert">{{ error }}</p><UiButton type="submit">ログインする</UiButton>
  </form></UiCard></main>
</template>

<style scoped>
.login { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
.ui-card { width: min(420px, 100%); }
form, label { display: grid; gap: 12px; } form { gap: 20px; }
input { min-height: 44px; border: 1px solid var(--color-border); border-radius: 8px; padding: 0 12px; }
[role='alert'] { color: #b74434; }
</style>
