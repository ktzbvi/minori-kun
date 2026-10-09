import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { usePresentationStore } from '@/stores/presentation'
import { toast } from '@minorikun/ui'
import { useAddBuyerCartItemMutation } from '@/services/cart/cart.mutation'
import {
  useBuyerProductFeedQuery,
  type BuyerCatalogueProduct,
} from '@/services/catalog/catalog.query'

export function useBuyerHome() {
  const { homeCategory: selectedCategory } = storeToRefs(usePresentationStore())
  const productList = ref<HTMLElement>()
  const loadMoreTrigger = ref<HTMLElement>()
  let productObserver: IntersectionObserver | undefined
  const router = useRouter()
  const catalogueQuery = useBuyerProductFeedQuery(selectedCategory)
  const addCartItemMutation = useAddBuyerCartItemMutation()
  const availableCategories = ref<string[]>([])
  watch(
    catalogueQuery.data,
    (data) => {
      if (data?.pages[0]) availableCategories.value = data.pages[0].categories ?? []
    },
    { immediate: true },
  )
  const categories = computed(() => [
    { id: 'all', label: 'すべて' },
    ...availableCategories.value.map((category) => ({ id: category, label: category })),
  ])
  const displayedProducts = computed(() => {
    const products = catalogueQuery.data.value?.pages.flatMap((page) => page.products) ?? []
    return Array.from(new Map(products.map((product) => [product.id, product])).values())
  })
  const productTotal = computed(() => catalogueQuery.data.value?.pages[0]?.total ?? 0)
  const hasMoreProducts = catalogueQuery.hasNextPage
  watch(selectedCategory, () => {
    if (productList.value) productList.value.scrollTop = 0
  })
  function addToCart(product: BuyerCatalogueProduct) {
    const variant = product.variants[0]
    if (!variant || variant.stock_quantity < 1) return

    addCartItemMutation.mutate(
      { variantId: variant.id, quantity: 1 },
      {
        onSuccess: () => toast.success('カートに追加しました'),
        onError: () => toast.error('カートに追加できませんでした。商品と在庫をご確認ください。'),
      },
    )
  }
  function openProduct(productId: string) {
    void router.push({ name: 'product-detail', params: { productId } })
  }
  function openSearch() {
    void router.push({ name: 'search' })
  }
  function loadMoreProducts() {
    if (!hasMoreProducts.value || catalogueQuery.isFetching.value || catalogueQuery.isError.value) {
      return
    }
    void catalogueQuery.fetchNextPage()
  }
  function retryProducts() {
    if (catalogueQuery.isFetching.value) return
    if (catalogueQuery.isFetchNextPageError.value) {
      void catalogueQuery.fetchNextPage()
    } else {
      void catalogueQuery.refetch()
    }
  }
  function observeProductList() {
    productObserver?.disconnect()
    if (
      loadMoreTrigger.value &&
      hasMoreProducts.value &&
      !catalogueQuery.isFetching.value &&
      !catalogueQuery.isError.value
    ) {
      productObserver?.observe(loadMoreTrigger.value)
    }
  }
  watch(
    [loadMoreTrigger, displayedProducts, catalogueQuery.isFetching, catalogueQuery.status],
    observeProductList,
    { flush: 'post' },
  )
  onMounted(() => {
    // The viewport observer also respects the mobile list's overflow clipping.
    productObserver = new IntersectionObserver((entries) => {
      if (entries.some((entry) => entry.target === loadMoreTrigger.value && entry.isIntersecting)) {
        loadMoreProducts()
      }
    })
    observeProductList()
  })
  onBeforeUnmount(() => productObserver?.disconnect())
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
    selectedCategory,
    productList,
    loadMoreTrigger,
    router,
    catalogueQuery,
    addCartItemMutation,
    categories,
    productTotal,
    displayedProducts,
    hasMoreProducts,
    addToCart,
    openProduct,
    openSearch,
    loadMoreProducts,
    retryProducts,
    productPrice,
    discountRate,
    formatYen,
  }
}
