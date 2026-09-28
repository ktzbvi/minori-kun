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
    <aside class="brand-panel">
      <div class="brand-panel__content">
        <p class="brand-panel__logo">みのりくん</p>
        <h2>生産者向けログイン</h2>
        <p class="brand-panel__description">登録済みの生産者アカウントでログインしてください。</p>
      </div>
    </aside>

    <section class="login-panel">
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
          <h1 class="intro__desktop-title">生産者ログイン</h1>
          <h1 class="intro__mobile-title">生産者ポータル</h1>
          <p class="intro__desktop-description">メールアドレスとパスワードを入力してください。</p>
          <p class="intro__mobile-description">生産者アカウントでログインしてください。</p>
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
    </section>
  </main>
</template>

<style scoped>
.login-page {
  display: grid;
  min-height: 100vh;
  grid-template-columns: 46.3% minmax(0, 1fr);
  background: #f2f6f3;
}

.brand-panel {
  display: flex;
  align-items: center;
  padding: 48px clamp(40px, 3.8vw, 96px);
  color: #eaf0dd;
  background: #123729;
}
.brand-panel__content { width: 100%; max-width: 700px; }
.brand-panel__logo { margin: 0 0 22px; font-size: clamp(36px, 3vw, 52px); font-weight: 800; font-style: italic; letter-spacing: .04em; }
.brand-panel h2 { margin: 0 0 22px; color: #a8c2ae; font-size: clamp(22px, 1.7vw, 30px); font-weight: 650; }
.brand-panel__description { margin: 0; color: #9bb5a5; font-size: clamp(16px, 1.2vw, 21px); line-height: 1.7; }

.login-panel {
  display: grid;
  min-width: 0;
  min-height: 100vh;
  place-items: center;
  padding: 48px clamp(48px, 7.6vw, 160px);
  background: #f2f6f3;
}
.login-page__glow, .landscape, .brand, .intro__mobile-title, .intro__mobile-description { display: none; }
.login-card {
  width: min(772px, 100%);
  transform: translateY(-9vh);
  padding: 0;
  border: 0;
  border-radius: 0;
  background: transparent;
  box-shadow: none;
}
.intro { margin-bottom: 24px; }
.intro h1 { margin: 0; color: #1e2923; font-size: clamp(28px, 2vw, 36px); font-weight: 750; line-height: 1.35; }
.intro__desktop-description { margin: 16px 0 0; color: #87968d; font-size: clamp(16px, 1.2vw, 20px); }
.login-form { display: grid; gap: 22px; }
.field { display: grid; gap: 12px; }
.field__label { color: #87968d; font-size: 18px; font-weight: 500; }
.field__control {
  display: flex;
  min-height: 76px;
  align-items: center;
  gap: 14px;
  padding: 0 24px;
  border: 1.5px solid #d5e2da;
  border-radius: 12px;
  background: #fff;
  color: #708278;
  transition: border-color .16s, box-shadow .16s;
}
.field__control:focus-within { border-color: #438c64; box-shadow: 0 0 0 3px rgb(51 135 91 / 10%); }
.field__icon { display: none; }
.field input { width: 100%; min-width: 0; border: 0; outline: 0; color: #202721; background: transparent; font: inherit; font-size: 20px; }
.field input::placeholder { color: #9aa69f; opacity: 1; }
.visibility-toggle { display: grid; flex: 0 0 auto; place-items: center; padding: 5px; border: 0; color: #71857a; background: transparent; cursor: pointer; opacity: 0; }
.field__control:hover .visibility-toggle, .field__control:focus-within .visibility-toggle { opacity: 1; }
.visibility-toggle:focus-visible, a:focus-visible { outline: 3px solid #438c64; outline-offset: 3px; opacity: 1; }
.form-actions { display: flex; margin-top: 1px; }
.form-actions a, .signup-prompt a { color: #368357; font-size: 18px; font-weight: 500; text-decoration: none; }
.submit-button {
  width: 100%;
  min-height: 68px;
  margin-top: 2px;
  border: 0;
  border-radius: 12px;
  color: #fff;
  background: #237b4d;
  box-shadow: none;
  font-size: 21px;
  font-weight: 700;
}
.submit-button:disabled { cursor: wait; opacity: .75; }
.signup-prompt { display: flex; flex-wrap: wrap; gap: 8px 20px; margin-top: 30px; color: #87968d; font-size: 18px; }
.signup-prompt a { text-decoration: underline; text-underline-offset: 3px; }

@media (max-width: 760px) {
  .login-page { display: block; min-height: 100svh; background: transparent; }
  .brand-panel { display: none; }
  .login-panel {
    position: relative;
    isolation: isolate;
    min-height: 100svh;
    overflow: hidden;
    padding: 28px 16px 175px;
    background: linear-gradient(145deg, #e5f3ed 0%, #f4f8ef 68%, #f4f7ec 100%);
  }
  .login-page__glow {
    position: absolute;
    z-index: -1;
    right: 12%;
    bottom: 14%;
    display: block;
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
    display: block;
    width: 100%;
    height: 190px;
  }
  .login-card {
    width: min(100%, 724px);
    transform: none;
    padding: 34px 24px 32px;
    border: 0;
    border-radius: 30px;
    background: #fff;
    box-shadow: 0 25px 60px rgb(33 75 54 / 11%);
  }
  .brand { display: flex; align-items: center; gap: 14px; }
  .brand__mark {
    display: grid;
    width: 62px;
    height: 62px;
    flex: 0 0 auto;
    place-items: center;
    border-radius: 50%;
    color: #fff;
    background: #e8f4ed;
  }
  .brand__mark :deep(svg) { width: 42px; height: 42px; padding: 7px; border-radius: 50%; background: #20794d; }
  .brand__name { color: #20794d; font-size: 29px; font-weight: 800; letter-spacing: .08em; }
  .intro { margin: 28px 0 0; }
  .intro__desktop-title, .intro__desktop-description { display: none; }
  .intro__mobile-title { display: block; margin: 0; color: #183c2c; font-size: 22px; font-weight: 750; line-height: 1.4; }
  .intro__mobile-description { display: block; margin: 7px 0 0; color: #687b70; font-size: 15px; }
  .login-form { gap: 20px; margin-top: 34px; }
  .field { gap: 9px; }
  .field__label { color: #1e3f30; font-size: 16px; font-weight: 700; }
  .field__control { min-height: 58px; gap: 13px; padding: 0 14px; border: 1.5px solid #cbded2; border-radius: 16px; box-shadow: 0 2px 3px rgb(30 68 48 / 6%); }
  .field__icon { display: block; width: 21px; height: 21px; flex: 0 0 auto; }
  .field input { font-size: 15px; }
  .visibility-toggle { color: #778a7f; opacity: 1; }
  .visibility-toggle svg { width: 21px; height: 21px; }
  .form-actions { justify-content: flex-end; margin-top: -1px; }
  .form-actions a, .signup-prompt a { color: #20794d; font-size: 14px; font-weight: 650; text-underline-offset: 4px; }
  .submit-button { min-height: 62px; margin-top: 2px; border-radius: 16px; background: linear-gradient(105deg, #2b9257, #17643e); box-shadow: 0 12px 22px rgb(29 98 59 / 22%); font-size: 18px; }
  .signup-prompt { justify-content: center; gap: 5px 12px; margin-top: 30px; font-size: 14px; text-align: center; }
}

@media (max-width: 360px) {
  .login-card { padding-right: 18px; padding-left: 18px; }
  .field__control { gap: 9px; padding: 0 11px; }
}
</style>
