<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue'
import { onBeforeRouteLeave, RouterLink, useRouter } from 'vue-router'
import { Camera, ChevronRight, Landmark, LoaderCircle, LockKeyhole, LogOut, UserRound } from 'lucide-vue-next'
import { z } from 'zod'
import { isAxiosError } from 'axios'
import { UiButton, UiCard, UiDialog, UiFormMessage, UiSkeleton } from '@minorikun/ui'
import { useProducerAccountQuery } from '@/services/account/account.query'
import { useUpdateShopPhotoMutation } from '@/services/account/account.mutation'
import { useProducerLogoutMutation } from '@/services/auth/auth.mutation'
import { getApiErrorMessage } from '@/lib/api-error'

const router = useRouter()
const accountQuery = useProducerAccountQuery()
const account = computed(() => accountQuery.data.value)
const photoMutation = useUpdateShopPhotoMutation()
const logoutMutation = useProducerLogoutMutation()
const photoInput = ref<globalThis.HTMLInputElement | null>(null)
const candidate = ref<globalThis.File | null>(null)
const preview = ref('')
const expectedPhotoId = ref<string | null>(null)
const photoError = ref('')
const feedback = ref('')
const imageFailed = ref(false)
const logoutOpen = ref(false)
const logoutError = ref('')
const initial = computed(() => Array.from(account.value?.farm_name.trim() || '生産者')[0])
const photoSchema = z.instanceof(globalThis.File)
  .refine(file => ['image/jpeg', 'image/png', 'image/webp'].includes(file.type), 'JPEG・PNG・WebPの写真を選択してください。')
  .refine(file => file.size > 0 && file.size <= 5 * 1024 * 1024, '写真は5MB以内で選択してください。')
const menuItems = [
  { label: '生産者アカウント情報', to: '/account/profile', icon: UserRound },
  { label: '振込口座情報', to: '/account/bank', icon: Landmark },
  { label: 'パスワード変更', to: '/account/password', icon: LockKeyhole },
]

function clearPreview() {
  if (preview.value) globalThis.URL.revokeObjectURL(preview.value)
  preview.value = ''
  candidate.value = null
  expectedPhotoId.value = null
}
function setPhotoDialogOpen(open: boolean) {
  if (!open && !photoMutation.isPending.value) { clearPreview(); photoError.value = '' }
}
function selectPhoto(event: globalThis.Event) {
  const input = event.target as globalThis.HTMLInputElement
  const file = input.files?.[0]
  input.value = ''
  if (!file) return
  photoError.value = ''; feedback.value = ''
  const result = photoSchema.safeParse(file)
  if (!result.success) { photoError.value = result.error.issues[0]?.message ?? '写真を確認してください。'; return }
  clearPreview()
  candidate.value = file
  expectedPhotoId.value = account.value?.shop_photo_id ?? null
  preview.value = globalThis.URL.createObjectURL(file)
}
async function savePhoto() {
  if (!candidate.value || photoMutation.isPending.value) return
  photoError.value = ''
  try {
    await photoMutation.mutateAsync({ photo: candidate.value, expectedPhotoId: expectedPhotoId.value })
    imageFailed.value = false
    clearPreview()
    feedback.value = 'プロフィール写真を変更しました。'
  } catch (cause) {
    if (isAxiosError(cause) && cause.response?.status === 409) {
      clearPreview()
      await accountQuery.refetch()
    }
    photoError.value = getApiErrorMessage(cause) ?? '写真を保存できませんでした。もう一度お試しください。'
  }
}
async function logout() {
  if (logoutMutation.isPending.value) return
  logoutError.value = ''
  try {
    await logoutMutation.mutateAsync()
    await router.replace({ name: 'login' })
  } catch (cause) {
    logoutError.value = getApiErrorMessage(cause) ?? 'ログアウトできませんでした。もう一度お試しください。'
  }
}
onBeforeUnmount(clearPreview)
onBeforeRouteLeave(() => !photoMutation.isPending.value && !logoutMutation.isPending.value)
</script>

