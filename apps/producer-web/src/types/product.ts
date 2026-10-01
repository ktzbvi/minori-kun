import type { components, operations } from '@minorikun/api-contracts'

type ProducerProductListOperation = operations['producerProduct.index']
type GeneratedProducerProductListResponse =
  ProducerProductListOperation['responses'][200]['content']['application/json']

export type ProducerProductListItem =
  components['schemas']['ProducerProductListItemResource']

export type ProducerProductListFilters = NonNullable<
  ProducerProductListOperation['parameters']['query']
>

export type ProducerProductPublicationState = Exclude<
  NonNullable<ProducerProductListFilters['publication_state']>,
  'all'
>

export type ProducerProductStockState = NonNullable<
  ProducerProductListFilters['stock_state']
>

export type ProducerProductListResponse = Omit<
  GeneratedProducerProductListResponse,
  'meta'
> & {
  meta: Omit<GeneratedProducerProductListResponse['meta'], 'categories'> & {
    categories: string[]
  }
}

export interface ProducerProductImage {
  id: string
  url: string
  display_order: number
}

export interface ProducerProductDetail {
  id: string
  name: string
  description: string
  category_id: string
  category_name: string | null
  price_yen: number
  stock_quantity: number
  discount_bps: number
  delivery_fee_honshu_yen: number
  delivery_fee_hokkaido_yen: number
  delivery_fee_okinawa_yen: number
  publication_state: ProducerProductPublicationState
  lock_version: number
  images: ProducerProductImage[]
}

export interface ProducerProductCategory {
  id: string
  name: string
}

export interface ProducerProductFormOptions {
  categories: ProducerProductCategory[]
}
