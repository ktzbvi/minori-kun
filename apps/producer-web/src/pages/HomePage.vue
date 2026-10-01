<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import {
  ChartNoAxesColumn,
  ChevronDown,
  ChevronRight,
  ClipboardList,
  Home,
  Leaf,
  Menu,
  Package,
  ReceiptText,
  UserRound,
  WalletCards,
} from 'lucide-vue-next'
import { UiCard } from '@minorikun/ui'
import { currentSessionQuery } from '@/services/auth/auth.query'

const sessionQuery = useQuery(currentSessionQuery)

const producerName = computed(() => sessionQuery.data.value?.display_name || '山田農園')

const summaryCards = [
  {
    label: '登録商品',
    value: '12件',
    meta: '公開中 10件',
    action: '商品を管理',
    to: '/products',
    icon: Package,
    accent: 'text-[#16804d]',
    bg: 'bg-[#e6f5eb]',
  },
  {
    label: '対応が必要な注文',
    value: '3件',
    meta: '受付 1件・対応中 2件',
    action: '注文を確認',
    to: '/orders',
    icon: ReceiptText,
    accent: 'text-[#b97412]',
    bg: 'bg-[#fff0d7]',
  },
  {
    label: '今月の売上',
    value: '¥128,400',
    meta: '2026年8月',
    action: '売上・振込を確認',
    to: '/sales',
    icon: ChartNoAxesColumn,
    accent: 'text-[#2b70bd]',
    bg: 'bg-[#e8f1ff]',
  },
] as const

const actionOrders = [
  { id: '#P-0828-001', date: '8/28 10:32', product: '季節の野菜セット', status: '受付', tone: 'amber' },
  { id: '#P-0827-014', date: '8/27 16:18', product: '高崎トマト', status: '対応中', tone: 'green' },
  { id: '#P-0827-008', date: '8/27 11:05', product: '新米 5kg', status: '対応中', tone: 'green' },
] as const

const navigationItems = [
  { label: 'ダッシュボード', shortLabel: 'ホーム', to: '/', icon: Home, active: true },
  { label: '商品管理', shortLabel: '商品', to: '/products', icon: Menu, active: false },
  { label: '注文管理', shortLabel: '注文', to: '/orders', icon: ClipboardList, active: false },
  { label: '売上・振込', shortLabel: '売上・振込', to: '/sales', icon: WalletCards, active: false },
] as const
</script>

