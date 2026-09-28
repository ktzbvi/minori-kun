<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { Camera, CircleCheck, Eye, EyeOff, Image as ImageIcon } from 'lucide-vue-next'
import { z } from 'zod'
import { UiButton, UiCheckbox, UiFormLabel, UiFormMessage, UiInput } from '@minorikun/ui'
import RegistrationProgress from '@/components/registration/RegistrationProgress.vue'
import RegistrationShell from '@/components/registration/RegistrationShell.vue'
import { useProducerRegistrationStore } from '@/stores/producerRegistration'

const registration = useProducerRegistrationStore()
const photoInput = ref<HTMLInputElement | null>(null)
const photo = ref<File | null>(null)
const photoPreview = ref('')
const shopName = ref('')
const contactName = ref('')
const phone = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const acceptedTerms = ref(false)
const passwordVisible = ref(false)
const confirmationVisible = ref(false)
const isSubmitting = ref(false)
const errors = ref<Record<string, string>>({})
const verifiedEmail = computed(() => registration.email || 'name@example.jp')

const detailsSchema = z
  .object({
    shopName: z.string().trim().min(1, 'ショップ名・農園名を入力してください。'),
    contactName: z.string().trim().min(1, '担当者名を入力してください。'),
    phone: z
      .string()
      .trim()
      .min(1, '電話番号を入力してください。')
      .regex(/^[0-9+()-]+$/, '正しい電話番号を入力してください。'),
    password: z
      .string()
      .min(8, '8〜64文字で入力してください。')
      .max(64, '8〜64文字で入力してください。')
      .regex(
        /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).+$/,
        '大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。',
      ),
    passwordConfirmation: z.string(),
    acceptedTerms: z.literal(true, {
      errorMap: () => ({ message: '生産者利用規約への同意が必要です。' }),
    }),
  })
  .refine((data) => data.password === data.passwordConfirmation, {
    path: ['passwordConfirmation'],
    message: 'パスワードが一致しません。',
  })

function selectPhoto(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0] ?? null
  if (!file) return

  if (photoPreview.value) URL.revokeObjectURL(photoPreview.value)
  photo.value = file
  photoPreview.value = URL.createObjectURL(file)
  delete errors.value.photo
}

async function submit() {
  errors.value = {}
  if (!photo.value) errors.value.photo = 'ショッププロフィール写真を追加してください。'

  const result = detailsSchema.safeParse({
    shopName: shopName.value,
    contactName: contactName.value,
    phone: phone.value,
    password: password.value,
    passwordConfirmation: passwordConfirmation.value,
    acceptedTerms: acceptedTerms.value,
  })

  if (!result.success) {
    for (const issue of result.error.issues) {
      errors.value[String(issue.path[0])] = issue.message
    }
  }
  if (!photo.value || !result.success) return

  isSubmitting.value = true
  try {
    await registration.createAccount({
      shopName: result.data.shopName,
      contactName: result.data.contactName,
      phone: result.data.phone,
      password: result.data.password,
      photo: photo.value,
      acceptedTerms: true,
    })
  } finally {
    isSubmitting.value = false
  }
}

onBeforeUnmount(() => {
  if (photoPreview.value) URL.revokeObjectURL(photoPreview.value)
})
</script>

