import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useBuyerOrdersQuery } from '@/services/orders/orders.query'

export function useBuyerOrderHistory() {
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
  return {
    router,
    filterOpen,
    filters,
    applied,
    queryFilters,
    orders,
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
