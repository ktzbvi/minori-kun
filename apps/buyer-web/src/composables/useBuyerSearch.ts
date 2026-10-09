import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { useAddBuyerCartItemMutation } from '@/services/cart/cart.mutation'
import { useBuyerCartQuery } from '@/services/cart/cart.query'
import {
  useBuyerCatalogueQuery,
  type BuyerCatalogueProduct,
} from '@/services/catalog/catalog.query'

export function useBuyerSearch() {
  const route = useRoute()
  const router = useRouter()
  const catalogueQuery = useBuyerCatalogueQuery()
  const cartQuery = useBuyerCartQuery()
  const addCartItemMutation = useAddBuyerCartItemMutation()
  const cartItemCount = computed(
    () => cartQuery.data.value?.items.reduce((total, item) => total + item.quantity, 0) ?? 0,
  )
  const searchInput = ref(readQuery())
  const query = ref(searchInput.value.trim())
  const results = computed(() => {
    const normalizedQuery = query.value.toLocaleLowerCase()

    if (!normalizedQuery) return []

    return (catalogueQuery.data.value ?? []).filter((product) =>
      [product.name, product.description, product.category ?? '', product.shop_name ?? ''].some(
        (term) => term.toLocaleLowerCase().includes(normalizedQuery),
      ),
    )
  })
  watch(
    () => route.query.q,
    () => {
      searchInput.value = readQuery()
      query.value = searchInput.value.trim()
    },
  )
  function readQuery() {
    return typeof route.query.q === 'string' ? route.query.q : ''
  }
  function searchProducts() {
    const normalizedQuery = searchInput.value.trim()

    query.value = normalizedQuery
    void router.replace({
      name: 'search',
      query: normalizedQuery ? { q: normalizedQuery } : {},
    })
  }
  function openProduct(productId: string) {
    void router.push({ name: 'product-detail', params: { productId } })
  }
  function addToCart(product: BuyerCatalogueProduct) {
    const variant = product.variants[0]
    if (!variant || variant.stock_quantity < 1) return

    addCartItemMutation.mutate(
      { variantId: variant.id, quantity: 1 },
      {
        onSuccess: () => toast.success('カートに追加しました'),
        onError: () =>
          toast.error('カートに追加できませんでした。商品と在庫をご確認ください。'),
      },
    )
  }
  function openCart() {
    void router.push({ name: 'cart' })
  }
  function formatYen(amount: number) {
    return `税込 ${amount.toLocaleString('ja-JP')}円`
  }
  function productPrice(product: BuyerCatalogueProduct) {
    const variant = product.variants[0]
    return variant ? Math.round((variant.price_yen * (10_000 - variant.discount_bps)) / 10_000) : 0
  }
  return {
    route,
    router,
    catalogueQuery,
    cartQuery,
    addCartItemMutation,
    cartItemCount,
    searchInput,
    query,
    results,
    readQuery,
    searchProducts,
    openProduct,
    addToCart,
    openCart,
    formatYen,
    productPrice,
  }
}
