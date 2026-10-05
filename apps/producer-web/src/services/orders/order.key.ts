import type { ProducerOrderListFilters } from '@/types/order'
import { producerKeys } from '@/services/producer.key'

export const producerOrderKeys = {
  all: () => [...producerKeys.all(), 'orders'] as const,
  list: (filters: ProducerOrderListFilters) => [...producerOrderKeys.all(), 'list', filters] as const,
  detail: (id: string) => [...producerOrderKeys.all(), 'detail', id] as const,
}
