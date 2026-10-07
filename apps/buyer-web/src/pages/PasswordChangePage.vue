<script setup lang="ts">
import { ChevronLeft } from 'lucide-vue-next'
import { UiButton } from '@minorikun/ui'
import { useBuyerPasswordChange } from '@/composables/useBuyerPasswordChange'
const { router, submitting, fieldErrors, form, passwordPolicy, validatePassword, submit } =
  useBuyerPasswordChange()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="min-h-screen w-full max-w-[375px] bg-[#f8faf6] sm:min-h-[728px] sm:shadow-sm">
      <header class="flex h-[65px] items-center gap-3 border-b border-[#e3e9e3] bg-white px-4">
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="Back"
          @click="router.back()"
        >
          <ChevronLeft :size="22" :stroke-width="2.5" />
        </button>
        <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">
          パスワードの変更
        </h1>
      </header>

      <p class="px-5 pt-3 text-[10px] text-[#718075]">
        ログインパスワードを変更します。
      </p>
      <form
        class="mx-5 mt-3 rounded-[8px] border border-[#dce5dc] bg-white p-3"
        novalidate
        @submit.prevent="submit"
      >
        <div class="field">
          <label for="current-password">
            現在のパスワード
            <span class="required">必須</span>
          </label>
          <input
            id="current-password"
            v-model="form.current_password"
            class="input"
            type="password"
            autocomplete="current-password"
          />
          <p v-if="fieldErrors.current_password" class="field-error" role="alert">
            {{ fieldErrors.current_password }}
          </p>
        </div>
        <div class="field">
          <label for="password">
            新しいパスワード
            <span class="required">必須</span>
          </label>
          <input
            id="password"
            v-model="form.password"
            class="input"
            type="password"
            autocomplete="new-password"
            @blur="validatePassword"
          />
          <p v-if="fieldErrors.password" class="field-error" role="alert">
            {{ fieldErrors.password }}
          </p>
        </div>
        <div class="field">
          <label for="password-confirmation">
            新しいパスワード
            （確認）
            <span class="required">必須</span>
          </label>
          <input
            id="password-confirmation"
            v-model="form.password_confirmation"
            class="input"
            type="password"
            autocomplete="new-password"
            @blur="validatePassword"
          />
          <p v-if="fieldErrors.password_confirmation" class="field-error" role="alert">
            {{ fieldErrors.password_confirmation }}
          </p>
        </div>
        <UiButton class="!mt-1 !w-full" type="submit" :disabled="submitting">
          <span v-if="submitting">保存中...</span>
          <span v-else>変更内容を保存</span>
        </UiButton>
      </form>

      <RouterLink
        class="mx-auto mt-4 block w-fit text-[11px] font-medium text-[#6e7e74] underline underline-offset-2"
        :to="{ name: 'password-reset' }"
      >
        パスワードをお忘れですか？
      </RouterLink>
    </section>
  </main>
</template>

<style scoped>
.field {
  margin-bottom: 11px;
}
.field label {
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
  width: 100%;
  min-height: 33px;
  box-sizing: border-box;
  border: 1px solid #dce5dc;
  border-radius: 5px;
  padding: 0 9px;
  font-size: 12px;
  outline: none;
}
.input:focus {
  border-color: #237f4b;
  box-shadow: 0 0 0 3px rgb(35 127 75 / 15%);
}
.field-error {
  margin: 4px 0 0;
  color: #b33a2b;
  font-size: 10px;
  font-weight: 600;
}
</style>
