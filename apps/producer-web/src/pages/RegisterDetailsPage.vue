<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { onBeforeRouteLeave, RouterLink, useRouter } from 'vue-router'
import { Camera, CircleCheck, Eye, EyeOff, Image as ImageIcon } from 'lucide-vue-next'
import { z } from 'zod'
import { UiButton, UiCheckbox, UiDialog, UiFormLabel, UiFormMessage, UiInput } from '@minorikun/ui'
import RegistrationBrand from '@/components/registration/RegistrationBrand.vue'
import RegistrationProgress from '@/components/registration/RegistrationProgress.vue'
import RegistrationShell from '@/components/registration/RegistrationShell.vue'
import { getApiErrorPayload } from '@/lib/api-error'
import { useProducerRegistration } from '@/composables/useProducerRegistration'
import { queryClient } from '@/lib/query'
import { currentSessionQueryOptions } from '@/services/auth/auth.query'
import {
  producerTermsQueryOptions,
  useProducerRegistrationDetailsQuery,
  useProducerTermsQuery,
} from '@/services/registration/registration.query'
import { producerRegistrationKeys } from '@/services/registration/registration.key'
import { useProducerRegistrationStore } from '@/stores/producerRegistration'
import type { ProducerRegistrationPhoto } from '@/types/registration'

const router = useRouter()
const registrationActions = useProducerRegistration()
const registration = useProducerRegistrationStore()
const shopName = ref(registration.shopName)
const contactName = ref(registration.contactName)
const phone = ref(registration.phone)
const detailsQuery = useProducerRegistrationDetailsQuery()
const termsQuery = useProducerTermsQuery(false)
const photoInputId = 'shop-photo-input'
const photoFile = ref<File | null>(null)
const localPreview = ref('')
const photoError = ref('')
const photoValidating = ref(false)
const photoUploadError = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const passwordVisible = ref(false)
const confirmationVisible = ref(false)
const acceptedTerms = ref(false)
const acceptedTermsVersion = ref('')
const termsDialogOpen = ref(false)
const feedback = ref('')
const termsError = ref('')
const errors = ref<Record<string, string>>({})
const isSubmitting = ref(false)

watch(shopName, (value) => { registration.shopName = value })
watch(contactName, (value) => { registration.contactName = value })
watch(phone, (value) => { registration.phone = value })

const verifiedEmail = computed(() => detailsQuery.data.value?.email ?? '')
const confirmedPhoto = computed<ProducerRegistrationPhoto | null>(() => detailsQuery.data.value?.photo ?? null)
const photoBusy = computed(() => photoValidating.value || registrationActions.isUploadingPhoto.value)
const photoChoicePending = computed(() => photoBusy.value || Boolean(photoFile.value))
const terms = computed(() => termsQuery.data.value ?? detailsQuery.data.value?.terms)
const displayedPhotoUrl = computed(() => localPreview.value || confirmedPhoto.value?.preview_url || '')

const passwordSchema = z.string().superRefine((value, context) => {
  const length = Array.from(value).length
  if (length < 8 || length > 64) {
    context.addIssue({ code: z.ZodIssueCode.custom, message: '8〜64文字で入力してください。' })
  }
  const hasUpper = /[A-Z]/.test(value)
  const hasLower = /[a-z]/.test(value)
  const hasDigit = /[0-9]/.test(value)
  const hasSpecial = /[^\p{L}\p{N}\s]/u.test(value)
  if (!hasUpper || !hasLower || !hasDigit || !hasSpecial) {
    context.addIssue({ code: z.ZodIssueCode.custom, message: '大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。' })
  }
})

const completionSchema = z.object({
  shopName: z.string().trim().min(1, 'ショップ名・農園名を入力してください。').max(255, '255文字以内で入力してください。'),
  contactName: z.string().trim().min(1, '担当者名を入力してください。').max(255, '255文字以内で入力してください。'),
  phone: z.string().trim().min(1, '電話番号を入力してください。').max(32, '32文字以内で入力してください。'),
  password: passwordSchema,
  passwordConfirmation: z.string(),
  acceptedTerms: z.literal(true, { errorMap: () => ({ message: '生産者利用規約への同意が必要です。' }) }),
}).superRefine((value, context) => {
  if (value.password !== value.passwordConfirmation) {
    context.addIssue({ code: z.ZodIssueCode.custom, path: ['passwordConfirmation'], message: 'パスワードが一致しません。' })
  }
})

