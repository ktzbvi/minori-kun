import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { checkoutDeliveryAddress, initializeCheckoutDeliveryAddress } from '@/lib/checkout'
import { queryClient } from '@/lib/query'
import { getBuyerAccountProfile } from '@/services/account/account.api'
import { buyerCartKeys } from '@/services/cart/cart.key'
import { useBuyerCartQuery } from '@/services/cart/cart.query'
import { createBuyerOrder } from '@/services/orders/orders.api'
import { usePaymentModeQuery } from '@/services/orders/orders.query'

export function useBuyerPayment() {
  const route = useRoute()
  const router = useRouter()
  const cart = useBuyerCartQuery()
  const paymentMode = usePaymentModeQuery()
  const submitting = ref(false)
  const producerId = computed(() =>
    typeof route.query.producer === 'string' ? route.query.producer : '',
  )
  const lines = computed(() =>
    (cart.data.value?.items ?? []).filter((item) => item.producer_id === producerId.value),
  )
  const shopName = computed(() => lines.value[0]?.shop_name ?? '')
  const idempotencyKey = ref(crypto.randomUUID())
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
  function itemTotal(item: (typeof lines.value)[number]) {
    return Math.round((item.unit_price_yen * (10000 - item.discount_bps)) / 10000) * item.quantity
  }
  function deliveryFee(item: (typeof lines.value)[number]) {
    const base =
      checkoutDeliveryAddress.prefecture === '北海道'
        ? item.delivery_fee_hokkaido_yen
        : checkoutDeliveryAddress.prefecture === '沖縄県'
          ? item.delivery_fee_okinawa_yen
          : item.delivery_fee_honshu_yen
    return Math.round((base * (10000 - item.discount_bps)) / 10000)
  }
  const totalYen = computed(
    () =>
      lines.value.reduce((sum, item) => sum + itemTotal(item), 0) +
      Math.max(0, ...lines.value.map(deliveryFee)),
  )
  async function completeFakePayment() {
    if (!producerId.value || !lines.value.length || submitting.value) return
    submitting.value = true
    try {
      const order = await createBuyerOrder({
        producer_id: producerId.value,
        idempotency_key: idempotencyKey.value,
        delivery_address: {
          name: checkoutDeliveryAddress.name,
          phone: checkoutDeliveryAddress.phone,
          postal_code: checkoutDeliveryAddress.postalCode,
          prefecture: checkoutDeliveryAddress.prefecture,
          city: checkoutDeliveryAddress.city,
          address_line1: checkoutDeliveryAddress.addressLine1,
          address_line2: checkoutDeliveryAddress.addressLine2,
        },
      })
      await queryClient.invalidateQueries({ queryKey: buyerCartKeys.all() })
      await router.replace({ name: 'order-complete', params: { orderId: order.id } })
    } catch {
      toast.error('決済を完了できませんでした。カートの内容と在庫をご確認ください。')
      idempotencyKey.value = crypto.randomUUID()
    } finally {
      submitting.value = false
    }
  }
  return {
    route,
    router,
    cart,
    paymentMode,
    submitting,
    producerId,
    lines,
    shopName,
    idempotencyKey,
    itemTotal,
    deliveryFee,
    totalYen,
    completeFakePayment,
  }
}
