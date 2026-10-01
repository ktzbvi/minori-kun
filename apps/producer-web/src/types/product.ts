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
