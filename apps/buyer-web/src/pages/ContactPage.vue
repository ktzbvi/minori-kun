<script setup lang="ts">
import { ChevronLeft } from 'lucide-vue-next'
import { UiButton, UiCard } from '@minorikun/ui'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useBuyerContact } from '@/composables/useBuyerContact'
const { router, form, fieldErrors, isSubmitting, idempotencyKey, clearErrors, validate, submit } =
  useBuyerContact()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section
      class="relative flex h-dvh w-full max-w-[375px] flex-col overflow-hidden bg-[#f8faf6] sm:h-[728px] sm:shadow-sm"
    >
      <header
        class="flex h-[65px] shrink-0 items-center gap-2 border-b border-[#e3e9e3] bg-white px-3"
      >
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="Back to My Page"
          @click="router.push({ name: 'my-page' })"
        >
          <ChevronLeft :size="21" :stroke-width="2.5" aria-hidden="true" />
        </button>
        <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">お問い合わせ</h1>
      </header>

      <div class="min-h-0 flex-1 overflow-y-auto px-5 pt-4 pb-[76px]">
        <p class="m-0 mb-4 text-[10px] text-[#718075]">件名とお問い合わせ内容をご入力ください。</p>

        <UiCard class="!rounded-[8px] !border-[#dce5dc] !bg-white !p-3 !shadow-none">
          <form class="grid gap-3" novalidate @submit.prevent="submit">
            <div class="grid gap-1.5">
              <label class="text-[11px] font-bold text-[#24372b]" for="contact-subject">
                件名
                <span class="rounded bg-[#d84444] px-1 py-px text-[9px] text-white">必須</span>
              </label>
              <input
                id="contact-subject"
                v-model="form.subject"
                class="min-h-[34px] rounded-[5px] border border-[#dce5dc] bg-white px-2.5 text-[12px] outline-none placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                :class="{ '!border-[#d84444]': fieldErrors.subject }"
                type="text"
                maxlength="255"
                autocomplete="off"
                placeholder="件名を入力してください。"
                :aria-invalid="Boolean(fieldErrors.subject)"
                :aria-describedby="fieldErrors.subject ? 'contact-subject-error' : undefined"
              />
              <p
                v-if="fieldErrors.subject"
                id="contact-subject-error"
                class="error-message"
                role="alert"
              >
                {{ fieldErrors.subject }}
              </p>
            </div>

            <div class="grid gap-1.5">
              <label class="text-[11px] font-bold text-[#24372b]" for="contact-message">
                お問い合わせ内容
                <span class="rounded bg-[#d84444] px-1 py-px text-[9px] text-white">必須</span>
              </label>
              <textarea
                id="contact-message"
                v-model="form.message"
                class="min-h-[100px] resize-y rounded-[5px] border border-[#dce5dc] bg-white px-2.5 py-2 text-[12px] outline-none placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                :class="{ '!border-[#d84444]': fieldErrors.message }"
                maxlength="5000"
                placeholder="内容を入力してください。"
                :aria-invalid="Boolean(fieldErrors.message)"
                :aria-describedby="fieldErrors.message ? 'contact-message-error' : undefined"
              />
              <p
                v-if="fieldErrors.message"
                id="contact-message-error"
                class="error-message"
                role="alert"
              >
                {{ fieldErrors.message }}
              </p>
            </div>

            <p v-if="fieldErrors.form" class="error-message text-center" role="alert">
              {{ fieldErrors.form }}
            </p>

            <UiButton
              class="!min-h-[35px] !w-full !rounded-[4px] !text-[12px]"
              type="submit"
              :disabled="isSubmitting"
            >
              <span v-if="isSubmitting">送信中...</span>
              <span v-else>送信する</span>
            </UiButton>
          </form>
        </UiCard>
      </div>

      <BuyerBottomNavigation active="profile" />
    </section>
  </main>
</template>

<style scoped>
.error-message {
  margin: 0;
  font-size: 11px;
  font-weight: 500;
  color: #b33a2b;
}
</style>
