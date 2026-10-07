import { computed, ref } from 'vue'
import { getApiErrorMessage } from '@/lib/api-error'
import { useProducerProductsQuery } from '@/services/products/product.query'
import type {
  ProducerProductListFilters,
  ProducerProductListItem,
  ProducerProductPublicationState,
  ProducerProductStockState,
} from '@/types/product'

export function useProducerProduct() {
  // FR-P-006 / SCR-P-006: preserve reactive filters and Producer-owned query data.
  const keyword = ref('')
  const categoryFilter = ref('all')
  const publicationFilter = ref<'all' | ProducerProductPublicationState>('all')
  const stockFilter = ref<ProducerProductStockState>('all')
  const filters = computed<ProducerProductListFilters>(() => ({
    keyword: keyword.value.trim(),
    category: categoryFilter.value,
    publication_state: publicationFilter.value,
    stock_state: stockFilter.value,
  }))
  const productsQuery = useProducerProductsQuery(filters)
  const categories = computed(() => productsQuery.data.value?.meta.categories ?? [])
  const errorMessage = computed(() => getApiErrorMessage(productsQuery.error.value) || '商品を取得できませんでした。')

  const publicationOptions = [
    { value: 'all', label: 'すべての公開状態' },
    { value: 'published', label: '公開中' },
    { value: 'unpublished', label: '非公開' },
    { value: 'draft', label: '下書き' },
  ] as const

  function publicationLabel(state: ProducerProductListItem['publication_state']) {
    if (state === 'published') return '公開中'
    if (state === 'unpublished') return '非公開'
    return '下書き'
  }

  const activeFilters = computed(() => [
    { key: 'keyword' as const, label: `検索：${filters.value.keyword}`, active: Boolean(filters.value.keyword) },
    { key: 'category' as const, label: `カテゴリ：${categoryFilter.value}`, active: categoryFilter.value !== 'all' },
    { key: 'publication_state' as const, label: publicationOptions.find(option => option.value === publicationFilter.value)?.label ?? '', active: publicationFilter.value !== 'all' },
    { key: 'stock_state' as const, label: { all: '在庫状態', in_stock: '在庫あり', low_stock: '在庫少なめ', out_of_stock: '在庫なし' }[stockFilter.value], active: stockFilter.value !== 'all' },
  ].filter(filter => filter.active))

  function clearFilter(key: keyof ProducerProductListFilters) {
    if (key === 'keyword') keyword.value = ''
    if (key === 'category') categoryFilter.value = 'all'
    if (key === 'publication_state') publicationFilter.value = 'all'
    if (key === 'stock_state') stockFilter.value = 'all'
  }

  const products = computed(() => (productsQuery.data.value?.data ?? []).map(product => ({
    ...product,
    categoryLabel: product.category || '未設定',
    priceLabel: product.price_yen === null
      ? '未設定'
      : `¥${product.price_yen.toLocaleString('ja-JP')}${product.has_multiple_prices ? '〜' : ''}`,
    discountLabel: product.discount_bps <= 0 ? '' : `${Math.round(product.discount_bps / 100)}%OFF`,
    publicationLabel: publicationLabel(product.publication_state),
  })))

  function resetFilters() {
    keyword.value = ''
    categoryFilter.value = 'all'
    publicationFilter.value = 'all'
    stockFilter.value = 'all'
  }

  function reloadProducts() {
    return productsQuery.refetch()
  }

  return {
    keyword,
    categoryFilter,
    publicationFilter,
    stockFilter,
    publicationOptions,
    activeFilters,
    clearFilter,
    products,
    categories,
    errorMessage,
    isPending: productsQuery.isPending,
    isError: productsQuery.isError,
    isFetching: productsQuery.isFetching,
    resetFilters,
    reloadProducts,
  }
}
