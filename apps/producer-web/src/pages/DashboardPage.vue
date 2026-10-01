<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { AlertCircle, ChartNoAxesColumn, ChevronRight, Package, ReceiptText, RefreshCw } from 'lucide-vue-next'
import { UiButton, UiCard } from '@minorikun/ui'
import { currentSessionQuery } from '@/services/auth/auth.query'
import { producerDashboardQuery } from '@/services/dashboard/dashboard.query'

const sessionQuery = useQuery(currentSessionQuery)
const dashboardQuery = useQuery(producerDashboardQuery)
const producerName = computed(() => sessionQuery.data.value?.display_name || '生産者')
const dashboard = computed(() => dashboardQuery.data.value?.data)

const numberFormatter = new Intl.NumberFormat('ja-JP')
const currencyFormatter = new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY', maximumFractionDigits: 0 })
const dateTimeFormatter = new Intl.DateTimeFormat('ja-JP', { month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit' })
const dateFormatter = new Intl.DateTimeFormat('ja-JP', { year: 'numeric', month: 'numeric', day: 'numeric' })

function formatCount(value: number | string | null | undefined) {
  return numberFormatter.format(Number(value ?? 0))
}

const summaryCards = computed(() => [
  {
    label: '登録商品',
    value: `${formatCount(dashboard.value?.products.total)}件`,
    meta: `公開中 ${formatCount(dashboard.value?.products.published)}件`,
    action: '商品を管理',
    to: '/products',
    icon: Package,
    accent: 'text-[#16804d]',
    bg: 'bg-[#e6f5eb]',
  },
  {
    label: '対応が必要な注文',
    value: `${formatCount(dashboard.value?.orders.requiring_action)}件`,
    meta: `受付 ${formatCount(dashboard.value?.orders.received)}件・対応中 ${formatCount(dashboard.value?.orders.processing)}件`,
    action: '注文を確認',
    to: '/orders',
    icon: ReceiptText,
    accent: 'text-[#b97412]',
    bg: 'bg-[#fff0d7]',
  },
  {
    label: '今月の売上',
    value: currencyFormatter.format(dashboard.value?.sales.total_yen ?? 0),
    meta: dashboard.value?.sales.period_label ?? '',
    action: '売上・振込を確認',
    to: '/sales',
    icon: ChartNoAxesColumn,
    accent: 'text-[#2b70bd]',
    bg: 'bg-[#e8f1ff]',
  },
])

function formatOrderedAt(value: string | null) {
  return value ? dateTimeFormatter.format(new Date(value)) : '日時未設定'
}

function formatDueOn(value: string | null | undefined) {
  return value ? `振込予定日：${dateFormatter.format(new Date(value))}` : '振込予定日未定'
}

function payoutStateLabel(state: string) {
  return { scheduled: '振込予定', carry_forward: '繰越', processing: '処理中', failed: '要確認' }[state] ?? '確認中'
}

function orderTone(state: string) {
  return state === 'received' ? 'bg-[#ffefd4] text-[#9c5a13]' : 'bg-[#e0f3e7] text-[#137c49]'
}

function fulfillmentLabel(state: string) {
  if (state === 'received') return '受付'
  if (state === 'processing') return '対応中'
  if (state === 'shipped') return '発送済み'
  return '確認中'
}
</script>

<template>
  <section>
    <div>
      <h1 class="text-[50px] leading-tight font-extrabold text-[#17241d] lg:text-[48px]">ダッシュボード</h1>
      <p class="mt-2 text-[26px] font-medium text-[#66766e] lg:text-2xl">
        {{ producerName }}の販売状況を確認できます。
      </p>
    </div>

    <UiCard v-if="dashboardQuery.isError.value" class="mt-9 rounded-[18px] border-[#d9b7aa] bg-[#fff7f3] p-7 shadow-sm">
      <div class="flex flex-wrap items-center gap-4">
        <AlertCircle class="size-8 text-[#b34d2e]" aria-hidden="true" :stroke-width="2.5" />
        <p class="text-2xl font-bold text-[#70351f]">ダッシュボードを読み込めませんでした。</p>
        <UiButton class="ml-auto" variant="outline" type="button" @click="dashboardQuery.refetch()">
          <RefreshCw class="mr-2 size-5" aria-hidden="true" />
          再読み込み
        </UiButton>
      </div>
    </UiCard>

    <section class="mt-9 grid grid-cols-2 gap-6 lg:grid-cols-3 lg:gap-7" aria-label="運用サマリー">
      <RouterLink
        v-for="card in summaryCards"
        :key="card.label"
        :to="card.to"
        class="group min-w-0 rounded-[20px] border border-[#d6e2da] bg-white p-7 shadow-sm transition-colors hover:border-[#a9c8b6] focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b] lg:p-7"
        :class="card.label === '今月の売上' ? 'col-span-2 lg:col-span-1' : ''"
        :aria-busy="dashboardQuery.isLoading.value"
      >
        <div class="grid grid-cols-[auto_minmax(0,1fr)] gap-4">
          <span class="grid size-[56px] shrink-0 place-items-center rounded-full lg:size-[62px]" :class="[card.bg, card.accent]" aria-hidden="true">
            <component :is="card.icon" class="size-7" :stroke-width="2.7" />
          </span>
          <div class="min-w-0">
            <p class="text-[24px] font-medium text-[#68766e] lg:text-2xl">{{ card.label }}</p>
            <p class="mt-2 text-[50px] leading-none font-extrabold tracking-normal text-[#17241d] lg:text-[46px]">
              {{ dashboardQuery.isLoading.value ? '読み込み中' : card.value }}
            </p>
            <p class="mt-4 text-[22px] font-medium text-[#68766e] lg:text-xl">{{ card.meta }}</p>
            <p class="mt-6 hidden items-center gap-1 text-2xl font-extrabold text-[#137c49] group-hover:text-[#0d6339] lg:flex">
              {{ card.action }} <span aria-hidden="true">→</span>
            </p>
          </div>
        </div>
      </RouterLink>
    </section>

    <div class="mt-9 grid gap-7 lg:grid-cols-[minmax(0,2fr)_minmax(360px,1fr)]">
      <section>
        <div class="mb-4 flex items-center justify-between lg:hidden">
          <h2 class="text-[36px] font-extrabold text-[#17241d]">対応が必要な注文</h2>
          <RouterLink to="/orders" class="text-[22px] font-extrabold text-[#137c49] focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b]">
            一覧を見る →
          </RouterLink>
        </div>

        <UiCard class="rounded-[20px] border-[#d6e2da] bg-white p-8 shadow-sm lg:min-h-[602px] lg:p-9">
          <header class="hidden items-center justify-between border-b border-[#d9e3dc] pb-5 lg:flex">
            <h2 class="text-[30px] font-extrabold text-[#17241d]">対応が必要な注文</h2>
            <RouterLink to="/orders" class="text-2xl font-extrabold text-[#137c49] focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b]">
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

          <div v-if="dashboardQuery.isLoading.value" class="grid min-h-[280px] place-items-center text-2xl font-bold text-[#68766e]">
            読み込み中
          </div>
          <div v-else-if="!dashboard?.action_orders.length" class="grid min-h-[280px] place-items-center text-center">
            <div>
              <p class="text-[28px] font-extrabold text-[#17241d]">対応が必要な注文はありません。</p>
              <p class="mt-3 text-xl font-medium text-[#68766e]">受付・対応中の注文が入るとここに表示されます。</p>
            </div>
          </div>
          <ul v-else class="divide-y divide-[#d9e3dc] lg:mt-3" aria-label="対応が必要な注文">
            <li v-for="order in dashboard.action_orders" :key="order.id">
              <RouterLink
                :to="`/orders/${order.id}`"
                class="grid min-h-[140px] grid-cols-[minmax(0,1fr)_auto_24px] items-center gap-4 py-7 focus-visible:outline-3 focus-visible:outline-offset-[-3px] focus-visible:outline-[#237f4b] lg:min-h-[98px] lg:grid-cols-[1fr_.95fr_1.1fr_.7fr_32px] lg:gap-6 lg:py-5"
              >
                <div class="min-w-0">
                  <p class="text-[26px] font-extrabold text-[#1d2b24] lg:text-2xl">{{ order.display_id }}</p>
                  <p class="mt-3 truncate text-[24px] font-medium text-[#68766e] lg:hidden">{{ order.product_summary }}</p>
                </div>
                <p class="hidden text-2xl font-medium text-[#1d2b24] lg:block">{{ formatOrderedAt(order.ordered_at) }}</p>
                <p class="hidden truncate text-2xl font-medium text-[#1d2b24] lg:block">{{ order.product_summary }}</p>
                <span class="inline-flex min-w-[116px] items-center justify-center rounded-full px-5 py-2.5 text-[22px] font-extrabold lg:min-w-[116px] lg:text-xl" :class="orderTone(order.fulfillment_state)">
                  {{ fulfillmentLabel(order.fulfillment_state) }}
                </span>
                <ChevronRight class="size-7 text-[#137c49]" aria-hidden="true" :stroke-width="3.2" />
              </RouterLink>
            </li>
          </ul>
        </UiCard>
      </section>

      <section>
        <h2 class="mb-4 text-[36px] font-extrabold text-[#17241d] lg:hidden">振込のお知らせ</h2>
        <UiCard class="rounded-[20px] border-[#d6e2da] bg-white p-8 shadow-sm lg:min-h-[602px] lg:p-9">
          <h2 class="hidden text-[30px] font-extrabold text-[#17241d] lg:block">振込のお知らせ</h2>
          <div class="mt-0 grid items-end gap-5 rounded-[14px] bg-[#f1f8f3] p-8 lg:mt-9 lg:block lg:p-8">
            <div>
              <p class="text-[24px] font-medium text-[#68766e] lg:text-xl">
                {{ dashboard?.payout_alert ? payoutStateLabel(dashboard.payout_alert.state) : '振込予定' }}
              </p>
              <p class="mt-5 text-[48px] leading-none font-extrabold tracking-normal text-[#17241d] lg:text-[40px]">
                {{ currencyFormatter.format(dashboard?.payout_alert?.expected_payout_yen ?? 0) }}
              </p>
              <p class="mt-6 text-[22px] font-medium text-[#68766e] lg:text-xl">
                {{ formatDueOn(dashboard?.payout_alert?.due_on) }}
              </p>
            </div>
            <RouterLink to="/sales" class="inline-flex min-h-[74px] items-center justify-center rounded-[14px] border border-[#16804d] px-6 text-[23px] font-extrabold text-[#137c49] hover:bg-white focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b] lg:mt-9 lg:w-full">
              売上・振込を確認 <span aria-hidden="true">→</span>
            </RouterLink>
          </div>
        </UiCard>
      </section>
    </div>
  </section>
</template>
