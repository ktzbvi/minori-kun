import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { useAddBuyerCartItemMutation } from '@/services/cart/cart.mutation'
import { useBuyerCartQuery } from '@/services/cart/cart.query'
import {
  useBuyerCatalogueQuery,
  type BuyerCatalogueProduct,
} from '@/services/catalog/catalog.query'

export function useBuyerHome() {
  const pageSize = 4
  const selectedCategory = ref('all')
  const visibleCount = ref(pageSize)
  const productList = ref<HTMLElement>()
  const router = useRouter()
  const catalogueQuery = useBuyerCatalogueQuery()
  const cartQuery = useBuyerCartQuery()
  const addCartItemMutation = useAddBuyerCartItemMutation()
  const cartItemCount = computed(
    () => cartQuery.data.value?.items.reduce((total, item) => total + item.quantity, 0) ?? 0,
  )
  const categories = computed(() => [
    { id: 'all', label: 'すべて' },
    ...Array.from(
      new Set((catalogueQuery.data.value ?? []).map((product) => product.category).filter(Boolean)),
    ).map((category) => ({ id: category!, label: category! })),
  ])
  const allProducts = computed(() => catalogueQuery.data.value ?? [])
  const visibleProducts = computed(() =>
    selectedCategory.value === 'all'
      ? allProducts.value
      : allProducts.value.filter((product) => product.category === selectedCategory.value),
  )
  const displayedProducts = computed(() => visibleProducts.value.slice(0, visibleCount.value))
  const hasMoreProducts = computed(
    () => displayedProducts.value.length < visibleProducts.value.length,
  )
  watch(selectedCategory, () => {
    visibleCount.value = pageSize
    void fillProductList()
  })
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
  function openProduct(productId: string) {
    void router.push({ name: 'product-detail', params: { productId } })
  }
  function openSearch() {
    void router.push({ name: 'search' })
  }
  function loadMoreProducts() {
    if (!hasMoreProducts.value) return

    visibleCount.value += pageSize
  }
  async function fillProductList() {
    await nextTick()

    while (
      hasMoreProducts.value &&
      productList.value &&
      productList.value.scrollHeight <= productList.value.clientHeight
    ) {
      loadMoreProducts()
      await nextTick()
    }
  }
  function handleProductListScroll(event: Event) {
    const element = event.currentTarget as HTMLElement
    const isNearBottom = element.scrollTop + element.clientHeight >= element.scrollHeight - 80

    if (isNearBottom) loadMoreProducts()
  }
  onMounted(() => {
    void fillProductList()
  })
  function productPrice(product: BuyerCatalogueProduct) {
    const variant = product.variants[0]
    if (!variant) return 0

    return Math.round((variant.price_yen * (10_000 - variant.discount_bps)) / 10_000)
  }
  function discountRate(product: BuyerCatalogueProduct) {
    return (product.variants[0]?.discount_bps ?? 0) / 100
  }
  function formatYen(amount: number) {
    return `税込 ${amount.toLocaleString('ja-JP')}円`
  }
  return {
    pageSize,
    selectedCategory,
    visibleCount,
    productList,
    router,
    catalogueQuery,
    cartQuery,
    addCartItemMutation,
    cartItemCount,
    categories,
    allProducts,
    visibleProducts,
    displayedProducts,
    hasMoreProducts,
    addToCart,
    openCart,
    openProduct,
    openSearch,
    loadMoreProducts,
    fillProductList,
    handleProductListScroll,
    productPrice,
    discountRate,
    formatYen,
  }
}
