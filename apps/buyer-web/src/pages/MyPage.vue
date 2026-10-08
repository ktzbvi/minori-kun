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
} from 'lucide-vue-next'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useBuyerMyPage } from '@/composables/useBuyerMyPage'
const { router, profile, logoutOpen, loggingOut, openSearch, openCart, confirmLogout } =
  useBuyerMyPage()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section
      class="relative flex h-dvh w-full max-w-[375px] flex-col overflow-hidden bg-[#f8faf6] sm:h-[728px] sm:shadow-sm"
    >
      <header
        class="flex h-[65px] shrink-0 items-center justify-between border-b border-[#e3e9e3] bg-white px-4"
      >
        <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">
          マイページ
        </h1>
        <div class="flex gap-2">
          <button
            class="grid size-9 place-items-center border-0 bg-transparent text-[#627469]"
            type="button"
            aria-label="Search"
            @click="openSearch"
          >
            <Search :size="21" />
          </button>
          <button
            class="grid size-9 place-items-center border-0 bg-transparent text-[#627469]"
            type="button"
            aria-label="Cart"
            @click="openCart"
          >
            <ShoppingCart :size="21" />
          </button>
        </div>
      </header>

      <div class="min-h-0 flex-1 overflow-y-auto px-3 pt-4 pb-[76px]">
        <section class="rounded-[8px] border border-[#dce5dc] bg-white px-3 py-2.5">
          <div>
            <p class="m-0 text-[13px] font-bold">{{ profile?.name || '読名' }}</p>
            <p class="m-0 mt-0.5 text-[9px] text-[#718075]">
              会員番号: {{ profile?.member_id || '-' }}
            </p>
          </div>
        </section>

        <p class="mb-1 mt-4 text-[10px] text-[#718075]">注文関連</p>
        <div class="menu-group">
          <button class="menu-row" type="button" @click="router.push({ name: 'order-history' })">
            <PackageOpen :size="15" />
            <span>注文履歴</span>
            <ChevronRight :size="16" />
          </button>
        </div>

        <p class="mb-1 mt-4 text-[10px] text-[#718075]">
          アカウント設定
        </p>
        <div class="menu-group">
          <button class="menu-row" type="button" @click="router.push({ name: 'member-info' })">
            <Pencil :size="15" />
            <span>会員情報の変更</span>
            <ChevronRight :size="16" />
          </button>
          <button class="menu-row" type="button" @click="router.push({ name: 'password-change' })">
            <KeyRound :size="15" />
            <span>パスワードの変更</span>
            <ChevronRight :size="16" />
          </button>
        </div>

        <p class="mb-1 mt-4 text-[10px] text-[#718075]">その他</p>
        <div class="menu-group">
          <button class="menu-row" type="button" @click="router.push({ name: 'contact' })">
            <Mail :size="15" />
            <span>お問い合わせ</span>
            <ChevronRight :size="16" />
          </button>
          <button class="menu-row" type="button" @click="logoutOpen = true">
            <LogOut :size="15" />
            <span>ログアウト</span>
            <ChevronRight :size="16" />
          </button>
        </div>
      </div>

      <BuyerBottomNavigation active="profile" />

      <div
        v-if="logoutOpen"
        class="absolute inset-0 z-10 grid place-items-center bg-black/30 px-7"
        role="presentation"
      >
        <section
          class="w-full rounded-[9px] bg-white p-4 shadow-lg"
          role="dialog"
          aria-modal="true"
          aria-labelledby="logout-title"
        >
          <h2 id="logout-title" class="m-0 text-center text-[14px] font-bold">
            ログアウトしますか？
          </h2>
          <p class="mt-2 mb-4 text-center text-[10px] text-[#718075]">
            現在のアカウントからログアウトします。
          </p>
          <div class="grid grid-cols-2 gap-2">
            <button
              class="modal-button border-[#dce5dc] bg-white text-[#4d6155]"
              type="button"
              :disabled="loggingOut"
              @click="logoutOpen = false"
            >
              キャンセル
            </button>
            <button
              class="modal-button border-[#d94444] bg-[#d94444] text-white"
              type="button"
              :disabled="loggingOut"
              @click="confirmLogout"
            >
              ログアウト
            </button>
          </div>
        </section>
      </div>
    </section>
  </main>
</template>

<style scoped>
.menu-group {
  overflow: hidden;
  border: 1px solid #dce5dc;
  border-radius: 7px;
}

.menu-row {
  display: flex;
  width: 100%;
  min-height: 41px;
  align-items: center;
  gap: 8px;
  border: 0;
  border-bottom: 1px solid #dce5dc;
  background: #fff;
  padding: 0 10px;
  text-align: left;
  font-size: 12px;
  color: #33443a;
}

.menu-row:last-child {
  border-bottom: 0;
}

.menu-row > span {
  flex: 1;
}

.modal-button {
  min-height: 32px;
  border-width: 1px;
  border-radius: 5px;
  font-size: 11px;
  font-weight: 700;
}
</style>
