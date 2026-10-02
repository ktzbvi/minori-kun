import api from '@/services/api'

export type OrderPreviewItem = {
  product_name: string
  quantity: number
  image_url: string | null
}

export type BuyerOrderSummary = {
  id: string
  order_number: string
  placed_at: string
  cancellation_deadline_at: string
  order_state: string
  payment_state: string
  refund_state: string
  total_yen: number
  shop_name: string | null
  items: OrderPreviewItem[]
}

export type BuyerOrder = BuyerOrderSummary & {
  subtotal_yen: number
  discount_yen: number
  shipping_yen: number
  cancellation_deadline_at: string
  can_cancel: boolean
  delivery_address: {
    recipient_name: string
    phone: string
    postal_code: string
    prefecture: string
    city: string
    address_line1: string
    address_line2: string | null
  }
  producer_orders: Array<{
    producer_id: string
    shop_name: string | null
    fulfillment_state: string
    items: Array<
      OrderPreviewItem & {
        id: string
        variant_label: string
        unit_price_yen: number
        discount_bps: number
        discount_yen: number
        line_total_yen: number
      }
    >
  }>
}

export type CheckoutAddress = {
  name: string
  phone: string
  postal_code: string
  prefecture: string
  city: string
  address_line1: string
  address_line2?: string
}

export async function getPaymentMode() {
  return (await api.get<{ data: { fake_enabled: boolean } }>('/api/v1/buyer/payment-mode')).data
    .data
}

export async function createBuyerOrder(values: {
  producer_id: string
  idempotency_key: string
  delivery_address: CheckoutAddress
}) {
  return (await api.post<{ data: BuyerOrder }>('/api/v1/buyer/orders/checkout', values)).data.data
}

export async function getBuyerOrders(filters: Record<string, string>) {
  return (
    await api.get<{ data: { data: BuyerOrderSummary[] } }>('/api/v1/buyer/orders', {
      params: filters,
    })
  ).data.data.data
}

export async function getBuyerOrder(id: string) {
  return (await api.get<{ data: BuyerOrder }>(`/api/v1/buyer/orders/${id}`)).data.data
}

export async function downloadBuyerReceipt(id: string, orderNumber: string) {
  const { data } = await api.get<Blob>(`/api/v1/buyer/orders/${id}/receipt`, {
    responseType: 'blob',
    headers: { Accept: 'application/pdf' },
  })
  const url = URL.createObjectURL(data)
  const link = document.createElement('a')
  link.href = url
  link.download = `receipt-${orderNumber}.pdf`
  link.click()
  window.setTimeout(() => URL.revokeObjectURL(url), 30_000)
}

export async function cancelBuyerOrder(id: string, idempotencyKey: string) {
  return (
    await api.post<{ data: BuyerOrder }>(`/api/v1/buyer/orders/${id}/cancel`, {
      idempotency_key: idempotencyKey,
    })
  ).data.data
}
