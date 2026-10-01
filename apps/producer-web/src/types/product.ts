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

type GeneratedProducerProductDetail = components['schemas']['ProducerProductResource']

export interface ProducerProductImage {
  id: string
  url: string
  display_order: number
}

export type ProducerProductDetail = Omit<
  GeneratedProducerProductDetail,
  'images' | 'publication_state'
> & {
  publication_state: ProducerProductPublicationState
  images: ProducerProductImage[]
}

export type ProducerProductCategory = Pick<components['schemas']['Category'], 'id' | 'name'>

type GeneratedProducerProductFormOptions =
  operations['producerProduct.options']['responses'][200]['content']['application/json']['data']

export type ProducerProductFormOptions = Omit<GeneratedProducerProductFormOptions, 'categories'> & {
  categories: ProducerProductCategory[]
}

type GeneratedProducerProductOptionsResponse =
  operations['producerProduct.options']['responses'][200]['content']['application/json']

export type ProducerProductOptionsResponse = Omit<GeneratedProducerProductOptionsResponse, 'data'> & {
  data: ProducerProductFormOptions
}

type GeneratedProducerProductShowResponse =
  operations['producerProduct.show']['responses'][200]['content']['application/json']

export type ProducerProductShowResponse = Omit<GeneratedProducerProductShowResponse, 'data'> & {
  data: ProducerProductDetail
}

type GeneratedProducerProductSaveResponse =
  operations['producerProduct.update']['responses'][200]['content']['application/json']

export type ProducerProductSaveResponse = Omit<GeneratedProducerProductSaveResponse, 'data'> & {
  data: ProducerProductDetail
}