const canSubmit = computed(() => {
  if (!confirmedPhoto.value || photoChoicePending.value || detailsQuery.isPending.value) return false

  return shopName.value.trim() !== ''
    && contactName.value.trim() !== ''
    && phone.value.trim() !== ''
    && password.value !== ''
    && passwordConfirmation.value !== ''
    && acceptedTerms.value
    && acceptedTermsVersion.value === terms.value?.version
})

watch(() => terms.value?.version, (version) => {
  if (version && acceptedTermsVersion.value && version !== acceptedTermsVersion.value) {
    acceptedTerms.value = false
    acceptedTermsVersion.value = ''
    errors.value.acceptedTerms = '規約が更新されました。内容を確認して、もう一度同意してください。'
  }
})

function showTermsDialog() {
  termsDialogOpen.value = true
}

async function reviewTerms() {
  termsError.value = ''
  showTermsDialog()
  const result = await termsQuery.refetch()
  if (result.error) termsError.value = registrationActions.errorMessage(result.error)
}

function closeTermsDialog() {
  termsDialogOpen.value = false
}

function setTermsConsent(value: boolean) {
  acceptedTerms.value = value
  acceptedTermsVersion.value = value ? terms.value?.version ?? '' : ''
  if (value) delete errors.value.acceptedTerms
}

async function readImageDimensions(file: File): Promise<{ width: number; height: number }> {
  const source = URL.createObjectURL(file)
  try {
    return await new Promise((resolve, reject) => {
      const image = new Image()
      image.onload = () => resolve({ width: image.naturalWidth, height: image.naturalHeight })
      image.onerror = () => reject(new Error('invalid-image'))
      image.src = source
    })
  } finally {
    URL.revokeObjectURL(source)
  }
}

function revokeLocalPreview() {
  if (localPreview.value) URL.revokeObjectURL(localPreview.value)
  localPreview.value = ''
}

async function uploadSelectedPhoto(file: File) {
  photoUploadError.value = ''
  try {
    await registrationActions.uploadPhoto(file)
    photoFile.value = null
    revokeLocalPreview()
    registrationActions.resetPhotoState()
    photoError.value = ''
  } catch (error) {
    const code = getApiErrorPayload(error)?.code
    photoUploadError.value = registrationActions.errorMessage(error)
    if (code === 'PHOTO_INVALID') {
      photoFile.value = null
      revokeLocalPreview()
    }
    registrationActions.resetPhotoState()
  }
}

async function selectPhoto(event: Event) {
  if (photoBusy.value) return
  const input = event.target as HTMLInputElement
  const file = input.files?.[0] ?? null
  input.value = ''
  if (!file) return

  photoError.value = ''
  photoUploadError.value = ''
  photoValidating.value = true
  try {
    const supportedTypes = ['image/jpeg', 'image/png', 'image/webp']
    if (!supportedTypes.includes(file.type)) throw new Error('unsupported-type')
    if (file.size > 5 * 1024 * 1024) throw new Error('file-too-large')
    const dimensions = await readImageDimensions(file)
    if (dimensions.width < 1 || dimensions.height < 1 || dimensions.width > 4096 || dimensions.height > 4096) {
      throw new Error('dimensions')
    }
    photoFile.value = file
    revokeLocalPreview()
    localPreview.value = URL.createObjectURL(file)
  } catch {
    photoFile.value = null
    revokeLocalPreview()
    photoError.value = 'JPEG・PNG・WebP形式で、5MiB以下、縦横4096ピクセル以下の写真を選んでください。'
  } finally {
    photoValidating.value = false
  }
  if (photoFile.value === file) await uploadSelectedPhoto(file)
}

