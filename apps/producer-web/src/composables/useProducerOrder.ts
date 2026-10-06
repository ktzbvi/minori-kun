import { computed, reactive, ref } from 'vue'
import { useProducerOrdersQuery } from '@/services/orders/order.query'
import type { ProducerOrderFilterValues, ProducerOrderListFilters } from '@/types/order'

export function useProducerOrder() {
  // FR-P-008 / P08-02: only validated, applied filters affect the owned-order query.
  const currentYear = new Intl.DateTimeFormat('en', { year: 'numeric', timeZone: 'Asia/Tokyo' }).format(new Date())
  const defaults = () => ({ keyword: '', status: 'all', period: 'all', year: currentYear, from: '', to: '' })
  const draft = reactive(defaults())
  const applied = ref<ProducerOrderListFilters>({ period: 'all', status: 'all', page: 1 })
  const ordersQuery = useProducerOrdersQuery(applied)
  const meta = computed(() => ordersQuery.data.value?.meta)
  const isBusy = ordersQuery.isFetching
  const dateFormatter = new Intl.DateTimeFormat('ja-JP', {
    timeZone: 'Asia/Tokyo', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false,
  })
  const orders = computed(() => (ordersQuery.data.value?.data ?? []).map(order => ({
    ...order,
    orderedAtLabel: order.ordered_at ? dateFormatter.format(new Date(order.ordered_at)) : '注文日未設定',
  })))
  const hasAppliedFilters = computed(() => Boolean(applied.value.keyword)
    || applied.value.period !== 'all' || applied.value.status !== 'all')
  const canPreviousPage = computed(() => !isBusy.value && (meta.value?.current_page ?? 1) > 1)
  const canNextPage = computed(() => !isBusy.value && (meta.value?.current_page ?? 1) < (meta.value?.last_page ?? 1))
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

  function restoreAppliedFilters() {
    Object.assign(draft, {
      status: applied.value.status ?? 'all', period: applied.value.period ?? 'all',
      year: String(applied.value.year ?? currentYear), from: applied.value.from ?? '', to: applied.value.to ?? '',
    })
  }

  function resetDraftFilters() {
    Object.assign(draft, defaults(), { keyword: draft.keyword })
  }

  function applyFilters(values: ProducerOrderFilterValues) {
    applied.value = {
      keyword: values.keyword || undefined, status: values.status, period: values.period,
      year: values.period === 'year' ? Number(values.year) : undefined,
      from: values.period === 'custom' ? values.from : undefined,
      to: values.period === 'custom' ? values.to : undefined, page: 1,
    }
  }

  function resetFilters() {
    Object.assign(draft, defaults())
    applied.value = { period: 'all', status: 'all', page: 1 }
  }

  function changePage(delta: number) {
    const next = (meta.value?.current_page ?? 1) + delta
    if (!isBusy.value && next >= 1 && next <= (meta.value?.last_page ?? 1)) {
      applied.value = { ...applied.value, page: next }
    }
  }

  function reloadOrders() {
    return ordersQuery.refetch()
  }

  return {
    draft, orders, meta, isBusy, hasAppliedFilters, canPreviousPage, canNextPage,
    periodOptions, statusOptions, restoreAppliedFilters, resetDraftFilters, applyFilters,
    resetFilters, changePage, reloadOrders,
    isPending: ordersQuery.isPending,
    isError: ordersQuery.isError,
  }
}
