<script setup lang="ts">
import { ChevronLeft, ChevronRight, Leaf, LoaderCircle, Check, CircleCheck } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { UiButton, UiCard, UiInput, UiFormLabel, UiFormMessage, UiCheckbox } from '@minorikun/ui'
import { useBuyerRegisterDetails } from '@/composables/useBuyerRegisterDetails'
const {
  router,
  email,
  submitting,
  fieldErrors,
  form,
  fieldColumns,
  canSubmit,
  validatePhonetic,
  submit,
} = useBuyerRegisterDetails()
</script>

<template>
  <main
    class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6 lg:bg-[#f3f6f3] lg:p-10"
  >
    <section
      class="flex h-dvh w-full max-w-[375px] flex-col overflow-hidden bg-[#fbfcfa] sm:h-[728px] sm:shadow-sm lg:h-auto lg:max-w-5xl lg:rounded-3xl lg:border lg:border-[#d7e3da] lg:bg-white lg:shadow-xl lg:shadow-[#14382a]/5"
    >
      <div class="flex min-h-0 min-w-0 flex-1 flex-col lg:block">
        <header
          class="flex h-[65px] shrink-0 items-center gap-3 border-b border-[#e1e8e1] px-5 lg:h-20 lg:px-8"
        >
          <RouterLink
            to="/"
            class="mr-auto hidden items-center gap-2 rounded-lg text-[#237f4b] focus-visible:outline-2 focus-visible:outline-offset-4 lg:flex"
          >
            <span class="grid size-9 place-items-center rounded-full bg-[#e8f2eb]">
              <Leaf class="size-5" aria-hidden="true" />
            </span>
            <span class="text-xl font-extrabold tracking-widest">みのりくん</span>
          </RouterLink>
          <UiButton
            variant="ghost"
            class="size-9 min-h-9 rounded-full p-0 text-[#267c4a] lg:w-auto lg:gap-1 lg:px-2 lg:text-sm"
            aria-label="戻る"
            @click="router.back()"
          >
            <ChevronLeft class="size-5" aria-hidden="true" />
            <span class="hidden lg:inline">戻る</span>
          </UiButton>
          <h1 class="text-base font-bold text-[#227644] lg:hidden">新規会員登録</h1>
        </header>
        <ol
          class="flex shrink-0 items-center justify-between gap-1 border-b border-[#e1e8e1] px-4 py-3 text-xs text-[#8a978e] lg:mx-auto lg:w-full lg:max-w-lg lg:border-0 lg:px-8 lg:pt-6 lg:pb-0"
          aria-label="会員登録の進捗"
        >
          <li class="flex items-center gap-1 text-[#237f4b] lg:gap-2">
            <span class="grid place-items-center lg:size-7 lg:rounded-full lg:bg-[#e8f2eb]">
              <Check class="size-3 lg:size-4" aria-hidden="true" />
            </span>
            <span class="sr-only">完了：</span>
            メール入力
          </li>
          <li class="hidden lg:block" aria-hidden="true"><ChevronRight class="size-3" /></li>
          <li class="flex items-center gap-1 text-[#237f4b] lg:gap-2">
            <span class="grid place-items-center lg:size-7 lg:rounded-full lg:bg-[#e8f2eb]">
              <Check class="size-3 lg:size-4" aria-hidden="true" />
            </span>
            <span class="sr-only">完了：</span>
            メール確認
          </li>
          <li class="hidden lg:block" aria-hidden="true"><ChevronRight class="size-3" /></li>
          <li class="font-bold text-[#237f4b]" aria-current="step">
            <span class="lg:hidden">③ 情報入力</span>
            <span class="hidden items-center gap-2 lg:flex">
              <span
                class="grid size-7 place-items-center rounded-full bg-[#237f4b] text-xs text-white"
              >
                3
              </span>
              情報入力
            </span>
          </li>
        </ol>
        <div
          class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-3 scroll-pb-5 lg:overflow-visible lg:px-8 lg:pt-6 lg:pb-0"
        >
          <div class="mb-6 hidden lg:block">
            <p class="text-sm font-medium text-[#237f4b]">STEP 3 / 3</p>
            <h1 class="mt-2 text-2xl font-bold text-[#1f2a24]">お客様情報の入力</h1>
            <p class="mt-3 text-sm leading-6 text-[#687a70]">
              お客様情報とお届け先を入力して、会員登録を完了してください。
            </p>
          </div>
          <UiCard
            class="rounded-[11px] border-[#dbe5dc] bg-white p-3 shadow-none lg:rounded-none lg:border-0 lg:p-0"
          >
            <div class="mb-4 flex items-center justify-center gap-2 lg:hidden">
              <span
                class="grid size-8 place-items-center rounded-full bg-[#e4f3e9] text-[#237d4a]"
                aria-hidden="true"
              >
                <Leaf class="size-5" />
              </span>
              <strong class="text-lg text-[#217848]">みのりくん</strong>
            </div>
            <form
              id="buyer-registration-details"
              class="grid gap-2 lg:grid-cols-2 lg:items-start lg:gap-x-8 lg:gap-y-6"
              :aria-busy="submitting"
              @submit.prevent="submit"
            >
              <div class="grid gap-1 lg:col-span-2 lg:gap-2">
                <span class="text-xs font-bold lg:text-sm">メールアドレス</span>
                <div
                  class="flex min-h-8 flex-wrap items-center justify-between gap-2 rounded-md border border-[#dce5dc] bg-[#f4f7f2] px-2 py-1 lg:min-h-12 lg:px-4 lg:py-3"
                >
                  <span class="min-w-0 break-all text-xs text-[#687a70] lg:text-sm">
                    {{ email }}
                  </span>
                  <span class="flex shrink-0 items-center gap-1 text-xs font-bold text-[#237f4b]">
                    <CircleCheck class="size-4" aria-hidden="true" />
                    確認済み
                  </span>
                </div>
              </div>
              <div
                v-for="(column, columnIndex) in fieldColumns"
                :key="columnIndex"
                class="contents lg:grid lg:min-w-0 lg:gap-6 lg:self-start"
              >
                <fieldset
                  v-for="group in column"
                  :key="group.title"
                  class="min-w-0 lg:rounded-xl lg:border lg:border-[#e1e8e1] lg:p-5"
                >
                  <legend class="hidden text-base font-bold text-[#24372b] lg:block lg:px-1">
                    {{ group.title }}
                  </legend>
                  <div class="grid items-start gap-2 lg:grid-cols-2 lg:gap-x-4 lg:gap-y-4">
                    <div
                      v-for="field in group.fields"
                      :key="field.name"
                      class="grid min-w-0 content-start gap-1 lg:gap-2"
                      :class="{
                        'lg:col-span-2':
                          field.name === 'address_line1' || field.name === 'address_line2',
                      }"
                    >
                      <UiFormLabel
                        class="text-xs leading-normal font-bold lg:text-sm"
                        :for="`registration-${field.name}`"
                      >
                        {{ field.label }}
                        <span
                          v-if="field.required"
                          class="rounded bg-[#d84444] px-1 py-px text-xs text-white"
                        >
                          必須
                        </span>
                      </UiFormLabel>
                      <UiInput
                        :id="`registration-${field.name}`"
                        v-model="form[field.name]"
                        class="h-8 rounded-md border-[#dce5dc] px-2 text-xs lg:h-12 lg:px-3 lg:text-base"
                        :name="
                          field.name === 'name_phonetic' ? 'registration-name-phonetic' : field.name
                        "
                        :autocomplete="field.autocomplete"
                        :inputmode="field.inputmode"
                        :type="field.name.includes('password') ? 'password' : 'text'"
                        :required="field.required"
                        :placeholder="field.placeholder"
                        :disabled="submitting"
                        :aria-describedby="
                          fieldErrors[field.name] ? `registration-${field.name}-error` : undefined
                        "
                        :aria-invalid="Boolean(fieldErrors[field.name])"
                        @blur="field.name === 'name_phonetic' && validatePhonetic()"
                      />
                      <UiFormMessage
                        v-if="fieldErrors[field.name]"
                        :id="`registration-${field.name}-error`"
                        role="alert"
                      >
                        {{ fieldErrors[field.name] }}
                      </UiFormMessage>
                    </div>
                  </div>
                </fieldset>
              </div>
            </form>
          </UiCard>
        </div>
        <footer
          class="shrink-0 border-t border-[#e1e8e1] bg-white px-4 pt-2.5 pb-[max(0.75rem,env(safe-area-inset-bottom))] lg:mx-8 lg:mt-6 lg:grid lg:grid-cols-2 lg:items-center lg:gap-x-8 lg:gap-y-3 lg:px-0 lg:pt-5 lg:pb-6"
        >
          <div class="flex items-center gap-2">
            <UiCheckbox
              id="registration-terms"
              v-model="form.terms_accepted"
              class="size-4 lg:size-5"
              :disabled="submitting"
              :aria-invalid="Boolean(fieldErrors.terms_accepted)"
              :aria-describedby="
                fieldErrors.terms_accepted ? 'registration-terms-error' : undefined
              "
            />
            <UiFormLabel for="registration-terms" class="text-xs leading-6 lg:text-sm">
              利用規約に同意する
            </UiFormLabel>
          </div>
          <UiFormMessage
            v-if="fieldErrors.terms_accepted"
            id="registration-terms-error"
            role="alert"
          >
            {{ fieldErrors.terms_accepted }}
          </UiFormMessage>
          <UiButton
            form="buyer-registration-details"
            class="mt-2 min-h-11 w-full lg:col-start-2 lg:row-start-1 lg:mt-0 lg:min-h-12 lg:text-base"
            type="submit"
            :disabled="!canSubmit"
          >
            <LoaderCircle v-if="submitting" class="size-4 animate-spin" aria-hidden="true" />
            {{ submitting ? '登録中...' : '登録する' }}
          </UiButton>
          <p class="mt-2 text-center text-xs text-[#78867d] lg:col-span-2 lg:mt-0 lg:text-sm">
            すでにアカウントをお持ちの方
            <RouterLink
              to="/login"
              class="font-bold text-[#237f4b] underline underline-offset-2 focus-visible:outline-2 focus-visible:outline-offset-4"
            >
              ログイン
            </RouterLink>
          </p>
        </footer>
      </div>
    </section>
  </main>
</template>
