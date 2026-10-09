import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useBuyerOrdersQuery } from '@/services/orders/orders.query'

export function useBuyerOrderHistory() {
  const router = useRouter()
  const filterOpen = ref(false)
  const desktopFilters = ref(false)
  let desktopMedia: MediaQueryList | undefined
  function updateFilterViewport() {
    desktopFilters.value = desktopMedia?.matches ?? false
  }
  onMounted(() => {
    desktopMedia = globalThis.matchMedia('(min-width: 1024px)')
    updateFilterViewport()
    desktopMedia.addEventListener('change', updateFilterViewport)
  })
  onBeforeUnmount(() => desktopMedia?.removeEventListener('change', updateFilterViewport))
  const filters = reactive({
    period: 'all',
    from: '',
    to: '',
    year: String(new Date().getFullYear()),
  })
  const periodOptions = [
    { value: 'all', label: 'すべての期間' },
    { value: '30d', label: '過去30日' },
    { value: '90d', label: '過去90日' },
    { value: '12m', label: '過去12か月' },
    { value: 'year', label: '年を選択' },
    { value: 'custom', label: '期間を指定' },
  ]
  const applied = reactive({ ...filters })
  const queryFilters = computed(() =>
    Object.fromEntries(Object.entries(applied).filter(([, value]) => value)),
  )
  const orders = useBuyerOrdersQuery(queryFilters)
  const appliedPeriodLabel = computed(() =>
    applied.period === 'custom'
      ? `${applied.from} 〜 ${applied.to}`
      : applied.period === 'year'
        ? `${applied.year}年`
        : (periodOptions.find((option) => option.value === applied.period)?.label ??
          'すべての期間'),
  )
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
  function goBack() {
    void router.push({ name: 'my-page' })
  }
  function openOrder(orderId: string) {
    void router.push({ name: 'order-detail', params: { orderId } })
  }
  function retryOrders() {
    void orders.refetch()
  }
  function yen(value: number) {
    return `${value.toLocaleString('ja-JP')}円`
  }
  function date(value: string) {
    return new Date(value).toLocaleString('ja-JP', { dateStyle: 'medium' })
  }
  return {
    router,
    filterOpen,
    desktopFilters,
    filters,
    applied,
    queryFilters,
    orders,
    periodOptions,
    appliedPeriodLabel,
    goBack,
    openOrder,
    retryOrders,
    applyFilters,
    openFilters,
    closeFilters,
    resetFilters,
    openSearch,
    openCart,
    yen,
    date,
  }
}
