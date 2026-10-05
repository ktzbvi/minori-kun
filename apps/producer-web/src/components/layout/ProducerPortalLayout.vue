<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { ChevronDown, ClipboardList, Home, Leaf, Menu, UserRound, WalletCards } from 'lucide-vue-next'
import { useCurrentSessionQuery } from '@/services/auth/auth.query'

const props = defineProps<{
  activeRoute?: '/dashboard' | '/products' | '/orders' | '/sales'
  title?: string
}>()

const route = useRoute()
const sessionQuery = useCurrentSessionQuery()
const producerName = computed(() => sessionQuery.data.value?.display_name || '生産者')
const activeRoute = computed(() => props.activeRoute ?? route.meta.activeRoute ?? '/dashboard')
const title = computed(() => props.title ?? route.meta.portalTitle ?? '')

const navigationItems = computed(() => [
  { label: 'ダッシュボード', shortLabel: 'ホーム', to: '/dashboard', icon: Home, active: activeRoute.value === '/dashboard' },
  { label: '商品管理', shortLabel: '商品', to: '/products', icon: Menu, active: activeRoute.value === '/products' },
  { label: '注文管理', shortLabel: '注文', to: '/orders', icon: ClipboardList, active: activeRoute.value === '/orders' },
  { label: '売上・振込', shortLabel: '売上・振込', to: '/sales', icon: WalletCards, active: activeRoute.value === '/sales' },
])
</script>

<template>
  <div class="min-h-svh bg-[#f4f7f4] text-[#1d2b24] lg:grid lg:grid-cols-[clamp(240px,16vw,280px)_minmax(0,1fr)]">
    <aside class="hidden bg-[#123f2d] lg:block lg:px-5 lg:py-4" aria-label="生産者ポータル主要ナビゲーション">
      <RouterLink
        to="/dashboard"
        class="flex h-[76px] items-center gap-3 rounded-[14px] bg-white px-6 text-[#1b7a49] shadow-sm focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-white"
        aria-label="みのりくん ダッシュボード"
      >
        <span class="grid size-12 shrink-0 place-items-center rounded-full bg-[#e8f5ed]" aria-hidden="true">
          <Leaf class="size-8 rounded-full bg-[#1b7a49] p-2 text-white" :stroke-width="2.5" />
        </span>
        <span class="text-2xl font-extrabold">みのりくん</span>
      </RouterLink>

      <nav class="mt-6 grid gap-2">
        <RouterLink
          v-for="item in navigationItems"
          :key="item.label"
          :to="item.to"
          class="flex min-h-14 items-center gap-4 rounded-xl px-4 text-lg font-bold text-white/75 transition-colors hover:bg-white/8 hover:text-white focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-white"
          :class="item.active ? 'bg-[#2a6849] text-white' : ''"
          :aria-current="item.active ? 'page' : undefined"
        >
          <component :is="item.icon" class="size-6" aria-hidden="true" :stroke-width="2.4" />
          <span>{{ item.label }}</span>
        </RouterLink>
      </nav>
    </aside>

    <div class="min-w-0 pb-[calc(88px+env(safe-area-inset-bottom))] lg:pb-0">
      <header class="flex min-h-18 items-center justify-between gap-3 border-b border-[#d8e2da] bg-white px-4 py-3 sm:px-6 lg:min-h-[clamp(72px,5vw,82px)] lg:px-[clamp(24px,3vw,40px)] lg:py-0">
        <RouterLink
          to="/dashboard"
          class="flex shrink-0 items-center gap-2 text-[#1b7a49] focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b] lg:hidden"
          aria-label="みのりくん ダッシュボード"
        >
          <span class="grid size-10 shrink-0 place-items-center rounded-full bg-[#e8f5ed]" aria-hidden="true">
            <Leaf class="size-7 rounded-full bg-[#1b7a49] p-1.5 text-white" :stroke-width="2.5" />
          </span>
          <span class="text-xl font-extrabold whitespace-nowrap sm:text-2xl">みのりくん</span>
        </RouterLink>

        <div class="hidden lg:block">
          <p class="text-sm font-bold text-[#79887f]">生産者ポータル</p>
          <p class="mt-0.5 text-xl font-extrabold text-[#1d2b24]">{{ title }}</p>
        </div>

        <button
          type="button"
          class="ml-auto flex min-h-11 min-w-0 max-w-[45%] items-center gap-2 rounded-xl border border-[#d7e3da] bg-[#f8fbf8] px-2 text-sm font-extrabold text-[#1d2b24] shadow-sm transition-colors hover:bg-white focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b] sm:px-3 sm:text-base lg:min-h-[52px] lg:min-w-[230px] lg:max-w-none lg:gap-3 lg:rounded-[14px] lg:px-4 lg:text-lg"
          aria-haspopup="menu"
        >
          <span class="grid size-7 shrink-0 place-items-center rounded-full bg-[#e8f5ed] lg:size-9" aria-hidden="true">
            <UserRound class="size-5 text-[#1b7a49] lg:size-6" :stroke-width="2.4" />
          </span>
          <span class="min-w-0 max-w-[180px] truncate">{{ producerName }}</span>
          <ChevronDown class="ml-auto size-4 shrink-0 text-[#687a70] lg:size-5" aria-hidden="true" :stroke-width="3" />
        </button>
      </header>

      <main class="mx-auto w-full min-w-0 max-w-[1440px] px-4 py-6 sm:px-6 sm:py-8 lg:px-[clamp(24px,3vw,40px)] lg:py-[clamp(24px,2.5vw,32px)]">
        <slot :producer-name="producerName">
          <RouterView />
        </slot>
      </main>

      <nav class="fixed inset-x-0 bottom-0 z-20 grid grid-cols-4 border-t border-[#cfddd4] bg-white px-2 pt-2 pb-[calc(8px+env(safe-area-inset-bottom))] lg:hidden" aria-label="生産者ポータル主要ナビゲーション">
        <RouterLink
          v-for="item in navigationItems"
          :key="item.label"
          :to="item.to"
          class="grid min-h-14 min-w-0 place-items-center gap-1 rounded-xl text-xs font-bold text-[#68766e] focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-[#237f4b]"
          :class="item.active ? 'text-[#137c49]' : ''"
          :aria-current="item.active ? 'page' : undefined"
        >
          <component :is="item.icon" class="size-6" aria-hidden="true" :stroke-width="2.4" />
          <span class="whitespace-nowrap">{{ item.shortLabel }}</span>
        </RouterLink>
      </nav>
    </div>
  </div>
</template>
