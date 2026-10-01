import api from '@/services/api'
import type { ProducerProductListFilters, ProducerProductListResponse } from '@/types/product'
import { producerProductKeys } from './product.key'

export function producerProductsQuery(filters: ProducerProductListFilters) {
  return {
    queryKey: producerProductKeys.list(filters),
    queryFn: async () => {
      const params = {
        keyword: filters.keyword || undefined,
        category: filters.category && filters.category !== 'all' ? filters.category : undefined,
        publication_state: filters.publication_state && filters.publication_state !== 'all' ? filters.publication_state : undefined,
        stock_state: filters.stock_state && filters.stock_state !== 'all' ? filters.stock_state : undefined,
      }

      return (await api.get<ProducerProductListResponse>('/api/v1/producer/products', { params })).data
    },
    staleTime: 0,
  }
}
