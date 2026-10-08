<script setup lang="ts">
import {
  LogOut,
  Mail,
  PackageOpen,
  Pencil,
  Search,
  ShoppingCart,
  ChevronRight,
  KeyRound,
  LoaderCircle,
} from 'lucide-vue-next'
import { UiButton, UiCard, UiDialog, UiSkeleton } from '@minorikun/ui'
import BuyerLayout from '@/components/layout/BuyerLayout.vue'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useBuyerMyPage } from '@/composables/useBuyerMyPage'
const { router, profile, logoutOpen, loggingOut, openSearch, openCart, confirmLogout } =
  useBuyerMyPage()
</script>
<template>
  <BuyerLayout active="profile">
    <template #mobile-header>
      <header
        class="flex h-[65px] items-center justify-between border-b border-[#e3e9e3] bg-white px-4"
      >
        <h1 class="text-base font-bold text-[#237d4a]">マイページ</h1>
        <div class="flex gap-2">
          <UiButton
            variant="ghost"
            class="size-9 min-h-9 p-0 text-[#627469]"
            aria-label="検索"
            @click="openSearch"
          >
            <Search class="size-5" aria-hidden="true" />
          </UiButton>
          <UiButton
            variant="ghost"
            class="size-9 min-h-9 p-0 text-[#627469]"
            aria-label="カート"
            @click="openCart"
          >
            <ShoppingCart class="size-5" aria-hidden="true" />
          </UiButton>
        </div>
      </header>
    </template>
    <div
      class="flex-1 px-3 pt-4 pb-[calc(88px+env(safe-area-inset-bottom))] lg:mx-auto lg:w-full lg:max-w-2xl lg:overflow-visible lg:px-6 lg:pt-6 lg:pb-10"
    >
      <div class="w-full">
        <UiCard
          class="rounded-lg border-[#dce5dc] bg-white px-3 py-2.5 shadow-none lg:rounded-lg lg:px-4 lg:py-4"
        >
          <template v-if="profile">
            <p class="break-words text-sm font-bold lg:text-xl">{{ profile.name }}</p>
            <p class="mt-1 break-all text-xs text-[#718075] lg:mt-3">
              会員番号: {{ profile.member_id }}
            </p>
          </template>
          <div v-else class="grid gap-2" role="status" aria-label="会員情報を読み込み中">
            <UiSkeleton class="h-5 w-3/4" />
            <UiSkeleton class="h-4 w-1/2" />
          </div>
        </UiCard>
        <div class="w-full">
          <section aria-label="注文関連">
            <h2 class="mb-2 mt-4 text-xs font-medium text-[#718075] lg:mt-6 lg:mb-2 lg:text-sm">
              注文関連
            </h2>
            <UiCard
              class="overflow-hidden rounded-lg border-[#dce5dc] bg-white p-0 shadow-none lg:rounded-xl"
            >
              <UiButton
                variant="ghost"
                class="min-h-11 w-full justify-start gap-2 whitespace-normal rounded-none border-0 border-b border-[#dce5dc] bg-white px-3 py-3 text-left text-xs font-medium text-[#33443a] last:border-b-0 lg:min-h-14 lg:gap-3 lg:px-4 lg:py-3 lg:text-sm"
                @click="router.push({ name: 'order-history' })"
              >
                <span class="text-[#237f4b] lg:shrink-0">
                  <PackageOpen class="size-4 lg:size-5" aria-hidden="true" />
                </span>
                <span class="min-w-0 flex-1">
                  <span class="block">注文履歴</span>
                  <span class="hidden">ご注文の内容と配送状況を確認できます。</span>
                </span>
                <ChevronRight class="size-4 shrink-0 text-[#718075]" aria-hidden="true" />
              </UiButton>
            </UiCard>
          </section>
          <section aria-label="アカウント設定">
            <h2 class="mb-2 mt-4 text-xs font-medium text-[#718075] lg:mt-6 lg:mb-2 lg:text-sm">
              アカウント設定
            </h2>
            <UiCard
              class="overflow-hidden rounded-lg border-[#dce5dc] bg-white p-0 shadow-none lg:rounded-xl"
            >
              <UiButton
                variant="ghost"
                class="min-h-11 w-full justify-start gap-2 whitespace-normal rounded-none border-0 border-b border-[#dce5dc] bg-white px-3 py-3 text-left text-xs font-medium text-[#33443a] last:border-b-0 lg:min-h-14 lg:gap-3 lg:px-4 lg:py-3 lg:text-sm"
                @click="router.push({ name: 'member-info' })"
              >
                <span class="text-[#237f4b] lg:shrink-0">
                  <Pencil class="size-4 lg:size-5" aria-hidden="true" />
                </span>
                <span class="min-w-0 flex-1">
                  <span class="block">会員情報の変更</span>
                  <span class="hidden">お客様情報とお届け先を確認・変更します。</span>
                </span>
                <ChevronRight class="size-4 shrink-0 text-[#718075]" aria-hidden="true" />
              </UiButton>
              <UiButton
                variant="ghost"
                class="min-h-11 w-full justify-start gap-2 whitespace-normal rounded-none border-0 border-b border-[#dce5dc] bg-white px-3 py-3 text-left text-xs font-medium text-[#33443a] last:border-b-0 lg:min-h-14 lg:gap-3 lg:px-4 lg:py-3 lg:text-sm"
                @click="router.push({ name: 'password-change' })"
              >
                <span class="text-[#237f4b] lg:shrink-0">
                  <KeyRound class="size-4 lg:size-5" aria-hidden="true" />
                </span>
                <span class="min-w-0 flex-1">
                  <span class="block">パスワードの変更</span>
                  <span class="hidden">ログイン用のパスワードを変更します。</span>
                </span>
                <ChevronRight class="size-4 shrink-0 text-[#718075]" aria-hidden="true" />
              </UiButton>
            </UiCard>
          </section>
          <section aria-label="その他">
            <h2 class="mb-2 mt-4 text-xs font-medium text-[#718075] lg:mt-6 lg:mb-2 lg:text-sm">
              その他
            </h2>
            <UiCard
              class="overflow-hidden rounded-lg border-[#dce5dc] bg-white p-0 shadow-none lg:rounded-xl"
            >
              <UiButton
                variant="ghost"
                class="min-h-11 w-full justify-start gap-2 whitespace-normal rounded-none border-0 border-b border-[#dce5dc] bg-white px-3 py-3 text-left text-xs font-medium text-[#33443a] last:border-b-0 lg:min-h-14 lg:gap-3 lg:px-4 lg:py-3 lg:text-sm"
                @click="router.push({ name: 'contact' })"
              >
                <span class="text-[#237f4b] lg:shrink-0">
                  <Mail class="size-4 lg:size-5" aria-hidden="true" />
                </span>
                <span class="min-w-0 flex-1">
                  <span class="block">お問い合わせ</span>
                  <span class="hidden">ご不明な点はこちらからお問い合わせください。</span>
                </span>
                <ChevronRight class="size-4 shrink-0 text-[#718075]" aria-hidden="true" />
              </UiButton>
              <UiButton
                variant="ghost"
                class="min-h-11 w-full justify-start gap-2 whitespace-normal rounded-none border-0 border-b border-[#dce5dc] bg-white px-3 py-3 text-left text-xs font-medium text-[#33443a] last:border-b-0 lg:min-h-14 lg:gap-3 lg:px-4 lg:py-3 lg:text-sm"
                @click="logoutOpen = true"
              >
                <span class="text-[#237f4b] lg:shrink-0">
                  <LogOut class="size-4 lg:size-5" aria-hidden="true" />
                </span>
                <span class="min-w-0 flex-1">
                  <span class="block">ログアウト</span>
                  <span class="hidden">現在のアカウントからログアウトします。</span>
                </span>
                <ChevronRight class="size-4 shrink-0 text-[#718075]" aria-hidden="true" />
              </UiButton>
            </UiCard>
          </section>
        </div>
      </div>
    </div>
<template #mobile-navigation>    <div
      class="fixed inset-x-0 bottom-0 z-40 h-[calc(64px+env(safe-area-inset-bottom))] bg-white lg:hidden"
    >
      <div class="relative h-16">
        <BuyerBottomNavigation active="profile" />
      </div>
    </div>
</template>
    <UiDialog
      :open="logoutOpen"
      title="ログアウトしますか？"
      description="現在のアカウントからログアウトします。"
      :show-close="!loggingOut"
      content-class="max-w-sm"
      @update:open="!loggingOut && (logoutOpen = $event)"
    >
      <template #footer>
        <UiButton variant="outline" :disabled="loggingOut" @click="logoutOpen = false">
          キャンセル
        </UiButton>
        <UiButton
          class="border-red-600 bg-red-600 hover:bg-red-700"
          :disabled="loggingOut"
          @click="confirmLogout"
        >
          <LoaderCircle v-if="loggingOut" class="size-4 animate-spin" aria-hidden="true" />
          {{ loggingOut ? 'ログアウト中...' : 'ログアウト' }}
        </UiButton>
      </template>
    </UiDialog>
  </BuyerLayout>
</template>
