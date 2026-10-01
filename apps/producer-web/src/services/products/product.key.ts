import type { ProducerProductListFilters } from '@/types/product'
import { producerKeys } from '@/services/producer.key'

export const producerProductKeys = {
  all: () => [...producerKeys.all(), 'products'] as const,
  list: (filters: ProducerProductListFilters) => [...producerProductKeys.all(), 'list', filters] as const,
}
