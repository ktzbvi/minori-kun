<script setup lang="ts">
import { ChevronLeft, Leaf } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { UiButton, UiCard } from '@minorikun/ui'
import { useBuyerRegisterDetails } from '@/composables/useBuyerRegisterDetails'
const {
  router,
  route,
  email,
  submitting,
  fieldErrors,
  form,
  fields,
  canSubmit,
  validatePhonetic,
  submit,
} = useBuyerRegisterDetails()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section
      class="flex h-dvh w-full max-w-[375px] flex-col overflow-hidden bg-[#fbfcfa] sm:h-[728px] sm:shadow-sm"
    >
      <header class="flex h-[65px] shrink-0 items-center gap-3 border-b border-[#e1e8e1] px-5">
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#267c4a]"
          type="button"
          aria-label="Back"
          @click="router.back()"
        >
          <ChevronLeft :size="22" />
        </button>
        <h1 class="text-[16px] font-bold text-[#227644]">
          新規会員登録
        </h1>
      </header>

      <ol
        class="flex shrink-0 justify-between border-b border-[#e1e8e1] px-4 py-3 text-[10px] text-[#8a978e]"
      >
        <li class="text-[#237f4b]">✓ メール入力</li>
        <li class="text-[#237f4b]">✓ メール確認</li>
        <li class="font-bold">③ 情報入力</li>
      </ol>

      <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-3 scroll-pb-5">
        <UiCard class="!rounded-[11px] !border-[#dbe5dc] !bg-white !p-3 !shadow-none">
          <div class="mb-4 flex items-center justify-center gap-2">
            <span class="grid size-8 place-items-center rounded-full bg-[#e4f3e9] text-[#237d4a]">
              <Leaf :size="19" />
            </span>
            <strong class="text-[17px] text-[#217848]">
              みのりくん
            </strong>
          </div>

          <form id="buyer-registration-details" class="grid gap-2" @submit.prevent="submit">
            <div>
              <label class="label">メールアドレス</label>
              <p class="input verified">
                {{ email }}
                <b>✓確認済み</b>
              </p>
            </div>

            <div v-for="field in fields" :key="field.name">
              <label class="label" :for="`registration-${field.name}`">
                {{ field.label }}
                <span v-if="field.required" class="required">必須</span>
              </label>
              <input
                :id="`registration-${field.name}`"
                v-model="form[field.name]"
                :type="field.name.includes('password') ? 'password' : 'text'"
                :required="field.required"
                :placeholder="field.placeholder"
                :class="['input', { 'input-error': fieldErrors[field.name] }]"
                :aria-describedby="
                  fieldErrors[field.name] ? `registration-${field.name}-error` : undefined
                "
                :aria-invalid="Boolean(fieldErrors[field.name])"
                @blur="field.name === 'name_phonetic' && validatePhonetic()"
              />
              <p
                v-if="fieldErrors[field.name]"
                :id="`registration-${field.name}-error`"
                class="field-error"
                role="alert"
              >
                {{ fieldErrors[field.name] }}
              </p>
            </div>
          </form>
        </UiCard>
      </div>

      <footer
        class="shrink-0 border-t border-[#e1e8e1] bg-white px-4 pt-2.5 pb-[max(0.75rem,env(safe-area-inset-bottom))]"
      >
        <label class="flex items-center gap-2 text-[11px]">
          <input v-model="form.terms_accepted" type="checkbox" class="size-4 accent-[#237f4b]" />
          利用規約に同意する
        </label>
        <UiButton
          form="buyer-registration-details"
          class="mt-2 block w-full mx-auto disabled:!border-[#9cbca7] disabled:!bg-[#9cbca7] disabled:!text-white"
          type="submit"
          :disabled="!canSubmit"
        >
          <span v-if="submitting">登録中...</span>
          <span v-else>登録する</span>
        </UiButton>
        <p class="mt-2 text-center text-[11px] text-[#78867d]">
          すでにアカウントをお持ちの方
          <RouterLink to="/login" class="font-bold text-[#237f4b] underline">
            ログイン
          </RouterLink>
        </p>
      </footer>
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
  min-height: 30px;
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

.input-error {
  border-color: #d84444;
}

.field-error {
  margin: 4px 0 0;
  color: #b33a2b;
  font-size: 11px;
  font-weight: 600;
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
