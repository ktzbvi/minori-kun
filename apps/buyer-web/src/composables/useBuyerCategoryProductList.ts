import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { useAddBuyerCartItemMutation } from '@/services/cart/cart.mutation'
import { useBuyerCartQuery } from '@/services/cart/cart.query'
import {
  useBuyerCatalogueQuery,
  type BuyerCatalogueProduct,
} from '@/services/catalog/catalog.query'

export function useBuyerCategoryProductList() {
  const route = useRoute()
  const router = useRouter()
  const catalogueQuery = useBuyerCatalogueQuery()
  const cartQuery = useBuyerCartQuery()
  const addCartItemMutation = useAddBuyerCartItemMutation()
  const selectedCategoryId = computed(() => String(route.params.categoryId ?? 'all'))
  const selectedCategory = computed(() =>
    selectedCategoryId.value === 'all'
      ? 'すべて'
      : (catalogueQuery.data.value?.find((product) => product.category === selectedCategoryId.value)
          ?.category ?? selectedCategoryId.value),
  )
  const cartItemCount = computed(
    () => cartQuery.data.value?.items.reduce((total, item) => total + item.quantity, 0) ?? 0,
  )
  const categoryProducts = computed(() =>
    selectedCategoryId.value === 'all'
      ? (catalogueQuery.data.value ?? [])
      : (catalogueQuery.data.value ?? []).filter(
          (product) => product.category === selectedCategoryId.value,
        ),
  )
  function goBack() {
    void router.push({ name: 'categories' })
  }
  function openSearch() {
    void router.push({ name: 'search' })
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
  function productPrice(product: BuyerCatalogueProduct) {
    const variant = product.variants[0]
    return variant ? Math.round((variant.price_yen * (10_000 - variant.discount_bps)) / 10_000) : 0
  }
  function formatYen(amount: number) {
    return `税込 ${amount.toLocaleString('ja-JP')}円`
  }
  return {
    route,
    router,
    catalogueQuery,
    cartQuery,
    addCartItemMutation,
    selectedCategoryId,
    selectedCategory,
    cartItemCount,
    categoryProducts,
    goBack,
    openSearch,
    openProduct,
    addToCart,
    openCart,
    productPrice,
    formatYen,
  }
}
