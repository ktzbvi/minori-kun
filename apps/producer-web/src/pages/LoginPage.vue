<script setup lang="ts">
import { ref } from 'vue'
import { Eye, EyeOff, Leaf, LockKeyhole, Mail } from 'lucide-vue-next'
import { UiButton, UiCard } from '@minorikun/ui'
import { useProducerLogin } from '@/composables/useProducerLogin'

const email = ref('')
const password = ref('')
const passwordVisible = ref(false)
const { isLoggingIn, login } = useProducerLogin(() => {
  password.value = ''
})

function submit() {
  login(email.value, password.value)
}
</script>

<template>
  <main class="login-page">
    <div class="login-page__glow" aria-hidden="true"></div>
    <svg class="landscape" viewBox="0 0 1440 360" preserveAspectRatio="none" aria-hidden="true">
      <path d="M0 110C190 10 310 42 470 112c210 92 300 18 490-18 192-37 294-67 480-28v294H0Z" fill="#dcefe6" />
      <path d="M0 178c180-88 292-70 440-4 210 93 304 44 494-12 220-66 336-78 506-20v218H0Z" fill="#bfe0ce" />
      <path d="M0 252c171-95 316-83 470-26 204 75 288 54 480-15 214-77 334-81 490-45v210H0Z" fill="#91c8a9" />
      <g fill="none" stroke="#428c67" stroke-linecap="round" stroke-width="7" opacity=".43">
        <path d="M-20 340c156-126 301-151 470-174M100 360c166-120 300-140 501-175m-258 175c147-106 299-145 478-161m96 161c114-78 245-111 416-132" />
        <path d="m170 310 0-86 76-68 76 68v86m-132 0v-87h112v87m-145 0h176" />
      </g>
      <g fill="#ecd990" opacity=".85"><circle cx="602" cy="120" r="11"/><circle cx="642" cy="101" r="8"/><circle cx="684" cy="124" r="9"/></g>
    </svg>

    <UiCard class="login-card">
      <header class="brand">
        <span class="brand__mark" aria-hidden="true"><Leaf :size="37" :stroke-width="2.2" /></span>
        <span class="brand__name">みのりくん</span>
      </header>

      <section class="intro">
        <h1>生産者ポータル</h1>
        <p>生産者アカウントでログインしてください。</p>
      </section>

      <form class="login-form" @submit.prevent="submit">
        <label class="field">
          <span class="field__label">メールアドレス</span>
          <span class="field__control">
            <Mail class="field__icon" :size="27" aria-hidden="true" />
            <input
              v-model="email"
              type="email"
              name="email"
              autocomplete="username"
              placeholder="メールアドレスを入力"
              required
            />
          </span>
        </label>

        <label class="field">
          <span class="field__label">パスワード</span>
          <span class="field__control">
            <LockKeyhole class="field__icon" :size="27" aria-hidden="true" />
            <input
              v-model="password"
              :type="passwordVisible ? 'text' : 'password'"
              name="password"
              autocomplete="current-password"
              placeholder="パスワードを入力"
              required
            />
            <button
              class="visibility-toggle"
              type="button"
              :aria-label="passwordVisible ? 'パスワードを隠す' : 'パスワードを表示する'"
              :aria-pressed="passwordVisible"
              @click="passwordVisible = !passwordVisible"
            >
              <Eye v-if="passwordVisible" :size="25" aria-hidden="true" />
              <EyeOff v-else :size="25" aria-hidden="true" />
            </button>
          </span>
        </label>

        <div class="form-actions">
          <a href="/password-reset">パスワードをお忘れですか？</a>
        </div>

        <UiButton class="submit-button" type="submit" :disabled="isLoggingIn">
          {{ isLoggingIn ? 'ログイン中…' : 'ログインする' }}
        </UiButton>
      </form>

      <footer class="signup-prompt">
        <span>アカウントをお持ちでない方</span>
        <a href="/register">生産者登録</a>
      </footer>
    </UiCard>
  </main>
</template>

