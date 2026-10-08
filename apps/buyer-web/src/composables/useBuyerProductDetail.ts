import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { useAddBuyerCartItemMutation } from '@/services/cart/cart.mutation'
import { useBuyerCartQuery } from '@/services/cart/cart.query'
import {
  useBuyerCatalogueQuery,
  type BuyerCatalogueVariant,
} from '@/services/catalog/catalog.query'

export function useBuyerProductDetail() {
  const route = useRoute()
  const router = useRouter()
  const catalogueQuery = useBuyerCatalogueQuery()
  const cartQuery = useBuyerCartQuery()
  const addCartItemMutation = useAddBuyerCartItemMutation()
  const product = computed(() =>
    catalogueQuery.data.value?.find((item) => item.id === route.params.productId),
  )
  const selectedVariantId = ref('')
  const quantity = ref(1)
  const selectedVariant = computed(
    () =>
      product.value?.variants.find((variant) => variant.id === selectedVariantId.value) ??
      product.value?.variants[0],
  )
  const cartItemCount = computed(
    () => cartQuery.data.value?.items.reduce((total, item) => total + item.quantity, 0) ?? 0,
  )
  const canPurchase = computed(() =>
    Boolean(selectedVariant.value && selectedVariant.value.stock_quantity >= quantity.value),
  )
  watch(
    product,
    (next) => {
      selectedVariantId.value = next?.variants[0]?.id ?? ''
      quantity.value = 1
    },
    { immediate: true },
  )
  function decreaseQuantity() {
    quantity.value = Math.max(1, quantity.value - 1)
  }
  function increaseQuantity() {
    if (selectedVariant.value) {
      quantity.value = Math.min(selectedVariant.value.stock_quantity, quantity.value + 1)
    }
  }
  function addToCart() {
    if (!canPurchase.value) return

    if (!selectedVariant.value) return

    addCartItemMutation.mutate(
      { variantId: selectedVariant.value.id, quantity: quantity.value },
      {
        onSuccess: () => toast.success('カートに追加しました'),
        onError: () =>
          toast.error('カートに追加できませんでした。ログイン状態を確認してください。'),
      },
    )
  }
  function openSearch() {
    void router.push({ name: 'search' })
  }
  function openCart() {
    void router.push({ name: 'cart' })
  }
  function buyNow() {
    if (!canPurchase.value || !selectedVariant.value) return

    addCartItemMutation.mutate(
      { variantId: selectedVariant.value.id, quantity: quantity.value },
      {
        onSuccess: () => router.push({ name: 'cart' }),
        onError: () =>
          toast.error('カートに追加できませんでした。在庫とログイン状態をご確認ください。'),
      },
    )
  }
  function formatYen(amount: number) {
    return `税込 ${amount.toLocaleString('ja-JP')}円`
  }
  function discountedPrice(variant: BuyerCatalogueVariant) {
    return Math.round((variant.price_yen * (10_000 - variant.discount_bps)) / 10_000)
  }
  return {
    route,
    router,
    catalogueQuery,
    cartQuery,
    addCartItemMutation,
    product,
    selectedVariantId,
    quantity,
    selectedVariant,
    cartItemCount,
    canPurchase,
    decreaseQuantity,
    increaseQuantity,
    addToCart,
    openSearch,
    openCart,
    buyNow,
    formatYen,
    discountedPrice,
  }
}
