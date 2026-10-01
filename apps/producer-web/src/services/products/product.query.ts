import { computed, toValue, type MaybeRefOrGetter } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import api from '@/services/api'
import type {
  ProducerProductListFilters,
  ProducerProductListResponse,
  ProducerProductOptionsResponse,
  ProducerProductShowResponse,
} from '@/types/product'
import { producerProductKeys } from './product.key'

export function useProducerProductsQuery(filters: MaybeRefOrGetter<ProducerProductListFilters>) {
  return useQuery(
    computed(() => {
      const currentFilters = toValue(filters)

      return {
        queryKey: producerProductKeys.list(currentFilters),
        queryFn: async () => {
          const params = {
            keyword: currentFilters.keyword || undefined,
            category: currentFilters.category && currentFilters.category !== 'all'
              ? currentFilters.category
              : undefined,
            publication_state: currentFilters.publication_state && currentFilters.publication_state !== 'all'
              ? currentFilters.publication_state
              : undefined,
            stock_state: currentFilters.stock_state && currentFilters.stock_state !== 'all'
              ? currentFilters.stock_state
              : undefined,
          }

          return (await api.get<ProducerProductListResponse>('/api/v1/producer/products', { params })).data
        },
      }
    }),
  )
}

export function useProducerProductOptionsQuery() {
  return useQuery({
    queryKey: producerProductKeys.options(),
    queryFn: async () => (await api.get<ProducerProductOptionsResponse>('/api/v1/producer/products/options')).data.data,
    staleTime: 5 * 60 * 1000,
  })
}

export function useProducerProductQuery(id: MaybeRefOrGetter<string>, enabled: MaybeRefOrGetter<boolean>) {
  return useQuery(computed(() => ({
    queryKey: producerProductKeys.detail(toValue(id)),
    queryFn: async () => (await api.get<ProducerProductShowResponse>(`/api/v1/producer/products/${toValue(id)}`)).data.data,
    enabled: toValue(enabled),
  })))
}
