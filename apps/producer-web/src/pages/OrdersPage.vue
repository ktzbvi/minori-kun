<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { ChevronDown, ChevronLeft, ChevronRight, LoaderCircle, Search } from 'lucide-vue-next'
import { z } from 'zod'
import { UiButton, UiCard, UiDialog, UiRadioGroup, UiFormControl, UiFormItem, UiFormLabel, UiFormMessage, UiInput, UiSelect } from '@minorikun/ui'
import OrderPeriodFields from '@/components/orders/OrderPeriodFields.vue'
import OrderStatusBadge from '@/components/orders/OrderStatusBadge.vue'
import { useProducerOrdersQuery } from '@/services/orders/order.query'
import type { ProducerOrderListFilters } from '@/types/order'

// FR-P-008 / P08-02: drafts only affect the API after Apply (or search submission).
const filterSchema = z.object({
  keyword: z.string().trim().max(255, '検索は255文字以内で入力してください。'),
  status: z.enum(['all', 'received', 'processing', 'shipped', 'cancelled', 'refunded']),
  period: z.enum(['all', '30d', '90d', '12m', 'year', 'custom']),
  year: z.string(), from: z.string(), to: z.string(),
}).superRefine((values, context) => {
  if (values.period === 'year' && (!/^\d{4}$/.test(values.year) || Number(values.year) < 2000 || Number(values.year) > 2100)) {
    context.addIssue({ code: 'custom', path: ['year'], message: '2000年から2100年の暦年を入力してください。' })
  }
  if (values.period === 'custom') {
    for (const field of ['from', 'to'] as const) {
      if (!z.string().date().safeParse(values[field]).success) {
        context.addIssue({ code: 'custom', path: [field], message: '有効な日付を入力してください。' })
      }
    }
    if (values.from && values.to && values.to < values.from) {
      context.addIssue({ code: 'custom', path: ['to'], message: '終了日は開始日以降を選択してください。' })
    }
  }
})
const currentYear = new Intl.DateTimeFormat('en', { year: 'numeric', timeZone: 'Asia/Tokyo' }).format(new Date())
const defaults = () => ({ keyword: '', status: 'all', period: 'all', year: currentYear, from: '', to: '' })
const draft = reactive(defaults())
const errors = ref<Record<string, string | undefined>>({})
const filtersOpen = ref(false)
const applied = ref<ProducerOrderListFilters>({ period: 'all', status: 'all', page: 1 })
const ordersQuery = useProducerOrdersQuery(applied)
const orders = computed(() => ordersQuery.data.value?.data ?? [])
const meta = computed(() => ordersQuery.data.value?.meta)
const isBusy = computed(() => ordersQuery.isFetching.value)
const dateFormatter = new Intl.DateTimeFormat('ja-JP', {
  timeZone: 'Asia/Tokyo', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false,
})
const hasAppliedFilters = computed(() => Boolean(applied.value.keyword)
  || applied.value.period !== 'all' || applied.value.status !== 'all')
const periodOptions = [
  { value: 'all', label: 'すべての期間' }, { value: '30d', label: '過去30日' },
  { value: '90d', label: '過去90日' }, { value: '12m', label: '過去12か月' },
  { value: 'year', label: '年を選択' }, { value: 'custom', label: '期間を指定' },
]

const statusOptions = [
  { value: 'all', label: 'すべて' }, { value: 'received', label: '受付' },
  { value: 'processing', label: '対応中' }, { value: 'shipped', label: '発送済み' },
  { value: 'cancelled', label: 'キャンセル' }, { value: 'refunded', label: '返金済み' },
]
const filterTrigger = ref<{ $el: { focus: () => void } } | null>(null)

function restoreAppliedFilters() {
  Object.assign(draft, {
    status: applied.value.status ?? 'all', period: applied.value.period ?? 'all',
    year: String(applied.value.year ?? currentYear), from: applied.value.from ?? '', to: applied.value.to ?? '',
  })
  errors.value = {}
}

function setFiltersOpen(open: boolean) {
  restoreAppliedFilters()
  filtersOpen.value = open
}

function restoreFilterFocus(event: globalThis.Event) {
  event.preventDefault()
  filterTrigger.value?.$el.focus()
}

function resetMobileDraft() {
  const keyword = draft.keyword
  Object.assign(draft, defaults(), { keyword })
  errors.value = {}
}

