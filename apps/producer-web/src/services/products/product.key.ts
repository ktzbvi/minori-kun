import type { ProducerProductListFilters } from '@/types/product'
import { producerKeys } from '@/services/producer.key'

export const producerProductKeys = {
  all: () => [...producerKeys.all(), 'products'] as const,
  options: () => [...producerProductKeys.all(), 'options'] as const,
  list: (filters: ProducerProductListFilters) => [...producerProductKeys.all(), 'list', filters] as const,
  detail: (id: string) => [...producerProductKeys.all(), 'detail', id] as const,
}
