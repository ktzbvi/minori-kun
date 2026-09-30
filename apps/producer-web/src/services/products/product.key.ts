import type { ProducerProductListFilters } from '@/types/product'

export const producerProductKeys = {
  all: () => ['producer-products'] as const,
  list: (filters: ProducerProductListFilters) => [...producerProductKeys.all(), 'list', filters] as const,
}
