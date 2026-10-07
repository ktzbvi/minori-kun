<script setup lang="ts">
import { ChevronLeft } from 'lucide-vue-next'
import { UiButton } from '@minorikun/ui'
import { useBuyerMemberInfo } from '@/composables/useBuyerMemberInfo'
const {
  router,
  route,
  loading,
  submitting,
  message,
  fieldErrors,
  form,
  fields,
  validatePhonetic,
  submit,
} = useBuyerMemberInfo()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section
      class="flex h-dvh w-full max-w-[375px] flex-col overflow-hidden bg-[#f8faf6] sm:h-[728px] sm:shadow-sm"
    >
      <header
        class="flex h-[65px] shrink-0 items-center gap-3 border-b border-[#e3e9e3] bg-white px-4"
      >
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="Back"
          @click="router.back()"
        >
          <ChevronLeft :size="22" :stroke-width="2.5" />
        </button>
        <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">
          会員情報の変更
        </h1>
      </header>

      <p class="shrink-0 border-b border-[#e3e9e3] px-5 py-2 text-[10px] text-[#718075]">
        登録済みの会員情報を確認・変更できます。
      </p>

      <form class="min-h-0 flex-1 overflow-y-auto px-4 py-3" novalidate @submit.prevent="submit">
        <p
          v-if="message"
          class="mb-3 rounded-[5px] bg-[#e4f3e9] px-3 py-2 text-[11px] text-[#237d4a]"
          role="status"
        >
          {{ message }}
        </p>
        <div v-if="loading" class="grid h-40 place-items-center text-[12px] text-[#718075]">
          読み込み中...
        </div>
        <div v-else class="rounded-[8px] border border-[#dce5dc] bg-white p-3">
          <div v-for="field in fields" :key="field.key" class="mb-2.5 last:mb-0">
            <label class="mb-1 block text-[11px] font-bold" :for="`member-${field.key}`">
              {{ field.label }}
              <span v-if="field.required" class="required">必須</span>
            </label>
            <input
              :id="`member-${field.key}`"
              v-model="form[field.key]"
              :type="field.type ?? 'text'"
              :autocomplete="field.key === 'email' ? 'email' : undefined"
              :class="['input', { 'input-error': fieldErrors[field.key] }]"
              :aria-invalid="Boolean(fieldErrors[field.key])"
              @blur="field.key === 'name_phonetic' && validatePhonetic()"
            />
            <p v-if="fieldErrors[field.key]" class="field-error" role="alert">
              {{ fieldErrors[field.key] }}
            </p>
          </div>
        </div>
      </form>

      <footer
        class="shrink-0 border-t border-[#dce5dc] bg-white p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]"
      >
        <UiButton class="!w-full" type="button" :disabled="loading || submitting" @click="submit">
          <span v-if="submitting">保存中...</span>
          <span v-else>変更内容を保存</span>
        </UiButton>
      </footer>
    </section>
  </main>
</template>

<style scoped>
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
.input-error {
  border-color: #d84444;
}
.field-error {
  margin: 4px 0 0;
  color: #b33a2b;
  font-size: 10px;
  font-weight: 600;
}
</style>
