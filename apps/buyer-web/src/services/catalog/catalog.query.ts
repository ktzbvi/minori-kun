import { useQuery } from '@tanstack/vue-query'
import api from '@/services/api'
import { buyerCatalogKeys } from './catalog.key'

export type BuyerCatalogueVariant = {
  id: string
  label: string
  price_yen: number
  stock_quantity: number
  discount_bps: number
}

export type BuyerCatalogueProduct = {
  id: string
  name: string
  description: string
  category: string | null
  producer_id: string
  shop_name: string | null
  image_url: string | null
  variants: BuyerCatalogueVariant[]
}

export function useBuyerCatalogueQuery() {
  return useQuery({
    queryKey: buyerCatalogKeys.products(),
    queryFn: async () =>
      (await api.get<{ data: BuyerCatalogueProduct[] }>('/api/v1/buyer/products')).data.data,
    staleTime: 30_000,
  })
}
