import type { components, operations } from '@minorikun/api-contracts'

type ListOperation = operations['producerOrder.index']
export type ProducerOrderListFilters = NonNullable<ListOperation['parameters']['query']>
export interface ProducerOrderFilterValues {
  keyword: string
  status: NonNullable<ProducerOrderListFilters['status']>
  period: NonNullable<ProducerOrderListFilters['period']>
  year: string
  from: string
  to: string
}
export type ProducerOrderListResponse = ListOperation['responses'][200]['content']['application/json']
export type ProducerOrderListItem = components['schemas']['ProducerOrderListItemResource']
export type ProducerOrderShowResponse = operations['producerOrder.show']['responses'][200]['content']['application/json']