async function retryPhotoUpload() {
  if (photoFile.value && !photoBusy.value) await uploadSelectedPhoto(photoFile.value)
}

function removePhoto() {
  if (photoBusy.value) return
  if (!photoFile.value) return
  photoFile.value = null
  revokeLocalPreview()
  photoUploadError.value = ''
}

function triggerPhotoPicker() {
  if (!photoBusy.value) document.getElementById(photoInputId)?.click()
}

function setServerErrors(serverErrors: Record<string, string[]>): boolean {
  const fieldMap: Record<string, string> = {
    shop_name: 'shopName', contact_name: 'contactName', phone: 'phone',
    password: 'password', password_confirmation: 'passwordConfirmation',
    photo_id: 'photo', accepted_terms: 'acceptedTerms', terms_version: 'acceptedTerms',
  }
  let mapped = false
  for (const [field, messages] of Object.entries(serverErrors)) {
    const formField = fieldMap[field]
    if (formField && messages[0]) {
      errors.value[formField] = messages[0]
      mapped = true
    }
  }
  return mapped
}

async function handleTermsChanged(message = '') {
  acceptedTerms.value = false
  acceptedTermsVersion.value = ''
  if (message) errors.value.acceptedTerms = message
  await reviewTerms()
}

async function recoverExpiredGrant() {
  await queryClient.invalidateQueries({ queryKey: producerRegistrationKeys.status() })
  await router.replace({ name: 'register' })
}

async function submit() {
  if (isSubmitting.value || photoChoicePending.value) return
  errors.value = {}
  feedback.value = ''
  if (!confirmedPhoto.value) errors.value.photo = 'ショッププロフィール写真を追加してください。'

  const result = completionSchema.safeParse({
    shopName: shopName.value,
    contactName: contactName.value,
    phone: phone.value,
    password: password.value,
    passwordConfirmation: passwordConfirmation.value,
    acceptedTerms: acceptedTerms.value && acceptedTermsVersion.value === terms.value?.version,
  })
  if (!result.success) {
    for (const issue of result.error.issues) {
      const field = String(issue.path[0])
      if (!errors.value[field]) errors.value[field] = issue.message
    }
  }
  if (!confirmedPhoto.value || !result.success) return

  isSubmitting.value = true
  try {
    const latestTerms = await queryClient.fetchQuery(producerTermsQueryOptions())
    if (latestTerms.version !== acceptedTermsVersion.value) {
      await handleTermsChanged()
      return
    }
    try {
      await registrationActions.completeRegistration({
        shop_name: result.data.shopName,
        contact_name: result.data.contactName,
        phone: result.data.phone,
        password: result.data.password,
        password_confirmation: result.data.passwordConfirmation,
        photo_id: confirmedPhoto.value.id,
        terms_version: latestTerms.version,
        accepted_terms: true,
      })
    } catch (error) {
      const payload = getApiErrorPayload(error)
      if (payload?.code === 'TERMS_CHANGED') {
        await handleTermsChanged(registrationActions.errorMessage(error))
        return
      }
      if (payload?.code === 'TERMS_UNAVAILABLE') {
        errors.value.acceptedTerms = registrationActions.errorMessage(error)
        return
      }
      if (payload?.errors && Object.keys(payload.errors).length > 0) {
        if (!setServerErrors(payload.errors)) feedback.value = registrationActions.errorMessage(error)
        return
      }
      if (payload?.code === 'VALIDATION_ERROR') {
        feedback.value = registrationActions.errorMessage(error)
        return
      }
      if (['REGISTRATION_EXPIRED', 'REGISTRATION_REQUIRED', 'REGISTRATION_UNVERIFIED'].includes(payload?.code ?? '')) {
        await recoverExpiredGrant()
        return
      }
      if (payload?.code === 'ACCOUNT_EXISTS') {
        feedback.value = registrationActions.errorMessage(error)
        return
      }
      if (payload?.code === 'PHOTO_INVALID' || payload?.code === 'PHOTO_UNAVAILABLE') {
        errors.value.photo = registrationActions.errorMessage(error)
        return
      }

      try {
        const session = await queryClient.fetchQuery(currentSessionQueryOptions())
        if (session.role === 'producer') {
          await router.replace({ name: session.producer?.eligible_to_sell ? 'dashboard' : 'onboarding' })
          return
        }
      } catch {
        // Recover through login when the completion response and /me are both unavailable.
      }
      await router.replace({ name: 'login', query: { recovery: 'registration' } })
    }
  } catch (error) {
    errors.value.acceptedTerms = registrationActions.errorMessage(error)
  } finally {
    password.value = ''
    passwordConfirmation.value = ''
    registrationActions.resetCompletionState()
    isSubmitting.value = false
  }
}

