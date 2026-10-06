<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { ChevronDown, ChevronLeft, ChevronRight, Search } from 'lucide-vue-next'
import { z } from 'zod'
import { UiButton, UiCard, UiDialog, UiRadioGroup, UiFormControl, UiFormItem, UiFormLabel, UiFormMessage, UiInput, UiSelect, UiSelectTrigger, UiSelectContent, UiSelectItem, UiSelectValue, UiSkeleton } from '@minorikun/ui'
import OrderPeriodFields from '@/components/orders/OrderPeriodFields.vue'
import OrderStatusBadge from '@/components/orders/OrderStatusBadge.vue'
import { useProducerOrder } from '@/composables/useProducerOrder'

const {
  draft, orders, meta, isBusy, isPending, isError, hasAppliedFilters, canPreviousPage, canNextPage,
  periodOptions, statusOptions, restoreAppliedFilters, resetDraftFilters,
  applyFilters: applyOrderFilters, resetFilters: resetOrderFilters, changePage, reloadOrders,
} = useProducerOrder()

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
const errors = ref<Record<string, string | undefined>>({})
const filtersOpen = ref(false)
const filterTrigger = ref<{ $el: { focus: () => void } } | null>(null)

function setFiltersOpen(open: boolean) {
  restoreAppliedFilters()
  errors.value = {}
  filtersOpen.value = open
}

function restoreFilterFocus(event: globalThis.Event) {
  event.preventDefault()
  filterTrigger.value?.$el.focus()
}

function resetMobileDraft() {
  resetDraftFilters()
  errors.value = {}
}

function applyFilters(closeSheet = false) {
  const result = filterSchema.safeParse(draft)
  errors.value = {}
  if (!result.success) {
    for (const issue of result.error.issues) errors.value[String(issue.path[0])] ??= issue.message
    return
  }
  applyOrderFilters(result.data)
  if (closeSheet) filtersOpen.value = false
}

function resetFilters() {
  resetOrderFilters()
  errors.value = {}
}
</script>