<template>
  <div class="min-h-svh bg-[#f4f7f4] text-[#1d2b24] lg:grid lg:grid-cols-[368px_minmax(0,1fr)]">
    <aside class="hidden bg-[#123f2d] px-6 py-3.5 lg:block" aria-label="生産者ポータル主要ナビゲーション">
      <RouterLink
        to="/"
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
      <header
        class="flex min-h-[110px] items-center justify-between border-b border-[#d8e2da] bg-white px-10 lg:px-[clamp(48px,3.9vw,64px)]"
      >
        <RouterLink
          to="/"
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
          <p class="mt-1 text-2xl font-extrabold text-[#1d2b24]">ダッシュボード</p>
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
        <section>
          <h1 class="text-[50px] leading-tight font-extrabold text-[#17241d] lg:text-[48px]">ダッシュボード</h1>
          <p class="mt-2 text-[26px] font-medium text-[#66766e] lg:text-2xl">
            {{ producerName }}の販売状況を確認できます。
          </p>
        </section>

        <section class="mt-9 grid grid-cols-2 gap-6 lg:grid-cols-3 lg:gap-7" aria-label="運用サマリー">
          <RouterLink
            v-for="card in summaryCards"
            :key="card.label"
            :to="card.to"
            class="group min-w-0 rounded-[20px] border border-[#d6e2da] bg-white p-7 shadow-sm transition-colors hover:border-[#a9c8b6] focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b] lg:p-7"
            :class="card.label === '今月の売上' ? 'col-span-2 lg:col-span-1' : ''"
          >
            <div
              class="grid gap-4"
              :class="card.label === '今月の売上' ? 'grid-cols-[auto_minmax(0,1fr)_auto] items-center lg:grid-cols-[auto_minmax(0,1fr)] lg:items-start' : 'grid-cols-[auto_minmax(0,1fr)]'"
            >
              <span
                class="grid size-[56px] shrink-0 place-items-center rounded-full lg:size-[62px]"
                :class="[card.bg, card.accent]"
                aria-hidden="true"
              >
                <component :is="card.icon" class="size-7" :stroke-width="2.7" />
              </span>
              <div class="min-w-0">
                <p class="text-[24px] font-medium text-[#68766e] lg:text-2xl">{{ card.label }}</p>
                <p class="mt-2 text-[50px] leading-none font-extrabold tracking-normal text-[#17241d] lg:text-[46px]">
                  {{ card.value }}
                </p>
                <p class="mt-4 text-[22px] font-medium text-[#68766e] lg:text-xl">{{ card.meta }}</p>
                <p
                  class="mt-6 hidden items-center gap-1 text-2xl font-extrabold text-[#137c49] group-hover:text-[#0d6339] lg:flex"
                >
                  {{ card.action }} <span aria-hidden="true">→</span>
                </p>
              </div>
              <p
                v-if="card.label === '今月の売上'"
                class="hidden shrink-0 items-center gap-1 text-[24px] font-extrabold text-[#137c49] group-hover:text-[#0d6339] max-lg:flex"
              >
                {{ card.action }} <span aria-hidden="true">→</span>
              </p>
            </div>
          </RouterLink>
        </section>

        <div class="mt-9 grid gap-7 lg:grid-cols-[minmax(0,2fr)_minmax(360px,1fr)]">
          <section>
            <div class="mb-4 flex items-center justify-between lg:hidden">
              <h2 class="text-[36px] font-extrabold text-[#17241d]">対応が必要な注文</h2>
              <RouterLink
                to="/orders"
                class="text-[22px] font-extrabold text-[#137c49] focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b]"
              >
                一覧を見る →
              </RouterLink>
            </div>

            <UiCard class="rounded-[20px] border-[#d6e2da] bg-white p-8 shadow-sm lg:min-h-[602px] lg:p-9">
              <header class="hidden items-center justify-between border-b border-[#d9e3dc] pb-5 lg:flex">
                <h2 class="text-[30px] font-extrabold text-[#17241d]">対応が必要な注文</h2>
                <RouterLink
                  to="/orders"
                  class="text-2xl font-extrabold text-[#137c49] focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b]"
                >
                  注文一覧を見る →
                </RouterLink>
              </header>

              <div class="hidden grid-cols-[1fr_.95fr_1.1fr_.7fr_32px] gap-6 pt-7 text-xl font-medium text-[#7a8880] lg:grid">
                <span>注文番号</span>
                <span>注文日時</span>
                <span>商品</span>
                <span>状態</span>
                <span class="sr-only">詳細</span>
              </div>

              <ul class="divide-y divide-[#d9e3dc] lg:mt-3" aria-label="対応が必要な注文">
                <li v-for="(order, index) in actionOrders" :key="order.id" :class="index === 2 ? 'hidden lg:block' : ''">
                  <RouterLink
                    :to="`/orders/${order.id.replace('#', '')}`"
                    class="grid min-h-[140px] grid-cols-[minmax(0,1fr)_auto_24px] items-center gap-4 py-7 focus-visible:outline-3 focus-visible:outline-offset-[-3px] focus-visible:outline-[#237f4b] lg:min-h-[98px] lg:grid-cols-[1fr_.95fr_1.1fr_.7fr_32px] lg:gap-6 lg:py-5"
                  >
                    <div class="min-w-0">
                      <p class="text-[26px] font-extrabold text-[#1d2b24] lg:text-2xl">{{ order.id }}</p>
                      <p class="mt-3 truncate text-[24px] font-medium text-[#68766e] lg:hidden">{{ order.product }}</p>
                    </div>
                    <p class="hidden text-2xl font-medium text-[#1d2b24] lg:block">{{ order.date }}</p>
                    <p class="hidden truncate text-2xl font-medium text-[#1d2b24] lg:block">{{ order.product }}</p>
                    <span
                      class="inline-flex min-w-[116px] items-center justify-center rounded-full px-5 py-2.5 text-[22px] font-extrabold lg:min-w-[116px] lg:text-xl"
                      :class="order.tone === 'amber' ? 'bg-[#ffefd4] text-[#9c5a13]' : 'bg-[#e0f3e7] text-[#137c49]'"
                    >
                      {{ order.status }}
                    </span>
                    <ChevronRight class="size-7 text-[#137c49]" aria-hidden="true" :stroke-width="3.2" />
                  </RouterLink>
                </li>
              </ul>

              <p class="mt-10 hidden text-xl font-medium text-[#7a8880] lg:block">
                注文を選択すると、注文詳細を確認できます。
              </p>
            </UiCard>
          </section>

          <section>
            <h2 class="mb-4 text-[36px] font-extrabold text-[#17241d] lg:hidden">振込のお知らせ</h2>
            <UiCard class="rounded-[20px] border-[#d6e2da] bg-white p-8 shadow-sm lg:min-h-[602px] lg:p-9">
              <h2 class="hidden text-[30px] font-extrabold text-[#17241d] lg:block">振込のお知らせ</h2>
              <div class="mt-0 grid items-end gap-5 rounded-[14px] bg-[#f1f8f3] p-8 lg:mt-9 lg:block lg:p-8">
                <div>
                  <p class="text-[24px] font-medium text-[#68766e] lg:text-xl">次回振込予定額</p>
                  <p class="mt-5 text-[48px] leading-none font-extrabold tracking-normal text-[#17241d] lg:text-[40px]">
                    ¥103,720
                  </p>
                  <p class="mt-6 text-[22px] font-medium text-[#68766e] lg:text-xl">振込予定日：2026年9月30日</p>
                </div>
                <RouterLink
                  to="/sales"
                  class="inline-flex min-h-[74px] items-center justify-center rounded-[14px] border border-[#16804d] px-6 text-[23px] font-extrabold text-[#137c49] hover:bg-white focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b] lg:mt-9 lg:w-full"
                >
                  売上・振込を確認<span aria-hidden="true">→</span>
                </RouterLink>
              </div>
              <div class="mt-10 hidden border-t border-[#d9e3dc] pt-7 text-xl leading-[1.9] font-medium text-[#68766e] lg:block">
                <p>振込内容は確定後に更新されます。</p>
                <p>詳細は売上・振込画面で確認できます。</p>
              </div>
            </UiCard>
          </section>
        </div>
      </main>

      <nav
        class="fixed inset-x-0 bottom-0 z-20 grid grid-cols-4 border-t border-[#cfddd4] bg-white px-4 py-5 lg:hidden"
        aria-label="生産者ポータル主要ナビゲーション"
      >
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