<template>
  <RegistrationShell>
    <div class="w-full max-w-[640px] py-2">
      <RegistrationProgress :current-step="3" class="mb-5" />
      <h1 class="text-[30px] font-bold text-[#1e2923] min-[761px]:text-4xl">アカウントを作成</h1>

      <form class="mt-7 grid gap-5" novalidate @submit.prevent="submit">
        <section class="grid gap-4" aria-labelledby="producer-information-title">
          <h2
            id="producer-information-title"
            class="text-xl font-bold text-[#25332b] min-[761px]:text-2xl"
          >
            生産者情報
          </h2>

          <div class="grid gap-2">
            <UiFormLabel class="text-[15px] text-[#687b70] min-[761px]:text-lg">
              ショッププロフィール写真 <span class="text-[#b74646]">*</span>
            </UiFormLabel>
            <button
              type="button"
              class="relative grid size-28 place-items-center overflow-visible rounded-full border-[3px] border-[#d5e4db] bg-white text-[#718178] outline-none focus-visible:ring-3 focus-visible:ring-[#237f4b]/20"
              :aria-label="photo ? 'ショッププロフィール写真を変更' : 'ショッププロフィール写真を追加'"
              @click="photoInput?.click()"
            >
              <img
                v-if="photoPreview"
                :src="photoPreview"
                alt="選択したショッププロフィール写真"
                class="size-full rounded-full object-cover"
              />
              <ImageIcon v-else class="size-14" :stroke-width="1.7" aria-hidden="true" />
              <span
                class="absolute right-0 bottom-0 grid size-8 place-items-center rounded-full bg-[#237f4b] text-white ring-2 ring-[#f3f7f4]"
              >
                <Camera class="size-5" :stroke-width="2.2" aria-hidden="true" />
              </span>
            </button>
            <input
              ref="photoInput"
              class="sr-only"
              type="file"
              accept="image/*"
              @change="selectPhoto"
            />
            <p class="text-xs leading-relaxed text-[#87968d] min-[761px]:hidden">
              登録した写真は、購入者向けの商品一覧にショップ画像として表示されます。
            </p>
            <UiFormMessage v-if="errors.photo" role="alert">{{ errors.photo }}</UiFormMessage>
          </div>

          <div class="grid gap-2">
            <UiFormLabel for="verified-email" class="text-[15px] text-[#687b70] min-[761px]:text-lg">
              メールアドレス
            </UiFormLabel>
            <div class="relative">
              <UiInput
                id="verified-email"
                :model-value="verifiedEmail"
                class="h-12 pr-28 text-base shadow-none"
                readonly
                disabled
              />
              <span
                class="absolute top-1/2 right-4 flex -translate-y-1/2 items-center gap-1 text-sm font-semibold text-[#258451]"
              >
                <CircleCheck class="size-4" aria-hidden="true" /> 確認済み
              </span>
            </div>
          </div>

          <div class="grid gap-2">
            <UiFormLabel for="shop-name" class="text-[15px] text-[#687b70] min-[761px]:text-lg">
              ショップ名・農園名 <span class="text-[#b74646]">*</span>
            </UiFormLabel>
            <UiInput
              id="shop-name"
              v-model="shopName"
              class="h-12 text-base shadow-none"
              :aria-invalid="Boolean(errors.shopName)"
            />
            <UiFormMessage v-if="errors.shopName" role="alert">{{ errors.shopName }}</UiFormMessage>
          </div>

          <div class="grid gap-4 min-[761px]:grid-cols-2">
            <div class="grid gap-2">
              <UiFormLabel for="contact-name" class="text-[15px] text-[#687b70] min-[761px]:text-lg">
                担当者名 <span class="text-[#b74646]">*</span>
              </UiFormLabel>
              <UiInput
                id="contact-name"
                v-model="contactName"
                class="h-12 text-base shadow-none"
                :aria-invalid="Boolean(errors.contactName)"
              />
              <UiFormMessage v-if="errors.contactName" role="alert">{{ errors.contactName }}</UiFormMessage>
            </div>
            <div class="grid gap-2">
              <UiFormLabel for="phone" class="text-[15px] text-[#687b70] min-[761px]:text-lg">
                電話番号 <span class="text-[#b74646]">*</span>
              </UiFormLabel>
              <UiInput
                id="phone"
                v-model="phone"
                type="tel"
                autocomplete="tel"
                class="h-12 text-base shadow-none"
                :aria-invalid="Boolean(errors.phone)"
              />
              <UiFormMessage v-if="errors.phone" role="alert">{{ errors.phone }}</UiFormMessage>
            </div>
          </div>
        </section>

        <section class="mt-1 grid gap-4" aria-labelledby="password-title">
          <div>
            <h2 id="password-title" class="text-xl font-bold text-[#25332b] min-[761px]:text-2xl">
              パスワード設定
            </h2>
            <p class="mt-1.5 text-xs leading-relaxed text-[#87968d]">
              8〜64文字で、大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。
            </p>
          </div>

          <div class="grid gap-4 min-[761px]:grid-cols-2">
            <div class="grid gap-2">
              <UiFormLabel for="password" class="text-[15px] text-[#687b70] min-[761px]:text-lg">
                パスワード <span class="text-[#b74646]">*</span>
              </UiFormLabel>
              <div class="relative">
                <UiInput
                  id="password"
                  v-model="password"
                  :type="passwordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  class="h-12 pr-12 text-base shadow-none"
                  :aria-invalid="Boolean(errors.password)"
                />
                <UiButton
                  variant="ghost"
                  class="absolute top-1/2 right-1 min-h-9 -translate-y-1/2 border-0 p-2 text-[#718178]"
                  :aria-label="passwordVisible ? 'パスワードを隠す' : 'パスワードを表示する'"
                  @click="passwordVisible = !passwordVisible"
                >
                  <EyeOff v-if="passwordVisible" class="size-5" aria-hidden="true" />
                  <Eye v-else class="size-5" aria-hidden="true" />
                </UiButton>
              </div>
              <UiFormMessage v-if="errors.password" role="alert">{{ errors.password }}</UiFormMessage>
            </div>

            <div class="grid gap-2">
              <UiFormLabel for="password-confirmation" class="text-[15px] text-[#687b70] min-[761px]:text-lg">
                パスワード（確認） <span class="text-[#b74646]">*</span>
              </UiFormLabel>
              <div class="relative">
                <UiInput
                  id="password-confirmation"
                  v-model="passwordConfirmation"
                  :type="confirmationVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  class="h-12 pr-12 text-base shadow-none"
                  :aria-invalid="Boolean(errors.passwordConfirmation)"
                />
                <UiButton
                  variant="ghost"
                  class="absolute top-1/2 right-1 min-h-9 -translate-y-1/2 border-0 p-2 text-[#718178]"
                  :aria-label="confirmationVisible ? '確認用パスワードを隠す' : '確認用パスワードを表示する'"
                  @click="confirmationVisible = !confirmationVisible"
                >
                  <EyeOff v-if="confirmationVisible" class="size-5" aria-hidden="true" />
                  <Eye v-else class="size-5" aria-hidden="true" />
                </UiButton>
              </div>
              <UiFormMessage v-if="errors.passwordConfirmation" role="alert">
                {{ errors.passwordConfirmation }}
              </UiFormMessage>
            </div>
          </div>
        </section>

        <div class="grid gap-2">
          <div class="flex items-center gap-3">
            <UiCheckbox id="producer-terms" v-model="acceptedTerms" />
            <label for="producer-terms" class="text-sm text-[#258451] min-[761px]:text-base">
              <a href="/producer-terms" class="underline underline-offset-4">生産者利用規約に同意する</a>
              <span class="text-[#b74646]"> *</span>
            </label>
          </div>
          <UiFormMessage v-if="errors.acceptedTerms" role="alert">{{ errors.acceptedTerms }}</UiFormMessage>
        </div>

        <UiButton
          type="submit"
          class="min-h-[58px] w-full rounded-xl text-[17px] min-[761px]:text-xl"
          :disabled="isSubmitting || registration.submitted"
        >
          {{ registration.submitted ? '入力内容を確認しました' : isSubmitting ? '作成中…' : 'アカウントを作成する' }}
        </UiButton>
        <RouterLink
          to="/login"
          class="w-fit text-sm font-medium text-[#258451] underline underline-offset-4 min-[761px]:text-base"
        >
          ログイン
        </RouterLink>
      </form>
    </div>
  </RegistrationShell>
</template>
