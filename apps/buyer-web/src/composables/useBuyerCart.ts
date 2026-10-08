import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import {
  useRemoveBuyerCartItemMutation,
  useUpdateBuyerCartItemMutation,
} from '@/services/cart/cart.mutation'
import { useBuyerCartQuery, type BuyerCartItem } from '@/services/cart/cart.query'
import { getBuyerAccountProfile } from '@/services/account/account.api'

export function useBuyerCart() {
  const router = useRouter()
  const cartQuery = useBuyerCartQuery()
  const updateCartItemMutation = useUpdateBuyerCartItemMutation()
  const removeCartItemMutation = useRemoveBuyerCartItemMutation()
  const cartItems = computed(() => cartQuery.data.value?.items ?? [])
  const deliveryPrefecture = ref('')
  onMounted(async () => {
    try {
      deliveryPrefecture.value = (await getBuyerAccountProfile()).prefecture
    } catch {
      // The protected Cart route redirects to login before this can affect checkout.
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
    }))
  })
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
      onSuccess: () => toast.error('カートから削除しました'),
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
    void router.push({ name: 'order-confirmation', query: { producer: producerId } })
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
    deliveryPrefecture,
    producerGroups,
    decreaseQuantity,
    increaseQuantity,
    removeLine,
    openSearch,
    goHome,
    proceedToOrderConfirmation,
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