<template>
  <section>
    <h1 class="text-xl font-extrabold text-[#17241d] sm:text-2xl lg:text-3xl">注文一覧</h1>
    <p class="mt-2 text-sm text-[#687a70] lg:text-base">自分の商品を含む注文を確認できます。</p>

    <form
      class="mt-6 grid items-start gap-4 xl:grid-cols-[minmax(0,1.3fr)_minmax(0,2fr)] xl:rounded-2xl xl:border xl:border-[#d6e2da] xl:bg-white xl:p-6"
      novalidate aria-label="注文の検索と絞り込み" @submit.prevent="applyFilters()">
      <UiFormItem class="min-w-0 content-start">
        <UiFormLabel for="order-search" class="sr-only xl:not-sr-only">注文番号・商品名で検索</UiFormLabel>
        <div class="relative h-11">
          <UiButton type="submit" variant="ghost"
            class="absolute top-1/2 left-1 z-10 size-9 min-h-0 -translate-y-1/2 p-2 text-[#687a70]" aria-label="注文を検索"
            :disabled="isBusy">
            <Search class="size-5" aria-hidden="true" />
          </UiButton>
          <UiFormControl>
            <UiInput id="order-search" v-model="draft.keyword" type="search" maxlength="255"
              class="h-11 rounded-xl bg-white pl-11 text-base" placeholder="注文番号・商品名で検索"
              :aria-invalid="Boolean(errors.keyword)"
              :aria-describedby="errors.keyword ? 'order-search-error' : undefined" />
          </UiFormControl>
        </div>
        <UiFormMessage v-if="errors.keyword" id="order-search-error" role="alert">{{ errors.keyword }}</UiFormMessage>
      </UiFormItem>

      <UiButton ref="filterTrigger" type="button" variant="outline"
        class="h-11 justify-between rounded-xl bg-white px-3 text-base text-[#687a70] xl:hidden"
        :aria-expanded="filtersOpen" aria-haspopup="dialog" @click="setFiltersOpen(true)">
        {{ hasAppliedFilters ? '絞り込み（適用中）' : '絞り込み' }}
        <ChevronDown class="size-4 transition-transform" :class="filtersOpen ? 'rotate-180' : ''" aria-hidden="true" />
      </UiButton>

      <div id="order-filter-fields"
        class="hidden items-start gap-4 xl:grid xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
        <UiFormItem class="grid gap-2">
          <UiFormLabel for="order-fulfillment">配送対応状態</UiFormLabel>
          <UiFormControl>
            <UiSelect v-model="draft.status">
              <UiSelectTrigger id="order-fulfillment" class="rounded-xl">
                <UiSelectValue />
              </UiSelectTrigger>
              <UiSelectContent>
                <UiSelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.value
                  === 'all' ? 'すべての状態' : option.label }}</UiSelectItem>
              </UiSelectContent>
            </UiSelect>
          </UiFormControl>
        </UiFormItem>
        <UiFormItem class="grid gap-2">
          <UiFormLabel for="order-period">注文期間</UiFormLabel>
          <UiFormControl>
            <UiSelect v-model="draft.period">
              <UiSelectTrigger id="order-period" class="rounded-xl">
                <UiSelectValue />
              </UiSelectTrigger>
              <UiSelectContent>
                <UiSelectItem v-for="option in periodOptions" :key="option.value" :value="option.value">{{ option.label
                  }}</UiSelectItem>
              </UiSelectContent>
            </UiSelect>
          </UiFormControl>
        </UiFormItem>
        <div class="flex gap-2 self-end">
          <UiButton type="submit" class="min-h-11" :disabled="isBusy">適用する</UiButton>
          <UiButton type="button" variant="outline" class="min-h-11" :disabled="isBusy" @click="resetFilters">リセット
          </UiButton>
        </div>
        <div v-if="draft.period === 'year' || draft.period === 'custom'"
          class="col-span-3 grid grid-cols-2 gap-4 border-t border-[#e0e9e3] pt-4">
          <OrderPeriodFields v-model:year="draft.year" v-model:from="draft.from" v-model:to="draft.to"
            :period="draft.period" :errors="errors" id-prefix="order" />
        </div>
      </div>
      <p class="hidden border-t border-[#e0e9e3] pt-4 text-sm leading-relaxed text-[#687a70] xl:col-span-2 xl:block">
        配送対応状態は生産者が管理し、キャンセル・返金はシステムが管理します。</p>
    </form>

    <UiDialog :open="filtersOpen" title="絞り込み" presentation="sheet" @update:open="setFiltersOpen"
      @close-auto-focus="restoreFilterFocus">
      <form id="order-mobile-filter-form" class="grid gap-5" novalidate @submit.prevent="applyFilters(true)">
        <section aria-labelledby="mobile-order-status-title">
          <h2 id="mobile-order-status-title" class="mb-3 text-sm font-bold">配送対応状態</h2>
          <div class="flex flex-wrap gap-2" role="group" aria-labelledby="mobile-order-status-title">
            <UiButton v-for="option in statusOptions" :key="option.value" type="button" variant="outline"
              class="min-h-9 rounded-full px-4 text-sm shadow-none"
              :class="draft.status === option.value ? 'border-[#237f4b] bg-[#237f4b] text-white hover:bg-[#176b3e] hover:text-white' : 'border-[#d7e3da] bg-[#f7faf7] text-[#687a70]'"
              :aria-pressed="draft.status === option.value" @click="draft.status = option.value">{{ option.label }}
            </UiButton>
          </div>
        </section>
        <section class="border-t border-[#dce5df] pt-4" aria-labelledby="mobile-order-period-title">
          <h2 id="mobile-order-period-title" class="mb-2 text-sm font-bold">注文期間</h2>
          <UiRadioGroup v-model="draft.period" :options="periodOptions" aria-labelledby="mobile-order-period-title" />
          <div v-if="draft.period === 'year' || draft.period === 'custom'" class="mt-3 grid gap-3">
            <OrderPeriodFields v-model:year="draft.year" v-model:from="draft.from" v-model:to="draft.to"
              :period="draft.period" :errors="errors" id-prefix="mobile-order" />
          </div>
        </section>
      </form>
      <template #footer>
        <div class="grid w-full grid-cols-2 gap-3">
          <UiButton type="button" variant="outline"
            class="min-h-11 rounded-xl border-[#237f4b] text-sm font-bold text-[#237f4b]" @click="resetMobileDraft">リセット
          </UiButton>
          <UiButton type="submit" form="order-mobile-filter-form" class="min-h-11 rounded-xl text-sm font-bold"
            :disabled="isBusy">適用する</UiButton>
        </div>
      </template>
    </UiDialog>

    <p class="mt-4 rounded-xl bg-[#fff6e4] p-4 text-sm leading-relaxed text-[#79582b] xl:hidden">
      購入者は注文完了後30分以内であれば、注文をキャンセルできます。</p>

    <section class="mt-6 xl:rounded-2xl xl:border xl:border-[#d6e2da] xl:bg-white xl:p-6" aria-labelledby="orders-title"
      :aria-busy="isBusy">
      <header class="mb-5 flex items-center justify-between gap-3">
        <h2 id="orders-title" class="text-lg font-bold text-[#17241d] xl:text-xl">注文</h2>
        <p class="text-sm text-[#687a70]" role="status">{{ isPending ? '確認中' : isError ? '取得できません' : `${meta?.total ??
          0}件`
          }}</p>
      </header>

      <div v-if="isPending" role="status" aria-live="polite">
        <span class="sr-only">注文を読み込んでいます。</span>
        <div class="grid gap-3 xl:hidden" aria-hidden="true">
          <div v-for="row in 5" :key="row" class="grid min-w-0 gap-4 rounded-xl border border-[#d6e2da] bg-white p-4">
            <div class="min-w-0">
              <div class="flex items-center justify-between gap-3">
                <UiSkeleton class="h-5 w-3/4" />
                <UiSkeleton class="size-5 shrink-0" />
              </div>
              <UiSkeleton class="mt-2 h-4 w-40 max-w-full" />
              <div class="mt-4 flex items-center justify-between gap-4">
                <UiSkeleton class="h-5 w-1/2" />
                <UiSkeleton class="h-5 w-6 shrink-0" />
              </div>
            </div>
            <div class="grid justify-items-start gap-1.5 border-t border-[#e0e9e3] pt-3">
              <UiSkeleton class="h-7 w-20 rounded-full" />
              <UiSkeleton class="h-3 w-16" />
            </div>
          </div>
        </div>
        <table class="hidden w-full table-fixed xl:table" aria-hidden="true">
          <colgroup>
            <col class="w-1/4" />
            <col class="w-44" />
            <col />
            <col class="w-16" />
            <col class="w-40" />
            <col class="w-12" />
          </colgroup>
          <thead class="bg-[#f3f6f3]">
            <tr>
              <td class="rounded-l-xl px-3 py-3">
                <UiSkeleton class="h-5 w-24" />
              </td>
              <td class="px-3 py-3">
                <UiSkeleton class="h-5 w-16" />
              </td>
              <td class="px-3 py-3">
                <UiSkeleton class="h-5 w-12" />
              </td>
              <td class="px-3 py-3">
                <UiSkeleton class="h-5 w-8" />
              </td>
              <td class="px-3 py-3">
                <UiSkeleton class="h-5 w-24" />
              </td>
              <td class="rounded-r-xl" />
            </tr>
          </thead>
          <tbody class="divide-y divide-[#e0e9e3]">
            <tr v-for="row in 5" :key="row">
              <td class="px-3 py-5 align-middle">
                <UiSkeleton class="h-5 w-5/6" />
              </td>
              <td class="px-3 py-5 align-middle">
                <UiSkeleton class="h-5 w-32" />
              </td>
              <td class="px-3 py-5 align-middle">
                <UiSkeleton class="h-5 w-3/4" />
              </td>
              <td class="px-3 py-5 align-middle">
                <UiSkeleton class="h-5 w-6" />
              </td>
              <td class="px-3 py-5 align-middle">
                <div class="grid justify-items-start gap-1.5">
                  <UiSkeleton class="h-7 w-20 rounded-full" />
                  <UiSkeleton class="h-3 w-16" />
                </div>
              </td>
              <td class="py-5">
                <div class="grid min-h-11 place-items-center">
                  <UiSkeleton class="size-4" />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="mt-4 flex items-center justify-between gap-3" aria-hidden="true">
          <UiSkeleton class="h-4 w-24" />
          <div class="flex gap-2">
            <UiSkeleton class="size-11 rounded-lg" />
            <UiSkeleton class="size-11 rounded-lg" />
          </div>
        </div>
      </div>
      <UiCard v-else-if="isError" class="grid justify-items-center gap-4 p-6 text-center">
        <p class="text-sm text-[#7b3329]" role="alert">注文を取得できませんでした。通信状態を確認して再試行してください。</p>
        <UiButton variant="outline" :disabled="isBusy" @click="reloadOrders">再試行</UiButton>
      </UiCard>
      <UiCard v-else-if="orders.length === 0"
        class="grid min-h-48 content-center justify-items-center gap-3 p-6 text-center">
        <p class="text-base font-bold">{{ hasAppliedFilters ? '条件に一致する注文はありません。' : '注文はまだありません。' }}</p>
        <UiButton v-if="hasAppliedFilters" type="button" variant="outline" @click="resetFilters">絞り込みをリセット</UiButton>
      </UiCard>
      <template v-else>
        <p v-if="isBusy" class="mb-3 text-xs text-[#687a70]" role="status">注文を更新しています。</p>
        <ul class="grid gap-3 xl:hidden" aria-label="注文一覧">
          <li v-for="order in orders" :key="order.id">
            <RouterLink :to="{ name: 'order-summary', params: { id: order.id } }"
              class="grid min-w-0 gap-4 rounded-xl border border-[#d6e2da] bg-white p-4 transition-colors hover:border-[#a9c8b6] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b]">
              <div class="min-w-0">
                <div class="flex items-center gap-3">
                  <p class="flex min-w-0 flex-1 text-sm font-bold text-[#17241d]" :title="`#${order.display_id}`">
                    <span class="truncate">#{{ order.display_id.slice(0, -8) }}</span><span class="shrink-0">{{
                      order.display_id.slice(-8) }}</span>
                  </p>
                  <ChevronRight class="size-5 shrink-0 text-[#687a70]" aria-hidden="true" />
                </div>
                <p class="mt-2 text-xs text-[#687a70]">注文日 {{ order.orderedAtLabel }}</p>
                <ul class="mt-4 grid gap-2 text-sm font-medium text-[#17241d]">
                  <li v-for="item in order.items" :key="item.id" class="flex items-start justify-between gap-4">
                    <span class="min-w-0 break-words">{{ item.product_name }}</span>
                    <span class="shrink-0 tabular-nums text-[#687a70]">×{{ item.quantity }}</span>
                  </li>
                </ul>
              </div>
              <div class="border-t border-[#e0e9e3] pt-3">
                <OrderStatusBadge :order="order" />
              </div>
            </RouterLink>
          </li>
        </ul>

        <table class="hidden w-full table-fixed text-left text-sm xl:table">
          <caption class="sr-only">生産者の注文一覧</caption>
          <colgroup>
            <col class="w-1/4" />
            <col class="w-44" />
            <col />
            <col class="w-16" />
            <col class="w-40" />
            <col class="w-12" />
          </colgroup>
          <thead class="bg-[#f3f6f3] text-[#687a70]">
            <tr>
              <th scope="col" class="rounded-l-xl px-3 py-3 font-medium">サブ注文番号</th>
              <th scope="col" class="px-3 py-3 font-medium">注文日</th>
              <th scope="col" class="px-3 py-3 font-medium">商品</th>
              <th scope="col" class="px-3 py-3 font-medium">数量</th>
              <th scope="col" class="px-3 py-3 font-medium">配送対応状態</th>
              <th scope="col" class="rounded-r-xl"><span class="sr-only">詳細</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#e0e9e3]">
            <tr v-for="order in orders" :key="order.id" class="hover:bg-[#f8fbf8]">
              <td class="px-3 py-5 align-middle">
                <RouterLink :to="{ name: 'order-summary', params: { id: order.id } }"
                  class="flex min-w-0 font-bold text-[#17241d] hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b]"
                  :title="`#${order.display_id}`"><span class="truncate">#{{ order.display_id.slice(0, -8)
                    }}</span><span class="shrink-0">{{ order.display_id.slice(-8) }}</span></RouterLink>
              </td>
              <td class="whitespace-nowrap px-3 py-5 align-middle tabular-nums text-[#687a70]">{{ order.orderedAtLabel
                }}</td>
              <td class="px-3 py-5 align-middle">
                <ul class="grid gap-1">
                  <li v-for="item in order.items" :key="item.id" class="break-words font-medium">{{ item.product_name }}
                  </li>
                </ul>
              </td>
              <td class="px-3 py-5 align-middle tabular-nums">
                <ul class="grid gap-1">
                  <li v-for="item in order.items" :key="item.id">{{ item.quantity }}</li>
                </ul>
              </td>
              <td class="px-3 py-5 align-middle">
                <OrderStatusBadge :order="order" />
              </td>
              <td class="py-5">
                <RouterLink :to="{ name: 'order-summary', params: { id: order.id } }"
                  class="grid min-h-11 place-items-center rounded-md text-[#237f4b] focus-visible:outline-2 focus-visible:outline-offset-2"
                  :aria-label="`注文 ${order.display_id} の概要を開く`">
                  <ChevronRight class="size-4" aria-hidden="true" />
                </RouterLink>
              </td>
            </tr>
          </tbody>
        </table>

        <nav v-if="meta" class="mt-4 flex items-center justify-between gap-3" aria-label="注文一覧のページ切り替え">
          <p class="text-xs text-[#687a70] sm:text-sm">{{ meta.from ?? 0 }}–{{ meta.to ?? 0 }} / {{ meta.total }}件</p>
          <div class="flex items-center gap-2">
            <UiButton type="button" variant="outline" class="size-11 p-2" aria-label="前のページ"
              :disabled="!canPreviousPage" @click="changePage(-1)">
              <ChevronLeft class="size-5" aria-hidden="true" />
            </UiButton>
            <span class="sr-only">{{ meta.current_page }} / {{ meta.last_page }}ページ</span>
            <UiButton type="button" variant="outline" class="size-11 p-2" aria-label="次のページ" :disabled="!canNextPage"
              @click="changePage(1)">
              <ChevronRight class="size-5" aria-hidden="true" />
            </UiButton>
          </div>
        </nav>
      </template>
    </section>
  </section>
</template>