<style scoped>
.login-page {
  position: relative;
  isolation: isolate;
  display: grid;
  min-height: 100svh;
  place-items: center;
  overflow: hidden;
  padding: 48px 28px 250px;
  background: linear-gradient(145deg, #e5f3ed 0%, #f4f8ef 68%, #f4f7ec 100%);
}

.login-page__glow {
  position: absolute;
  z-index: -1;
  right: 12%;
  bottom: 14%;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: #eee6b7;
  opacity: .5;
}

.landscape {
  position: absolute;
  z-index: -1;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: clamp(190px, 24vh, 360px);
}

.login-card {
  width: min(100%, 724px);
  padding: 64px 64px 62px;
  border: 0;
  border-radius: 48px;
  background: #fff;
  box-shadow: 0 25px 60px rgb(33 75 54 / 11%);
}

.brand { display: flex; align-items: center; gap: 24px; }
.brand__mark {
  display: grid;
  width: 88px;
  height: 88px;
  flex: 0 0 auto;
  place-items: center;
  border-radius: 50%;
  color: #fff;
  background: #e8f4ed;
}
.brand__mark :deep(svg) { width: 56px; height: 56px; padding: 9px; border-radius: 50%; background: #20794d; }
.brand__name { color: #20794d; font-size: clamp(30px, 5vw, 44px); font-weight: 800; letter-spacing: .08em; }
.intro { margin-top: 38px; }
.intro h1 { margin: 0; color: #183c2c; font-size: 28px; font-weight: 750; line-height: 1.4; }
.intro p { margin: 7px 0 0; color: #687b70; font-size: 21px; line-height: 1.6; }
.login-form { display: grid; gap: 27px; margin-top: 62px; }
.field { display: grid; gap: 14px; }
.field__label { color: #1e3f30; font-size: 22px; font-weight: 700; }
.field__control {
  display: flex;
  min-height: 76px;
  align-items: center;
  gap: 22px;
  padding: 0 24px;
  border: 1.5px solid #cbded2;
  border-radius: 22px;
  box-shadow: 0 2px 3px rgb(30 68 48 / 6%);
  color: #778a7f;
  transition: border-color .16s, box-shadow .16s;
}
.field__control:focus-within { border-color: #33875b; box-shadow: 0 0 0 3px rgb(51 135 91 / 12%); }
.field__icon { flex: 0 0 auto; }
.field input { width: 100%; min-width: 0; border: 0; outline: 0; color: #263d31; background: transparent; font: inherit; font-size: 21px; }
.field input::placeholder { color: #718579; opacity: 1; }
.visibility-toggle { display: grid; flex: 0 0 auto; place-items: center; padding: 5px; border: 0; color: #778a7f; background: transparent; cursor: pointer; }
.visibility-toggle:focus-visible, a:focus-visible { outline: 3px solid #33875b; outline-offset: 3px; }
.form-actions { display: flex; justify-content: flex-end; margin-top: -1px; }
.form-actions a, .signup-prompt a { color: #20794d; font-size: 18px; font-weight: 650; text-underline-offset: 4px; }
.submit-button { width: 100%; min-height: 82px; margin-top: 2px; border: 0; border-radius: 23px; color: #fff; background: linear-gradient(105deg, #2b9257, #17643e); box-shadow: 0 12px 22px rgb(29 98 59 / 22%); font-size: 24px; font-weight: 750; }
.submit-button:disabled { cursor: wait; opacity: .75; }
.signup-prompt { display: flex; flex-wrap: wrap; justify-content: center; gap: 7px 18px; margin-top: 48px; color: #718176; font-size: 18px; text-align: center; }

@media (max-width: 600px) {
  .login-page { min-height: 100svh; padding: 28px 16px 175px; }
  .landscape { height: 190px; }
  .login-card { padding: 34px 24px 32px; border-radius: 30px; }
  .brand { gap: 14px; }
  .brand__mark { width: 62px; height: 62px; }
  .brand__mark :deep(svg) { width: 42px; height: 42px; padding: 7px; }
  .brand__name { font-size: 29px; }
  .intro { margin-top: 28px; }
  .intro h1 { font-size: 22px; }
  .intro p { font-size: 15px; }
  .login-form { gap: 20px; margin-top: 34px; }
  .field { gap: 9px; }
  .field__label { font-size: 16px; }
  .field__control { min-height: 58px; gap: 13px; padding: 0 14px; border-radius: 16px; }
  .field__icon { width: 21px; height: 21px; }
  .field input { font-size: 15px; }
  .visibility-toggle svg { width: 21px; height: 21px; }
  .form-actions a, .signup-prompt a { font-size: 14px; }
  .submit-button { min-height: 62px; border-radius: 16px; font-size: 18px; }
  .signup-prompt { gap: 5px 12px; margin-top: 30px; font-size: 14px; }
}

@media (max-width: 360px) {
  .login-card { padding-right: 18px; padding-left: 18px; }
  .field__control { gap: 9px; padding: 0 11px; }
}
</style>