<template>
  <section class="mx-auto min-w-0 max-w-5xl" :aria-busy="accountQuery.isFetching.value">
    <h1 class="text-2xl font-extrabold text-[#17241d] sm:text-3xl">アカウント設定</h1>
    <div v-if="accountQuery.isPending.value" class="mt-6 grid gap-5 lg:grid-cols-[minmax(16rem,.85fr)_minmax(0,1.4fr)] lg:gap-6" role="status">
      <span class="sr-only">アカウント情報を読み込んでいます。</span>
      <UiSkeleton class="h-44 rounded-2xl lg:h-80" aria-hidden="true" />
      <div class="grid gap-5" aria-hidden="true"><UiSkeleton class="h-60 rounded-2xl" /><UiSkeleton class="h-16 rounded-2xl" /></div>
    </div>
    <UiCard v-else-if="accountQuery.isError.value && !account" class="mt-6 grid justify-items-start gap-4 p-6">
      <p class="text-sm" role="alert">アカウント情報を取得できませんでした。もう一度お試しください。</p>
      <UiButton variant="outline" :disabled="accountQuery.isFetching.value" @click="accountQuery.refetch()">再試行</UiButton>
    </UiCard>
    <template v-else-if="account">
      <p v-if="accountQuery.isError.value" class="mt-4 text-sm text-[#a63c2c]" role="alert">最新の情報を取得できませんでした。<UiButton variant="ghost" :disabled="accountQuery.isFetching.value" @click="accountQuery.refetch()">再試行</UiButton></p>
      <div class="mt-6 grid items-start gap-5 sm:gap-6 lg:grid-cols-[minmax(16rem,.85fr)_minmax(0,1.4fr)] lg:gap-6">
        <UiCard class="min-w-0 rounded-2xl border-[#d2e1d8] p-5 shadow-none sm:p-6 lg:p-8">
          <div class="flex min-w-0 items-center gap-4 sm:gap-5 lg:flex-col lg:gap-5 lg:text-center">
            <UiButton type="button" variant="ghost" class="relative size-20 shrink-0 rounded-full bg-[#e8f5ed] p-0 text-[#237f4b] hover:bg-[#dceddf] sm:size-24 lg:size-28" aria-label="プロフィール写真を変更" aria-describedby="shop-photo-guidance" aria-haspopup="dialog" :disabled="photoMutation.isPending.value || logoutMutation.isPending.value" @click="photoInput?.click()">
              <img v-if="account.shop_photo_url && !imageFailed" :src="account.shop_photo_url" alt="" class="size-full rounded-full object-cover" @error="imageFailed = true">
              <span v-else class="text-2xl font-bold sm:text-3xl" aria-hidden="true">{{ initial }}</span>
              <span class="absolute -right-1 -bottom-1 grid size-8 place-items-center rounded-full border-2 border-white bg-[#237f4b] text-white sm:size-9" aria-hidden="true"><Camera class="size-4 sm:size-5" /></span>
            </UiButton>
            <p id="shop-photo-guidance" class="sr-only">JPEG・PNG・WebP、5MB以内、縦横4096ピクセル以内の写真を選択してください。</p>
            <input ref="photoInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" tabindex="-1" aria-hidden="true" @change="selectPhoto">
            <div class="min-w-0">
              <h2 class="break-words text-xl font-bold sm:text-2xl">{{ account.farm_name }}</h2>
              <p class="mt-2 text-sm text-[#687a70]">ログインメール</p>
              <p class="mt-1 break-all text-sm text-[#17241d] sm:text-base">{{ account.masked_email }}</p>
            </div>
          </div>
          <UiFormMessage v-if="photoError && !preview" class="mt-4" role="alert">{{ photoError }}</UiFormMessage>
          <p v-if="feedback" class="mt-4 text-sm text-[#237f4b]" role="status">{{ feedback }}</p>
        </UiCard>
        <div class="grid min-w-0 gap-5 sm:gap-6">
          <UiCard class="min-w-0 rounded-2xl border-[#d2e1d8] px-5 py-0 shadow-none sm:px-6">
            <nav aria-label="アカウント設定メニュー" class="divide-y divide-[#d7e3da]">
              <RouterLink v-for="item in menuItems" :key="item.to" :to="item.to" class="flex min-h-20 min-w-0 items-center gap-4 py-5 text-base font-bold text-[#17241d] transition-colors hover:text-[#237f4b] focus-visible:rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#237f4b] sm:min-h-24 sm:text-lg">
                <component :is="item.icon" class="size-6 shrink-0 text-[#237f4b] sm:size-7" aria-hidden="true" />
                <span class="min-w-0 flex-1 break-words">{{ item.label }}</span>
                <ChevronRight class="size-4 shrink-0 text-[#687a70]" aria-hidden="true" />
              </RouterLink>
            </nav>
          </UiCard>
          <UiButton type="button" variant="outline" class="min-h-16 w-full gap-3 rounded-2xl border-[#d2e1d8] bg-white text-base font-bold text-[#bf4436] shadow-none hover:bg-[#fff5f2] hover:text-[#a63c2c] sm:min-h-18 sm:text-lg" :disabled="photoMutation.isPending.value || logoutMutation.isPending.value" @click="logoutError = ''; logoutOpen = true"><LogOut class="size-5" aria-hidden="true" />ログアウト</UiButton>
        </div>
      </div>
    </template>
    <!-- P11-04 / AT-P-011: local circular preview; persist only after explicit confirmation. -->
    <UiDialog :open="Boolean(preview)" title="プロフィール写真を変更" description="この写真をプロフィールに設定しますか？" content-class="w-[calc(100%_-_3rem)] max-w-lg gap-3 rounded-2xl border-[#d2e1d8] p-6 sm:gap-4 sm:p-8" title-class="pr-0 text-xl font-bold text-[#17241d] sm:text-2xl" description-class="text-sm leading-relaxed text-[#687a70] sm:text-base" footer-class="grid grid-cols-2 gap-4 sm:gap-5" :show-close="false" @update:open="setPhotoDialogOpen">
      <div class="flex min-h-36 items-center justify-center py-6 sm:min-h-44 sm:py-8">
        <img v-if="preview" :src="preview" alt="選択したプロフィール写真のプレビュー" class="size-24 rounded-full border-2 border-[#d2e1d8] bg-[#fff9eb] object-cover sm:size-28">
      </div>
      <UiFormMessage v-if="photoError" class="mb-4" role="alert">{{ photoError }}</UiFormMessage>
      <template #footer>
        <UiButton variant="outline" class="min-h-12 w-full rounded-lg px-2 text-base font-bold sm:px-4" :disabled="photoMutation.isPending.value" @click="setPhotoDialogOpen(false)">キャンセル</UiButton>
        <UiButton class="min-h-12 w-full rounded-lg px-2 text-base font-bold sm:px-4" :disabled="photoMutation.isPending.value" @click="savePhoto"><LoaderCircle v-if="photoMutation.isPending.value" class="size-4 animate-spin" aria-hidden="true" />{{ photoMutation.isPending.value ? '変更中…' : '変更する' }}</UiButton>
      </template>
    </UiDialog>
    <UiDialog :open="logoutOpen" title="ログアウトしますか？" description="生産者ポータルからログアウトします。" @update:open="value => { if (!logoutMutation.isPending.value) logoutOpen = value }">
      <p v-if="logoutError" class="text-sm text-[#a63c2c]" role="alert">{{ logoutError }}</p>
      <template #footer><UiButton variant="outline" :disabled="logoutMutation.isPending.value" @click="logoutOpen = false">キャンセル</UiButton><UiButton :disabled="logoutMutation.isPending.value" @click="logout"><LoaderCircle v-if="logoutMutation.isPending.value" class="size-4 animate-spin" aria-hidden="true" />{{ logoutMutation.isPending.value ? 'ログアウト中…' : 'ログアウト' }}</UiButton></template>
    </UiDialog>
  </section>
</template>