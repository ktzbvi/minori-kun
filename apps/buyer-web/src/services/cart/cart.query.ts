import { useQuery } from '@tanstack/vue-query'
import api from '@/services/api'
import { buyerCartKeys } from './cart.key'

export type BuyerCartItem = {
  id: string
  quantity: number
  variant_id: string
  variant_label: string
  stock_quantity: number
  discount_bps: number
  product_id: string
  product_name: string
  producer_id: string
  shop_name: string | null
  delivery_fee_honshu_yen: number
  delivery_fee_hokkaido_yen: number
  delivery_fee_okinawa_yen: number
  image_url: string | null
  unit_price_yen: number
}

export type BuyerCart = {
  id: string
  items: BuyerCartItem[]
}

export function useBuyerCartQuery() {
  return useQuery({
    queryKey: buyerCartKeys.current(),
    queryFn: async () => (await api.get<{ data: BuyerCart }>('/api/v1/buyer/cart')).data.data,
  })
}
