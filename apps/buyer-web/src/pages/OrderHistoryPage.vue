<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ChevronDown, ChevronLeft, ChevronRight, Search, ShoppingCart, X } from 'lucide-vue-next'
import BuyerPageShell from '@/components/BuyerPageShell.vue'
import { useBuyerOrdersQuery } from '@/services/orders/orders.query'
import { orderStatusLabel, paymentStatusLabel, refundStatusLabel } from '@/services/orders/orders.status'

const router = useRouter()
const filterOpen = ref(false)
const filters = reactive({
  period: 'all',
  from: '',
  to: '',
  year: String(new Date().getFullYear()),
})
const applied = reactive({ ...filters })
const queryFilters = computed(() =>
  Object.fromEntries(Object.entries(applied).filter(([, value]) => value)),
)
const orders = useBuyerOrdersQuery(queryFilters)

function applyFilters() {
  if (filters.period === 'custom' && (!filters.from || !filters.to || filters.from > filters.to))
    return
  Object.assign(applied, filters)
  filterOpen.value = false
}
function openFilters() {
  Object.assign(filters, applied)
  filterOpen.value = true
}
function closeFilters() {
  Object.assign(filters, applied)
  filterOpen.value = false
}
function resetFilters() {
  Object.assign(filters, {
    period: 'all',
    from: '',
    to: '',
    year: String(new Date().getFullYear()),
  })
}
function openSearch() {
  void router.push({ name: 'search' })
}
function openCart() {
  void router.push({ name: 'cart' })
}

function yen(value: number) {
  return `${value.toLocaleString('ja-JP')}円`
}
function date(value: string) {
  return new Date(value).toLocaleString('ja-JP', { dateStyle: 'medium' })
}
</script>

