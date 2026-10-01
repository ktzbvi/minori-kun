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
  <div class="min-h-svh bg-[#f4f7f4] text-[#1d2b24] lg:grid lg:grid-cols-[368px_minmax(0,1fr)]">
    <aside class="hidden bg-[#123f2d] px-6 py-3.5 lg:block" aria-label="生産者ポータル主要ナビゲーション">
      <RouterLink
        to="/dashboard"
        class="flex h-[100px] items-center gap-5 rounded-[18px] bg-white px-10 text-[#1b7a49] shadow-sm focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-white"
        aria-label="みのりくん ダッシュボード"
      >
        <span class="grid size-[70px] shrink-0 place-items-center rounded-full bg-[#e8f5ed]" aria-hidden="true">
          <Leaf class="size-[46px] rounded-full bg-[#1b7a49] p-2.5 text-white" :stroke-width="2.5" />
        </span>
        <span class="text-[32px] font-extrabold">みのりくん</span>
      </RouterLink>

      <nav class="mt-8 grid gap-5">
        <RouterLink
          v-for="item in navigationItems"
          :key="item.label"
          :to="item.to"
          class="flex min-h-[74px] items-center gap-5 rounded-[14px] px-5 text-2xl font-bold text-white/75 transition-colors hover:bg-white/8 hover:text-white focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-white"
          :class="item.active ? 'bg-[#2a6849] text-white' : ''"
          :aria-current="item.active ? 'page' : undefined"
        >
          <component :is="item.icon" class="size-8" aria-hidden="true" :stroke-width="2.4" />
          <span>{{ item.label }}</span>
        </RouterLink>
      </nav>
    </aside>

    <div class="min-w-0 pb-[126px] lg:pb-0">
      <header class="flex min-h-[110px] items-center justify-between border-b border-[#d8e2da] bg-white px-10 lg:px-[clamp(48px,3.9vw,64px)]">
        <RouterLink
          to="/dashboard"
          class="flex items-center gap-5 text-[#1b7a49] focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b] lg:hidden"
          aria-label="みのりくん ダッシュボード"
        >
          <span class="grid size-[88px] shrink-0 place-items-center rounded-full bg-[#e8f5ed]" aria-hidden="true">
            <Leaf class="size-[58px] rounded-full bg-[#1b7a49] p-3 text-white" :stroke-width="2.5" />
          </span>
          <span class="text-[40px] font-extrabold">みのりくん</span>
        </RouterLink>

        <div class="hidden lg:block">
          <p class="text-base font-bold text-[#79887f]">生産者ポータル</p>
          <p class="mt-1 text-2xl font-extrabold text-[#1d2b24]">{{ title }}</p>
        </div>

        <button
          type="button"
          class="ml-auto flex min-h-[70px] items-center gap-3 rounded-[18px] border border-[#d7e3da] bg-[#f8fbf8] px-6 text-[26px] font-extrabold text-[#1d2b24] shadow-sm transition-colors hover:bg-white focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b] lg:min-h-[70px] lg:min-w-[306px] lg:px-6 lg:text-2xl"
          aria-haspopup="menu"
        >
          <span class="grid size-[48px] shrink-0 place-items-center rounded-full bg-[#e8f5ed]" aria-hidden="true">
            <UserRound class="size-8 text-[#1b7a49]" :stroke-width="2.4" />
          </span>
          <span class="max-w-[180px] truncate">{{ producerName }}</span>
          <ChevronDown class="ml-auto size-7 text-[#687a70]" aria-hidden="true" :stroke-width="3" />
        </button>
      </header>

      <main class="mx-auto w-full max-w-[1550px] px-10 py-12 lg:px-[clamp(48px,3.9vw,64px)]">
        <slot :producer-name="producerName">
          <RouterView />
        </slot>
      </main>

      <nav class="fixed inset-x-0 bottom-0 z-20 grid grid-cols-4 border-t border-[#cfddd4] bg-white px-4 py-5 lg:hidden" aria-label="生産者ポータル主要ナビゲーション">
        <RouterLink
          v-for="item in navigationItems"
          :key="item.label"
          :to="item.to"
          class="grid min-h-[86px] place-items-center gap-1 rounded-xl text-[18px] font-bold text-[#68766e] focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-[#237f4b]"
          :class="item.active ? 'text-[#137c49]' : ''"
          :aria-current="item.active ? 'page' : undefined"
        >
          <component :is="item.icon" class="size-8" aria-hidden="true" :stroke-width="2.8" />
          <span>{{ item.shortLabel }}</span>
        </RouterLink>
      </nav>
    </div>
  </div>
</template>