async function retryDetails() {
  await detailsQuery.refetch()
}

onBeforeRouteLeave(() => {
  password.value = ''
  passwordConfirmation.value = ''
  registrationActions.resetCompletionState()
  registrationActions.resetPhotoState()
  revokeLocalPreview()
  photoFile.value = null
})

onBeforeUnmount(() => {
  revokeLocalPreview()
})
</script>

<template>
  <RegistrationShell compact-mobile>
    <div class="w-full min-w-0 max-w-[724px] [overflow-wrap:anywhere] min-[761px]:max-w-[640px] min-[761px]:py-2">
      <RegistrationProgress :current-step="3" class="mb-7 px-1 min-[761px]:mb-4 min-[761px]:px-0 max-[760px]:mb-5" />

      <div class="w-full rounded-[28px] bg-white px-6 py-9 shadow-[0_18px_50px_rgb(37_91_61/10%)] min-[761px]:rounded-none min-[761px]:bg-transparent min-[761px]:p-0 min-[761px]:shadow-none max-[760px]:rounded-2xl max-[760px]:px-5 max-[760px]:py-6">
        <RegistrationBrand />
        <h1 class="mt-8 text-xl font-bold text-[#1e2923] min-[761px]:mt-0 min-[761px]:text-2xl max-[760px]:mt-5">アカウントを作成</h1>

        <p v-if="detailsQuery.isPending.value" class="mt-4 text-sm leading-relaxed text-[#687b70]" role="status">
          登録情報を確認しています…
        </p>
        <div v-else-if="detailsQuery.isError.value" class="mt-7 rounded-xl border border-[#e4c9c3] bg-white p-5 max-[760px]:mt-5">
          <p class="text-sm leading-relaxed text-[#7b3329]" role="alert">{{ registrationActions.errorMessage(detailsQuery.error.value) }}</p>
          <div class="mt-4 flex flex-wrap gap-3">
            <UiButton variant="outline" :disabled="detailsQuery.isFetching.value" @click="retryDetails">{{ detailsQuery.isFetching.value ? '確認中…' : '再試行' }}</UiButton>
            <UiButton variant="ghost" @click="recoverExpiredGrant">メール確認に戻る</UiButton>
          </div>
        </div>

        <form v-if="!detailsQuery.isError.value" class="mt-7 grid gap-5 min-[761px]:mt-5 min-[761px]:gap-4 max-[760px]:mt-5" novalidate :aria-busy="isSubmitting || photoBusy || detailsQuery.isPending.value" @submit.prevent="submit">
          <section class="grid gap-4" aria-labelledby="producer-information-title">
            <h2 id="producer-information-title" class="text-xl font-bold text-[#25332b]">生産者情報</h2>

            <div class="grid gap-2">
              <UiFormLabel id="shop-photo-label" class="text-sm font-bold text-[#173b2c] min-[761px]:font-medium min-[761px]:text-[#687b70]">
                ショッププロフィール写真 <span class="ml-1 text-[#b74646]">*</span>
              </UiFormLabel>
              <button
                type="button"
                class="relative size-28 overflow-visible rounded-full text-[#718178] outline-none focus-visible:ring-3 focus-visible:ring-[#237f4b]/20 disabled:cursor-not-allowed disabled:opacity-60 max-[760px]:size-24"
                :aria-label="displayedPhotoUrl ? 'ショッププロフィール写真を変更' : 'ショッププロフィール写真を追加'"
                :aria-describedby="photoError ? 'shop-photo-error' : undefined"
                :disabled="photoBusy || isSubmitting || detailsQuery.isPending.value"
                @click="triggerPhotoPicker"
              >
                <span class="absolute inset-0 grid place-items-center overflow-hidden rounded-full border-[3px] border-[#d5e4db] bg-white">
                  <img v-if="displayedPhotoUrl" :src="displayedPhotoUrl" alt="選択したショッププロフィール写真" class="absolute inset-0 h-full w-full rounded-full object-cover" />
                  <ImageIcon v-else class="size-14" :stroke-width="1.7" aria-hidden="true" />
                </span>
                <span class="absolute -right-1 -bottom-1 z-10 grid size-8 place-items-center rounded-full bg-[#237f4b] text-white ring-2 ring-[#f3f7f4]">
                  <Camera class="size-5" :stroke-width="2.2" aria-hidden="true" />
                </span>
              </button>
              <UiInput
                :id="photoInputId"
                class="sr-only"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                :disabled="photoBusy || isSubmitting || detailsQuery.isPending.value"
                :aria-labelledby="'shop-photo-label'"
                :aria-describedby="photoError ? 'shop-photo-error' : undefined"
                @change="selectPhoto"
              />
              <p v-if="photoBusy" class="text-sm text-[#687b70]" role="status">
                {{ photoValidating ? '写真を確認しています…' : '写真をアップロードしています…' }}
              </p>
              <p v-if="photoFile && confirmedPhoto" class="text-xs leading-relaxed text-[#687b70]" role="status">新しい写真をアップロードしています。完了すると現在の写真と入れ替わります。</p>
              <p class="text-xs leading-relaxed text-[#87968d] min-[761px]:hidden">登録した写真は、購入者向けの商品一覧にショップ画像として表示されます。</p>
              <UiFormMessage v-if="photoError || errors.photo" id="shop-photo-error" role="alert">{{ photoError || errors.photo }}</UiFormMessage>
              <p v-if="photoUploadError" class="text-sm leading-relaxed text-[#7b3329]" role="alert">{{ photoUploadError }}</p>
              <div v-if="photoFile" class="flex flex-wrap gap-3">
                <UiButton type="button" variant="outline" :disabled="photoBusy || isSubmitting || detailsQuery.isPending.value" @click="retryPhotoUpload">写真を再アップロードする</UiButton>
                <UiButton type="button" variant="ghost" :disabled="photoBusy || isSubmitting || detailsQuery.isPending.value" @click="removePhoto">選択した写真を取り消す</UiButton>
              </div>
            </div>

            <div class="grid gap-2">
              <UiFormLabel for="verified-email" class="text-sm font-medium text-[#687b70]">メールアドレス</UiFormLabel>
              <div class="relative">
                <UiInput id="verified-email" :model-value="verifiedEmail" class="h-14 pr-28 text-base shadow-none min-[761px]:h-12 max-[760px]:h-12" readonly />
                <span class="absolute top-1/2 right-4 flex -translate-y-1/2 items-center gap-1 text-sm font-semibold text-[#258451]">
                  <CircleCheck class="size-4" aria-hidden="true" /> 確認済み
                </span>
              </div>
            </div>

            <div class="grid gap-2">
              <UiFormLabel for="shop-name" class="text-sm font-bold text-[#173b2c] min-[761px]:font-medium min-[761px]:text-[#687b70]">
                ショップ名・農園名 <span class="ml-1 text-[#b74646]">*</span>
              </UiFormLabel>
              <UiInput id="shop-name" v-model="shopName" required class="h-14 text-base shadow-none min-[761px]:h-12 max-[760px]:h-12" :disabled="isSubmitting" :aria-invalid="Boolean(errors.shopName)" :aria-describedby="errors.shopName ? 'shop-name-error' : undefined" />
              <UiFormMessage v-if="errors.shopName" id="shop-name-error" role="alert">{{ errors.shopName }}</UiFormMessage>
            </div>

            <div class="grid gap-4 min-[761px]:grid-cols-2">
              <div class="grid gap-2">
                <UiFormLabel for="contact-name" class="text-sm font-bold text-[#173b2c] min-[761px]:font-medium min-[761px]:text-[#687b70]">
                  担当者名 <span class="ml-1 text-[#b74646]">*</span>
                </UiFormLabel>
                <UiInput id="contact-name" v-model="contactName" required class="h-14 text-base shadow-none min-[761px]:h-12 max-[760px]:h-12" :disabled="isSubmitting" :aria-invalid="Boolean(errors.contactName)" :aria-describedby="errors.contactName ? 'contact-name-error' : undefined" />
                <UiFormMessage v-if="errors.contactName" id="contact-name-error" role="alert">{{ errors.contactName }}</UiFormMessage>
              </div>
              <div class="grid gap-2">
                <UiFormLabel for="phone" class="text-sm font-bold text-[#173b2c] min-[761px]:font-medium min-[761px]:text-[#687b70]">
                  電話番号 <span class="ml-1 text-[#b74646]">*</span>
                </UiFormLabel>
                <UiInput id="phone" v-model="phone" type="tel" autocomplete="tel" required class="h-14 text-base shadow-none min-[761px]:h-12 max-[760px]:h-12" :disabled="isSubmitting" :aria-invalid="Boolean(errors.phone)" :aria-describedby="errors.phone ? 'phone-error' : undefined" />
                <UiFormMessage v-if="errors.phone" id="phone-error" role="alert">{{ errors.phone }}</UiFormMessage>
              </div>
            </div>
          </section>

          <section class="mt-1 grid gap-4" aria-labelledby="password-title">
            <div>
              <h2 id="password-title" class="text-xl font-bold text-[#25332b]">パスワード設定</h2>
              <p class="mt-1.5 text-xs leading-relaxed text-[#87968d]">8〜64文字で、大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。</p>
            </div>
            <div class="grid gap-4 min-[761px]:grid-cols-2">
              <div class="grid gap-2">
                <UiFormLabel for="password" class="text-sm font-bold text-[#173b2c] min-[761px]:font-medium min-[761px]:text-[#687b70]">
                  パスワード <span class="ml-1 text-[#b74646]">*</span>
                </UiFormLabel>
                <div class="relative">
                  <UiInput id="password" v-model="password" :type="passwordVisible ? 'text' : 'password'" autocomplete="new-password" required class="h-14 pr-12 text-base shadow-none min-[761px]:h-12 max-[760px]:h-12" :disabled="isSubmitting" :aria-invalid="Boolean(errors.password)" :aria-describedby="errors.password ? 'password-error' : undefined" />
                  <UiButton tabIndex="-1" type="button" variant="ghost" class="absolute top-1/2 right-1 min-h-9 -translate-y-1/2 border-0 p-2 text-[#718178]" :aria-label="passwordVisible ? 'パスワードを隠す' : 'パスワードを表示する'" :disabled="isSubmitting" @click="passwordVisible = !passwordVisible">
                    <EyeOff v-if="passwordVisible" class="size-5" aria-hidden="true" /><Eye v-else class="size-5" aria-hidden="true" />
                  </UiButton>
                </div>
                <UiFormMessage v-if="errors.password" id="password-error" role="alert">{{ errors.password }}</UiFormMessage>
              </div>
              <div class="grid gap-2">
                <UiFormLabel for="password-confirmation" class="text-sm font-bold text-[#173b2c] min-[761px]:font-medium min-[761px]:text-[#687b70]">
                  パスワード（確認） <span class="ml-1 text-[#b74646]">*</span>
                </UiFormLabel>
                <div class="relative">
                  <UiInput id="password-confirmation" v-model="passwordConfirmation" :type="confirmationVisible ? 'text' : 'password'" autocomplete="new-password" required class="h-14 pr-12 text-base shadow-none min-[761px]:h-12 max-[760px]:h-12" :disabled="isSubmitting" :aria-invalid="Boolean(errors.passwordConfirmation)" :aria-describedby="errors.passwordConfirmation ? 'password-confirmation-error' : undefined" />
                  <UiButton tabIndex="-1" type="button" variant="ghost" class="absolute top-1/2 right-1 min-h-9 -translate-y-1/2 border-0 p-2 text-[#718178]" :aria-label="confirmationVisible ? '確認用パスワードを隠す' : '確認用パスワードを表示する'" :disabled="isSubmitting" @click="confirmationVisible = !confirmationVisible">
                    <EyeOff v-if="confirmationVisible" class="size-5" aria-hidden="true" /><Eye v-else class="size-5" aria-hidden="true" />
                  </UiButton>
                </div>
                <UiFormMessage v-if="errors.passwordConfirmation" id="password-confirmation-error" role="alert">{{ errors.passwordConfirmation }}</UiFormMessage>
              </div>
            </div>
          </section>

          <div class="grid gap-2">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
              <UiCheckbox class="cursor-pointer" id="producer-terms" :model-value="acceptedTerms" :disabled="!terms || termsQuery.isFetching.value || isSubmitting" :aria-invalid="Boolean(errors.acceptedTerms)" :aria-describedby="errors.acceptedTerms ? 'accepted-terms-error' : undefined" @update:model-value="setTermsConsent" />
              <label for="producer-terms" class="text-sm text-[#258451]">
                生産者利用規約に同意する <span class="ml-1 text-[#b74646]">*</span>
              </label>
              <UiButton type="button" variant="ghost" class="min-h-0 border-0 p-0 text-sm text-[#258451] underline underline-offset-4" :disabled="isSubmitting" @click="reviewTerms">規約を確認する</UiButton>
            </div>
            <p v-if="terms?.is_sample" class="text-xs text-[#87968d]">現在はサンプル規約を表示しています。正式な規約が公開されたら再確認が必要です。</p>
            <UiFormMessage v-if="errors.acceptedTerms" id="accepted-terms-error" role="alert">{{ errors.acceptedTerms }}</UiFormMessage>
          </div>

          <p v-if="feedback" class="rounded-lg bg-[#fff8e8] px-4 py-3 text-sm leading-relaxed text-[#614c22]" role="alert">{{ feedback }}</p>
          <UiButton type="submit" class="min-h-[58px] w-full rounded-xl text-base min-[761px]:min-h-12 min-[761px]:text-base max-[760px]:min-h-12 max-[760px]:text-base" :disabled="isSubmitting || !canSubmit">
            {{ isSubmitting ? '作成中…' : 'アカウントを作成する' }}
          </UiButton>
          <RouterLink to="/login" class="w-fit text-sm font-medium text-[#258451] underline underline-offset-4">ログイン</RouterLink>
        </form>
      </div>
    </div>

    <UiDialog v-model:open="termsDialogOpen" :title="terms?.title ?? '生産者利用規約'" description="生産者利用規約を確認してください。">
      <p v-if="terms" class="mb-4 text-xs text-[#87968d]">規約バージョン {{ terms.version }}<span v-if="terms.is_sample">（サンプル規約）</span></p>
      <p v-if="termsQuery.isFetching.value" class="text-sm text-[#687b70]" role="status">生産者利用規約を読み込んでいます…</p>
      <div v-else-if="termsError || termsQuery.isError.value" class="grid gap-3">
        <p class="text-sm leading-relaxed text-[#7b3329]" role="alert">{{ termsError || registrationActions.errorMessage(termsQuery.error.value) }}</p>
        <UiButton type="button" variant="outline" class="w-fit" :disabled="termsQuery.isFetching.value" @click="reviewTerms">再試行</UiButton>
      </div>
      <p v-else class="whitespace-pre-wrap text-sm leading-7 text-[#303a34]">{{ terms?.content }}</p>
      <template #footer>
        <UiButton type="button" variant="outline" @click="closeTermsDialog">閉じる</UiButton>
      </template>
    </UiDialog>
  </RegistrationShell>
</template>
