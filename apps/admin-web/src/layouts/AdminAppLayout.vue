<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import type { CurrentSession } from '@minorikun/api-client'
import { useAdminLogoutMutation } from '@/services/auth/auth.mutation'
import { currentSessionQuery } from '@/services/auth/auth.query'
import { queryClient } from '@/lib/query'

const router = useRouter()
const isMenuOpen = ref(false)
const logoutMutation = useAdminLogoutMutation()
const isLoggingOut = logoutMutation.isPending
const session = computed(() =>
  queryClient.getQueryData<CurrentSession>(currentSessionQuery.queryKey),
)
const adminLabel = computed(() => session.value?.display_name || session.value?.email || '管理者')

const menuItems = [
  { label: 'ダッシュボード', current: true },
  { label: '購入者・問い合わせ', current: false },
  { label: '生産者管理', current: false },
  { label: 'カテゴリ管理', current: false },
  { label: 'バナー管理', current: false },
  { label: '商品管理', current: false },
  { label: '注文管理', current: false },
  { label: '売上・会社手数料', current: false },
  { label: '振込管理', current: false },
  { label: '監査ログ', current: false },
]

async function logout() {
  if (isLoggingOut.value) return
  try {
    await logoutMutation.mutateAsync()
  } finally {
    await router.replace('/login')
  }
}
</script>

<template>
  <div class="min-h-screen bg-[var(--color-background)] text-[var(--color-text)]">
    <button
      v-if="isMenuOpen"
      class="fixed inset-0 z-20 hidden border-0 bg-[#0a1c14]/45 max-[900px]:block"
      type="button"
      aria-label="メニューを閉じる"
      @click="isMenuOpen = false"
    />

    <aside
      class="fixed inset-y-0 left-0 z-30 flex w-[264px] flex-col bg-[#123c2b] px-5 pt-7 pb-6 text-white transition-transform duration-200 max-[900px]:w-[min(300px,86vw)]"
      :class="isMenuOpen ? 'max-[900px]:translate-x-0' : 'max-[900px]:-translate-x-full'"
    >
      <div class="grid gap-1 border-b border-white/15 px-3 pb-6">
        <span class="text-2xl font-extrabold tracking-[0.02em]">みのりくん</span>
        <span class="text-xs text-white/70">管理ポータル</span>
      </div>

      <nav class="mt-[22px] grid gap-1.5" aria-label="管理画面メニュー">
        <button
          v-for="item in menuItems"
          :key="item.label"
          class="flex min-h-11 items-center gap-3 rounded-[9px] border-0 px-[13px] text-left text-sm font-bold disabled:cursor-default"
          :class="item.current ? 'bg-[#276a4c] text-white' : 'bg-transparent text-white/75'"
          type="button"
          :disabled="!item.current"
          :aria-current="item.current ? 'page' : undefined"
        >
          <span
            class="size-2 shrink-0 rounded-full border-2 border-current"
            :class="{ 'bg-current': item.current }"
            aria-hidden="true"
          />
          {{ item.label }}
        </button>
      </nav>

      <div
        class="mt-auto grid grid-cols-[40px_1fr] items-center gap-2.5 border-t border-white/15 px-2.5 pt-[18px]"
      >
        <span
          class="grid size-10 place-items-center rounded-full bg-[#e1f1e7] font-extrabold text-[#176d42]"
          aria-hidden="true"
          >管</span
        >
        <span class="grid min-w-0 gap-0.5"
          ><strong>{{ adminLabel }}</strong
          ><small class="text-[11px] text-white/60">管理者アカウント</small></span
        >
        <button
          class="col-span-full min-h-[38px] rounded-lg border border-white/30 bg-transparent text-xs font-bold text-white disabled:cursor-wait disabled:opacity-60"
          type="button"
          :disabled="isLoggingOut"
          @click="logout"
        >
          {{ isLoggingOut ? '処理中…' : 'ログアウト' }}
        </button>
      </div>
    </aside>

    <div class="min-h-screen ml-[264px] max-[900px]:ml-0">
      <header
        class="hidden min-h-16 items-center justify-between border-b border-[var(--color-border)] bg-white px-5 max-[900px]:flex"
      >
        <button
          class="grid size-11 place-content-center gap-[5px] border-0 bg-transparent"
          type="button"
          aria-label="メニューを開く"
          @click="isMenuOpen = true"
        >
          <span class="h-0.5 w-5 rounded-sm bg-[var(--color-text)]" />
          <span class="h-0.5 w-5 rounded-sm bg-[var(--color-text)]" />
          <span class="h-0.5 w-5 rounded-sm bg-[var(--color-text)]" />
        </button>
        <strong>みのりくん</strong>
        <span
          class="grid size-9 place-items-center rounded-full bg-[#e1f1e7] text-xs font-extrabold text-[#176d42]"
          aria-hidden="true"
          >管</span
        >
      </header>
      <slot />
    </div>
  </div>
</template>
