import { computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { checkoutDeliveryAddress, initializeCheckoutDeliveryAddress } from '@/lib/checkout'
import { getBuyerAccountProfile } from '@/services/account/account.api'
import { useBuyerCartQuery, type BuyerCartItem } from '@/services/cart/cart.query'

export function useBuyerOrderConfirmation() {
  const router = useRouter()
  const route = useRoute()
  const cartQuery = useBuyerCartQuery()
  onMounted(async () => {
    try {
      const profile = await getBuyerAccountProfile()

      initializeCheckoutDeliveryAddress({
        name: profile.name,
        phone: profile.phone,
        postalCode: profile.postal_code,
        prefecture: profile.prefecture,
        city: profile.city,
        addressLine1: profile.address_line1,
        addressLine2: profile.address_line2,
      })
    } catch {
      await router.replace({ name: 'login', query: { redirect: route.fullPath } })
    }
  })
  const producerGroups = computed(() => {
    const grouped = new Map<string, BuyerCartItem[]>()

    for (const item of cartQuery.data.value?.items ?? []) {
      const items = grouped.get(item.producer_id) ?? []
      items.push(item)
      grouped.set(item.producer_id, items)
    }

    return Array.from(grouped, ([producerId, items]) => ({
      producerId,
      shopName: items[0]?.shop_name ?? 'ショップ',
      items,
    }))
  })
  const selectedProducerGroup = computed(() => {
    const selectedProducerId =
      typeof route.query.producer === 'string' ? route.query.producer : undefined

    return (
      producerGroups.value.find((group) => group.producerId === selectedProducerId) ??
      producerGroups.value[0]
    )
  })
  watch(
    () => ({
      data: cartQuery.data.value,
      isFetching: cartQuery.isFetching.value,
      isSuccess: cartQuery.isSuccess.value,
    }),
    ({ data, isFetching, isSuccess }) => {
      if (!isSuccess || isFetching || !data || selectedProducerGroup.value) return

      void router.replace({ name: data.items.length ? 'cart' : 'order-history' })
    },
    { immediate: true },
  )
  const total = computed(() =>
    selectedProducerGroup.value ? shopTotal(selectedProducerGroup.value.items) : 0,
  )
  const canProceedToPayment = computed(() =>
    Boolean(selectedProducerGroup.value && total.value > 0),
  )
  function changeAddress() {
    void router.push({
      name: 'checkout-address',
      query:
        typeof route.query.producer === 'string' ? { producer: route.query.producer } : undefined,
    })
  }
  function proceedToPayment() {
    if (!canProceedToPayment.value) {
      toast.error('カートの商品を確認してください。')
      void router.replace({ name: 'cart' })
      return
    }

    void router.push({
      name: 'buyer-payment',
      query: { producer: selectedProducerGroup.value?.producerId },
    })
  }
  function formatYen(amount: number) {
    return `${amount.toLocaleString('ja-JP')}円`
  }
  function unitPrice(item: BuyerCartItem) {
    return Math.round((item.unit_price_yen * (10_000 - item.discount_bps)) / 10_000)
  }
  function lineTotal(item: BuyerCartItem) {
    return unitPrice(item) * item.quantity
  }
  function regularLineTotal(item: BuyerCartItem) {
    return item.unit_price_yen * item.quantity
  }
  function deliveryFee(item: BuyerCartItem) {
    if (checkoutDeliveryAddress.prefecture === '北海道') {
      return item.delivery_fee_hokkaido_yen
    }

    if (checkoutDeliveryAddress.prefecture === '沖縄県') {
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
  function shopTotal(items: BuyerCartItem[]) {
    return items.reduce((total, item) => total + lineTotal(item), 0) + discountedDeliveryFee(items)
  }
  function lineAmount(item: BuyerCartItem, items: BuyerCartItem[]) {
    return (
      lineTotal(item) + (deliveryFeeItem(items)?.id === item.id ? discountedDeliveryFee(items) : 0)
    )
  }
  function regularLineAmount(item: BuyerCartItem, items: BuyerCartItem[]) {
    return regularLineTotal(item) + (deliveryFeeItem(items)?.id === item.id ? deliveryFee(item) : 0)
  }
  function discountedFee(item: BuyerCartItem) {
    return Math.round((deliveryFee(item) * (10_000 - item.discount_bps)) / 10_000)
  }
  return {
    router,
    route,
    cartQuery,
    producerGroups,
    selectedProducerGroup,
    total,
    canProceedToPayment,
    changeAddress,
    proceedToPayment,
    formatYen,
    unitPrice,
    lineTotal,
    regularLineTotal,
    deliveryFee,
    deliveryFeeItem,
    discountedDeliveryFee,
    shopTotal,
    lineAmount,
    regularLineAmount,
    discountedFee,
  }
}