function applyFilters(closeSheet = false) {
  const result = filterSchema.safeParse(draft)
  errors.value = {}
  if (!result.success) {
    for (const issue of result.error.issues) errors.value[String(issue.path[0])] ??= issue.message
    return
  }
  const values = result.data
  applied.value = {
    keyword: values.keyword || undefined, status: values.status, period: values.period,
    year: values.period === 'year' ? Number(values.year) : undefined,
    from: values.period === 'custom' ? values.from : undefined,
    to: values.period === 'custom' ? values.to : undefined, page: 1,
  }
  if (closeSheet) filtersOpen.value = false
}

function resetFilters() {
  Object.assign(draft, defaults())
  errors.value = {}
  applied.value = { period: 'all', status: 'all', page: 1 }
}

function changePage(delta: number) {
  const next = (meta.value?.current_page ?? 1) + delta
  if (!isBusy.value && next >= 1 && next <= (meta.value?.last_page ?? 1)) {
    applied.value = { ...applied.value, page: next }
  }
}

function orderedAt(value: string | null) {
  return value ? dateFormatter.format(new Date(value)) : '注文日未設定'
}
</script>

<template>
  <section>
    <h1 class="text-xl font-extrabold text-[#17241d] sm:text-2xl lg:text-3xl">注文一覧</h1>
    <p class="mt-2 text-sm text-[#687a70] lg:text-base">自分の商品を含む注文を確認できます。</p>

    <form class="mt-5 grid gap-3 xl:grid-cols-3 xl:rounded-2xl xl:border xl:border-[#d6e2da] xl:bg-white xl:p-5" novalidate aria-label="注文の検索と絞り込み" @submit.prevent="applyFilters()">
      <UiFormItem class="xl:pt-7">
        <UiFormLabel for="order-search" class="sr-only">注文番号・商品名で検索</UiFormLabel>
        <div class="relative xl:max-w-lg">
          <UiButton type="submit" variant="ghost" class="absolute top-1/2 left-1 z-10 size-9 -translate-y-1/2 p-2 text-[#687a70]" aria-label="注文を検索" :disabled="isBusy">
            <Search class="size-5" aria-hidden="true" />
          </UiButton>
          <UiFormControl>
            <UiInput id="order-search" v-model="draft.keyword" type="search" maxlength="255" class="h-11 rounded-xl bg-white pl-11 text-base" placeholder="注文番号・商品名で検索" :aria-invalid="Boolean(errors.keyword)" :aria-describedby="errors.keyword ? 'order-search-error' : undefined" />
          </UiFormControl>
        </div>
        <UiFormMessage v-if="errors.keyword" id="order-search-error" role="alert">{{ errors.keyword }}</UiFormMessage>
      </UiFormItem>

      <UiButton ref="filterTrigger" type="button" variant="outline" class="h-11 justify-between rounded-xl bg-white px-3 text-base text-[#687a70] xl:hidden" :aria-expanded="filtersOpen" aria-haspopup="dialog" @click="setFiltersOpen(true)">
        {{ hasAppliedFilters ? '絞り込み（適用中）' : '絞り込み' }}
        <ChevronDown class="size-4 transition-transform" :class="filtersOpen ? 'rotate-180' : ''" aria-hidden="true" />
      </UiButton>

      <div id="order-filter-fields" class="hidden gap-4 xl:col-span-2 xl:grid xl:grid-cols-2 2xl:grid-cols-3">
        <UiFormItem class="grid gap-2">
          <UiFormLabel for="order-fulfillment">配送対応状態</UiFormLabel>
          <UiFormControl>
            <UiSelect id="order-fulfillment" v-model="draft.status" class="rounded-xl">
              <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.value === 'all' ? 'すべての状態' : option.label }}</option>
            </UiSelect>
          </UiFormControl>
        </UiFormItem>
        <UiFormItem class="grid gap-2">
          <UiFormLabel for="order-period">注文期間</UiFormLabel>
          <UiFormControl>
            <UiSelect id="order-period" v-model="draft.period" class="rounded-xl">
              <option v-for="option in periodOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
            </UiSelect>
          </UiFormControl>
        </UiFormItem>
        <OrderPeriodFields v-model:year="draft.year" v-model:from="draft.from" v-model:to="draft.to" :period="draft.period" :errors="errors" id-prefix="order" />
        <div class="flex flex-wrap items-end gap-2 xl:col-span-2 2xl:col-span-3">
          <UiButton type="submit" class="min-h-11" :disabled="isBusy">適用する</UiButton>
          <UiButton type="button" variant="outline" class="min-h-11" :disabled="isBusy" @click="resetFilters">リセット</UiButton>
        </div>
      </div>
      <p class="hidden text-sm leading-relaxed text-[#687a70] xl:col-span-3 xl:block">配送対応状態は生産者が管理し、キャンセル・返金はシステムが管理します。</p>
    </form>

    <UiDialog :open="filtersOpen" title="絞り込み" presentation="sheet" @update:open="setFiltersOpen" @close-auto-focus="restoreFilterFocus">
      <form id="order-mobile-filter-form" class="grid gap-5" novalidate @submit.prevent="applyFilters(true)">
        <section aria-labelledby="mobile-order-status-title">
          <h2 id="mobile-order-status-title" class="mb-3 text-sm font-bold">配送対応状態</h2>
          <div class="flex flex-wrap gap-2" role="group" aria-labelledby="mobile-order-status-title">
            <UiButton v-for="option in statusOptions" :key="option.value" type="button" variant="outline" class="min-h-9 rounded-full px-4 text-sm shadow-none" :class="draft.status === option.value ? 'border-[#237f4b] bg-[#237f4b] text-white hover:bg-[#176b3e] hover:text-white' : 'border-[#d7e3da] bg-[#f7faf7] text-[#687a70]'" :aria-pressed="draft.status === option.value" @click="draft.status = option.value">{{ option.label }}</UiButton>
          </div>
        </section>
        <section class="border-t border-[#dce5df] pt-4" aria-labelledby="mobile-order-period-title">
          <h2 id="mobile-order-period-title" class="mb-2 text-sm font-bold">注文期間</h2>
          <UiRadioGroup v-model="draft.period" :options="periodOptions" aria-labelledby="mobile-order-period-title" />
          <div v-if="draft.period === 'year' || draft.period === 'custom'" class="mt-3 grid gap-3">
            <OrderPeriodFields v-model:year="draft.year" v-model:from="draft.from" v-model:to="draft.to" :period="draft.period" :errors="errors" id-prefix="mobile-order" />
          </div>
        </section>
      </form>
      <template #footer>
        <div class="grid w-full grid-cols-2 gap-3">
          <UiButton type="button" variant="outline" class="min-h-11 rounded-xl border-[#237f4b] text-sm font-bold text-[#237f4b]" @click="resetMobileDraft">リセット</UiButton>
          <UiButton type="submit" form="order-mobile-filter-form" class="min-h-11 rounded-xl text-sm font-bold" :disabled="isBusy">適用する</UiButton>
        </div>
      </template>
    </UiDialog>

    <p class="mt-4 rounded-xl bg-[#fff6e4] p-4 text-sm leading-relaxed text-[#79582b] xl:hidden">購入者は注文完了後30分以内であれば、注文をキャンセルできます。</p>

    <section class="mt-5 xl:rounded-2xl xl:border xl:border-[#d6e2da] xl:bg-white xl:p-5" aria-labelledby="orders-title" :aria-busy="isBusy">
      <header class="mb-3 flex items-center justify-between gap-3">
        <h2 id="orders-title" class="text-lg font-bold text-[#17241d] xl:text-xl">注文</h2>
        <p class="text-sm text-[#687a70]" role="status">{{ ordersQuery.isPending.value ? '確認中' : ordersQuery.isError.value ? '取得できません' : `${meta?.total ?? 0}件` }}</p>
      </header>

      <UiCard v-if="ordersQuery.isPending.value" class="grid min-h-48 place-items-center gap-3 p-6 text-sm text-[#687a70]" role="status">
        <div class="grid justify-items-center gap-3"><LoaderCircle class="size-6 animate-spin" aria-hidden="true" />注文を読み込んでいます。</div>
      </UiCard>
      <UiCard v-else-if="ordersQuery.isError.value" class="grid justify-items-center gap-4 p-6 text-center">
        <p class="text-sm text-[#7b3329]" role="alert">注文を取得できませんでした。通信状態を確認して再試行してください。</p>
        <UiButton variant="outline" :disabled="isBusy" @click="ordersQuery.refetch()">再試行</UiButton>
      </UiCard>
      <UiCard v-else-if="orders.length === 0" class="grid min-h-48 content-center justify-items-center gap-3 p-6 text-center">
        <p class="text-base font-bold">{{ hasAppliedFilters ? '条件に一致する注文はありません。' : '注文はまだありません。' }}</p>
        <UiButton v-if="hasAppliedFilters" type="button" variant="outline" @click="resetFilters">絞り込みをリセット</UiButton>
      </UiCard>
      <template v-else>
        <p v-if="isBusy" class="mb-3 text-xs text-[#687a70]" role="status">注文を更新しています。</p>
        <ul class="grid gap-3 xl:hidden" aria-label="注文一覧">
          <li v-for="order in orders" :key="order.id">
            <RouterLink :to="{ name: 'order-summary', params: { id: order.id } }" class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto] items-center gap-3 rounded-xl border border-[#d6e2da] bg-white p-3.5 transition-colors hover:border-[#a9c8b6] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b]">
              <div class="min-w-0">
                <p class="break-all text-base font-bold text-[#17241d]">#{{ order.display_id }}</p>
                <p class="mt-2 text-xs text-[#687a70]">注文日 {{ orderedAt(order.ordered_at) }}</p>
                <ul class="mt-2 grid gap-1 text-sm font-bold text-[#17241d]">
                  <li v-for="item in order.items" :key="item.id" class="break-words">{{ item.product_name }} ×{{ item.quantity }}</li>
                </ul>
              </div>
              <div class="flex items-center gap-2">
                <OrderStatusBadge :order="order" />
                <ChevronRight class="size-4 shrink-0 text-[#687a70]" aria-hidden="true" />
              </div>
            </RouterLink>
          </li>
        </ul>

        <table class="hidden w-full table-fixed text-left text-sm xl:table">
          <caption class="sr-only">生産者の注文一覧</caption>
          <colgroup><col class="w-1/5" /><col class="w-1/5" /><col class="w-1/4" /><col class="w-1/12" /><col class="w-1/5" /><col /></colgroup>
          <thead class="bg-[#f3f6f3] text-[#687a70]">
            <tr><th scope="col" class="rounded-l-xl px-3 py-3 font-medium">サブ注文番号</th><th scope="col" class="px-3 py-3 font-medium">注文日</th><th scope="col" class="px-3 py-3 font-medium">商品</th><th scope="col" class="px-3 py-3 font-medium">数量</th><th scope="col" class="px-3 py-3 font-medium">配送対応状態</th><th scope="col" class="rounded-r-xl"><span class="sr-only">詳細</span></th></tr>
          </thead>
          <tbody class="divide-y divide-[#e0e9e3]">
            <tr v-for="order in orders" :key="order.id" class="hover:bg-[#f8fbf8]">
              <td class="px-3 py-5 align-top"><RouterLink :to="{ name: 'order-summary', params: { id: order.id } }" class="break-all font-bold text-[#17241d] hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b]">#{{ order.display_id }}</RouterLink></td>
              <td class="px-3 py-5 align-top text-[#687a70]">{{ orderedAt(order.ordered_at) }}</td>
              <td class="px-3 py-5 align-top"><ul class="grid gap-1"><li v-for="item in order.items" :key="item.id" class="break-words">{{ item.product_name }}</li></ul></td>
              <td class="px-3 py-5 align-top"><ul class="grid gap-1"><li v-for="item in order.items" :key="item.id">{{ item.quantity }}</li></ul></td>
              <td class="px-3 py-5 align-top"><OrderStatusBadge :order="order" /></td>
              <td class="py-5"><RouterLink :to="{ name: 'order-summary', params: { id: order.id } }" class="grid min-h-11 place-items-center rounded-md text-[#237f4b] focus-visible:outline-2 focus-visible:outline-offset-2" :aria-label="`注文 ${order.display_id} の概要を開く`"><ChevronRight class="size-4" aria-hidden="true" /></RouterLink></td>
            </tr>
          </tbody>
        </table>

        <nav v-if="meta" class="mt-4 flex items-center justify-between gap-3" aria-label="注文一覧のページ切り替え">
          <p class="text-xs text-[#687a70] sm:text-sm">{{ meta.from ?? 0 }}–{{ meta.to ?? 0 }} / {{ meta.total }}件</p>
          <div class="flex items-center gap-2">
            <UiButton type="button" variant="outline" class="size-11 p-2" aria-label="前のページ" :disabled="isBusy || meta.current_page <= 1" @click="changePage(-1)"><ChevronLeft class="size-5" aria-hidden="true" /></UiButton>
            <span class="sr-only">{{ meta.current_page }} / {{ meta.last_page }}ページ</span>
            <UiButton type="button" variant="outline" class="size-11 p-2" aria-label="次のページ" :disabled="isBusy || meta.current_page >= meta.last_page" @click="changePage(1)"><ChevronRight class="size-5" aria-hidden="true" /></UiButton>
          </div>
        </nav>
      </template>
    </section>
  </section>
</template>
