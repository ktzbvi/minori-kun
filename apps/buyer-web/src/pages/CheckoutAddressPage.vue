<script setup lang="ts">
import { ChevronLeft } from 'lucide-vue-next'
import { UiButton } from '@minorikun/ui'
import { useBuyerCheckoutAddress } from '@/composables/useBuyerCheckoutAddress'
const {
  router,
  route,
  submitting,
  saveAsDefault,
  fieldErrors,
  form,
  fields,
  orderConfirmationLocation,
  clearFieldError,
  validate,
  applyServerErrors,
  saveDefaultAddress,
  submit,
  cancel,
} = useBuyerCheckoutAddress()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="flex h-dvh w-full flex-col bg-[#f8faf6] sm:max-w-[375px] sm:shadow-sm">
      <header
        class="flex h-[65px] shrink-0 items-center gap-2 border-b border-[#e3e9e3] bg-white px-4"
      >
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="Back"
          @click="cancel"
        >
          <ChevronLeft :size="22" :stroke-width="2.5" />
        </button>
        <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">お届け先情報の変更</h1>
      </header>

      <form class="min-h-0 flex-1 overflow-y-auto px-4 py-4" novalidate @submit.prevent="submit">
        <section class="rounded-[8px] border border-[#dce5dc] bg-white p-3">
          <div v-for="field in fields" :key="field.key" class="mb-3 last:mb-0">
            <label class="mb-1 block text-[11px] font-bold" :for="`checkout-${field.key}`">
              {{ field.label }}
              <span v-if="field.required !== false" class="required">必須</span>
            </label>
            <input
              :id="`checkout-${field.key}`"
              v-model="form[field.key]"
              :type="field.type ?? 'text'"
              :inputmode="field.inputmode"
              :autocomplete="field.autocomplete"
              :class="['input', { 'input-error': fieldErrors[field.key] }]"
              :aria-invalid="Boolean(fieldErrors[field.key])"
              @input="clearFieldError(field.key)"
            />
            <p v-if="fieldErrors[field.key]" class="field-error" role="alert">
              {{ fieldErrors[field.key] }}
            </p>
          </div>

          <label class="mt-4 flex items-center gap-2 text-[11px] text-[#35463b]">
            <input v-model="saveAsDefault" class="size-4 accent-[#237f4b]" type="checkbox" />
            基本のお届け先として保存する
          </label>

          <UiButton class="mt-4 !w-full" type="submit" :disabled="submitting">
            <span v-if="submitting">保存中...</span>
            <span v-else>このお届け先を使用</span>
          </UiButton>
        </section>
      </form>
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
  box-sizing: border-box;
  min-height: 34px;
  width: 100%;
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