<template>
  <BuyerPageShell active="profile">
    <header
      class="flex h-[60px] shrink-0 items-center justify-between border-b border-[#e3e9e3] bg-white px-3"
    >
      <div class="flex items-center gap-2">
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="戻る"
          @click="router.push({ name: 'my-page' })"
        >
          <ChevronLeft :size="22" />
        </button>
        <h1 class="m-0 text-base font-bold text-[#237d4a]">注文履歴</h1>
      </div>
      <div class="flex gap-2">
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#627469]"
          type="button"
          aria-label="検索"
          @click="openSearch"
        >
          <Search :size="21" />
        </button>
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#627469]"
          type="button"
          aria-label="カート"
          @click="openCart"
        >
          <ShoppingCart :size="21" />
        </button>
      </div>
    </header>
    <section class="min-h-0 flex-1 space-y-3 overflow-y-auto px-3 py-3 pb-[78px]">
      <button
        class="flex min-h-9 w-full items-center justify-between rounded-lg border border-[#dce5dc] bg-white px-3 text-left text-xs text-[#526258]"
        type="button"
        aria-haspopup="dialog"
        :aria-expanded="filterOpen"
        @click="openFilters"
      >
        <span>絞り込み</span>
        <ChevronDown :size="15" />
      </button>
      <p v-if="orders.isLoading.value" class="py-8 text-center text-xs text-[#68786e]">
        注文履歴を読み込んでいます...
      </p>
      <p v-else-if="orders.isError.value" class="py-8 text-center text-xs text-[#b33a2b]">
        注文履歴を読み込めませんでした。
      </p>
      <p v-else-if="!orders.data.value?.length" class="py-8 text-center text-xs text-[#68786e]">
        該当する注文はありません。
      </p>
      <button
        v-for="order in orders.data.value"
        :key="order.id"
        class="block w-full rounded-md border border-[#dce5dc] bg-white p-3 text-left"
        type="button"
        @click="router.push({ name: 'order-detail', params: { orderId: order.id } })"
      >
        <span class="flex items-start justify-between gap-2">
          <span>
            <strong class="block text-xs">{{ order.shop_name }}</strong>
            <span class="mt-1 block text-[10px] text-[#68786e]">
              注文番号 {{ order.order_number }}
            </span>
            <span class="block text-[10px] text-[#849188]">{{ date(order.placed_at) }}</span>
          </span>
          <ChevronRight :size="18" class="shrink-0" />
        </span>
        <span
          v-for="item in order.items.slice(0, 2)"
          :key="item.product_name"
          class="mt-2 flex items-center gap-2 border-t border-[#e5ebe5] pt-2"
        >
          <img
            v-if="item.image_url"
            :src="item.image_url"
            class="size-9 rounded object-cover"
            alt=""
          />
          <span v-else class="size-9 rounded bg-[#edf2ed]" />
          <span class="min-w-0 flex-1 truncate text-[10px]">
            {{ item.product_name }} × {{ item.quantity }}
          </span>
        </span>
        <span
          v-if="order.items.length > 2"
          class="mt-1 block text-right text-[10px] text-[#68786e]"
        >
          ほか{{ order.items.length - 2 }}点
        </span>
        <span
          class="mt-2 flex flex-wrap items-center justify-between gap-2 border-t border-[#e5ebe5] pt-2 text-xs"
        >
          <strong>合計 {{ yen(order.total_yen) }}</strong>
          <span class="flex flex-wrap justify-end gap-1">
            <span class="rounded bg-[#edf3f8] px-2 py-1 text-[10px]">
              注文：{{ orderStatusLabel(order.order_state) }}
            </span>
            <span class="rounded bg-[#e9f5ee] px-2 py-1 text-[10px]">
              支払：{{ paymentStatusLabel(order.payment_state) }}
            </span>
            <span
              v-if="refundStatusLabel(order.refund_state)"
              class="rounded bg-[#fff4e5] px-2 py-1 text-[10px]"
            >
              返金：{{ refundStatusLabel(order.refund_state) }}
            </span>
          </span>
        </span>
      </button>
    </section>
    <div
      v-if="filterOpen"
      class="absolute inset-0 z-20 flex items-end justify-center bg-black/35"
      role="presentation"
      @click.self="closeFilters"
    >
      <section
        class="flex max-h-[82%] w-full flex-col rounded-t-[20px] bg-white px-4 pb-4 pt-2 shadow-xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="order-filter-title"
      >
        <div class="mx-auto mb-3 h-1 w-11 rounded-full bg-[#d2d9d4]" />
        <div class="mb-4 flex items-center justify-between">
          <h2 id="order-filter-title" class="m-0 text-base font-bold">絞り込み</h2>
          <button
            class="grid size-8 place-items-center border-0 bg-transparent text-[#66766d]"
            type="button"
            aria-label="閉じる"
            @click="closeFilters"
          >
            <X :size="21" />
          </button>
        </div>
        <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="applyFilters">
          <fieldset class="m-0 min-h-0 flex-1 overflow-y-auto border-0 p-0">
            <legend class="mb-2 text-xs font-bold">注文期間</legend>
            <label
              v-for="option in [
                { value: 'all', label: 'すべての期間' },
                { value: '30d', label: '過去30日' },
                { value: '90d', label: '過去90日' },
                { value: '12m', label: '過去12か月' },
                { value: 'year', label: '年を選択' },
                { value: 'custom', label: '期間を指定' },
              ]"
              :key="option.value"
              class="flex min-h-8 cursor-pointer items-center gap-2.5 text-[11px]"
            >
              <input
                v-model="filters.period"
                class="size-3.5 accent-[#237f4b]"
                type="radio"
                name="order-period"
                :value="option.value"
              />
              {{ option.label }}
            </label>
            <div v-if="filters.period === 'year'" class="mt-2 pl-6">
              <label for="order-year" class="mb-1 block text-[10px] text-[#68786e]">年</label>
              <input
                id="order-year"
                v-model="filters.year"
                class="min-h-9 w-full rounded-md border border-[#dce5dc] px-2 text-xs"
                type="number"
                min="2000"
                max="2100"
              />
            </div>
            <div v-if="filters.period === 'custom'" class="mt-2 grid grid-cols-2 gap-3">
              <label class="text-[10px]">
                開始日
                <input
                  v-model="filters.from"
                  class="mt-1 min-h-9 w-full rounded-lg border border-[#dce5dc] px-2 text-xs"
                  type="date"
                />
              </label>
              <label class="text-[10px]">
                終了日
                <input
                  v-model="filters.to"
                  class="mt-1 min-h-9 w-full rounded-lg border border-[#dce5dc] px-2 text-xs"
                  type="date"
                />
              </label>
              <p
                v-if="filters.from && filters.to && filters.from > filters.to"
                class="col-span-2 m-0 text-[10px] text-[#c8322a]"
              >
                開始日は終了日より前の日付を選択してください。
              </p>
            </div>
          </fieldset>
          <div class="grid grid-cols-2 gap-3 pt-3">
            <button
              class="min-h-[38px] rounded-lg border border-[#237f4b] bg-white text-xs font-bold text-[#237f4b]"
              type="button"
              @click="resetFilters"
            >
              リセット
            </button>
            <button
              class="min-h-[38px] rounded-lg border-0 bg-[#237f4b] text-xs font-bold text-white disabled:opacity-50"
              type="submit"
              :disabled="filters.period === 'custom' && (!filters.from || !filters.to || filters.from > filters.to)"
            >
              適用する
            </button>
          </div>
        </form>
      </section>
    </div>
  </BuyerPageShell>
</template>
