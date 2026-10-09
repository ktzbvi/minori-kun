import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQueryClient } from '@tanstack/vue-query'
import { toast } from '@minorikun/ui'
import {
  useRemoveBuyerCartItemMutation,
  useUpdateBuyerCartItemMutation,
} from '@/services/cart/cart.mutation'
import { cacheBuyerCart, useBuyerCartQuery, type BuyerCartItem } from '@/services/cart/cart.query'
import { mergeGuestCartAfterAuthentication } from '@/services/cart/cart.mutation'
import { buyerCartKeys } from '@/services/cart/cart.key'
import { getBuyerAccountProfile } from '@/services/account/account.api'
import { readGuestCart } from '@/lib/guest-cart'

export function useBuyerCart() {
  const router = useRouter()
  const queryClient = useQueryClient()
  const cartQuery = useBuyerCartQuery()
  const updateCartItemMutation = useUpdateBuyerCartItemMutation()
  const removeCartItemMutation = useRemoveBuyerCartItemMutation()
  const cartItems = computed(() => cartQuery.data.value?.items ?? [])
  const deliveryPrefecture = ref('')
  const isDeliveryFeeKnown = computed(() => Boolean(deliveryPrefecture.value))
  onMounted(async () => {
    try {
      deliveryPrefecture.value = (await getBuyerAccountProfile()).prefecture
      if (readGuestCart().length) {
        const result = await mergeGuestCartAfterAuthentication()
        if (result.cart) cacheBuyerCart(result.cart)
        await queryClient.invalidateQueries({ queryKey: buyerCartKeys.all() })
        if (result.failedCount) {
          toast.warning('一部の商品をカートに反映できませんでした。商品と在庫をご確認ください。')
        }
      }
    } catch {
      // Guests have no saved delivery prefecture; their cart stays local until authentication.
    }
  })
  const producerGroups = computed(() => {
    const grouped = new Map<string, BuyerCartItem[]>()

    for (const item of cartItems.value) {
      const current = grouped.get(item.producer_id) ?? []
      current.push(item)
      grouped.set(item.producer_id, current)
    }

    return Array.from(grouped, ([producerId, items]) => ({
      producerId,
      shopName: items[0]?.shop_name ?? 'ショップ',
      items,
      itemCount: items.reduce((total, item) => total + item.quantity, 0),
      subtotal: shopSubtotal(items),
      hasUnavailableItem: items.some((item) => item.pending_merge || item.unavailable),
    }))
  })
  const hasPendingGuestItems = computed(() => cartItems.value.some((item) => item.pending_merge))
  function decreaseQuantity(item: BuyerCartItem) {
    if (item.quantity === 1) return

    updateCartItemMutation.mutate(
      { itemId: item.id, quantity: item.quantity - 1 },
      {
        onError: () => toast.error('数量を変更できませんでした。'),
      },
    )
  }
  function increaseQuantity(item: BuyerCartItem) {
    if (item.quantity >= item.stock_quantity) {
      toast.warning('在庫数を確認してください')
      return
    }

    updateCartItemMutation.mutate(
      { itemId: item.id, quantity: item.quantity + 1 },
      {
        onError: () => toast.error('数量を変更できませんでした。'),
      },
    )
  }
  function removeLine(item: BuyerCartItem) {
    removeCartItemMutation.mutate(item.id, {
      onSuccess: () => toast.success('カートから削除しました'),
      onError: () => toast.error('商品を削除できませんでした。'),
    })
  }
  function openSearch() {
    void router.push({ name: 'search' })
  }
  function goHome() {
    void router.push({ name: 'home' })
  }
  function proceedToOrderConfirmation(producerId: string) {
    if (cartQuery.data.value?.id === 'guest') {
      void router.push({ name: 'login', query: { redirect: '/cart' } })
      return
    }

    void router.push({ name: 'order-confirmation', query: { producer: producerId } })
  }
  async function retryGuestCartMerge() {
    try {
      const result = await mergeGuestCartAfterAuthentication()
      if (result.cart) cacheBuyerCart(result.cart)
      await queryClient.invalidateQueries({ queryKey: buyerCartKeys.all() })
      if (result.failedCount) {
        toast.warning('一部の商品をカートに反映できませんでした。商品と在庫をご確認ください。')
      } else {
        toast.success('カートを更新しました。')
      }
    } catch {
      toast.error('カートを更新できませんでした。時間をおいて再度お試しください。')
    }
  }
  function formatYen(amount: number) {
    return `税込 ${amount.toLocaleString('ja-JP')}円`
  }
  function unitPrice(item: BuyerCartItem) {
    return Math.round((item.unit_price_yen * (10_000 - item.discount_bps)) / 10_000)
  }
  function lineTotal(item: BuyerCartItem) {
    return unitPrice(item) * item.quantity
  }
  function deliveryFee(item: BuyerCartItem) {
    if (!deliveryPrefecture.value) return 0

    if (deliveryPrefecture.value === '北海道') {
      return item.delivery_fee_hokkaido_yen
    }

    if (deliveryPrefecture.value === '沖縄県') {
      return item.delivery_fee_okinawa_yen
    }

    return item.delivery_fee_honshu_yen
  }
  function deliveryFeeItem(items: BuyerCartItem[]) {
    return items.reduce<BuyerCartItem | undefined>(
      (selected, item) =>
        !selected || discountedFee(item) > discountedFee(selected) ? item : selected,
      undefined,
    )
  }
  function discountedDeliveryFee(items: BuyerCartItem[]) {
    const item = deliveryFeeItem(items)
    return item ? discountedFee(item) : 0
  }
  function shopSubtotal(items: BuyerCartItem[]) {
    return items.reduce((total, item) => total + lineTotal(item), 0) + discountedDeliveryFee(items)
  }
  function lineAmount(item: BuyerCartItem, items: BuyerCartItem[]) {
    return (
      lineTotal(item) + (deliveryFeeItem(items)?.id === item.id ? discountedDeliveryFee(items) : 0)
    )
  }
  function regularLineAmount(item: BuyerCartItem, items: BuyerCartItem[]) {
    return (
      item.unit_price_yen * item.quantity +
      (deliveryFeeItem(items)?.id === item.id ? deliveryFee(item) : 0)
    )
  }
  function discountedFee(item: BuyerCartItem) {
    return Math.round((deliveryFee(item) * (10_000 - item.discount_bps)) / 10_000)
  }
  return {
    router,
    cartQuery,
    updateCartItemMutation,
    removeCartItemMutation,
    cartItems,
    isDeliveryFeeKnown,
    hasPendingGuestItems,
    deliveryPrefecture,
    producerGroups,
    decreaseQuantity,
    increaseQuantity,
    removeLine,
    openSearch,
    goHome,
    proceedToOrderConfirmation,
    retryGuestCartMerge,
    formatYen,
    unitPrice,
    lineTotal,
    deliveryFee,
    deliveryFeeItem,
    discountedDeliveryFee,
    shopSubtotal,
    lineAmount,
    regularLineAmount,
    discountedFee,
  }
}
