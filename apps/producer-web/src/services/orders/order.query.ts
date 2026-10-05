import { computed, toValue, type MaybeRefOrGetter } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import api from '@/services/api'
import type { ProducerOrderListFilters, ProducerOrderListResponse, ProducerOrderShowResponse } from '@/types/order'
import { producerOrderKeys } from './order.key'

export function useProducerOrdersQuery(filters: MaybeRefOrGetter<ProducerOrderListFilters>) {
  return useQuery(computed(() => {
    const currentFilters = toValue(filters)
    return {
      queryKey: producerOrderKeys.list(currentFilters),
      queryFn: async ({ signal }: { signal: AbortSignal }) => (await api.get<ProducerOrderListResponse>('/api/v1/producer/orders', {
        params: currentFilters, signal,
      })).data,
      refetchInterval: 30_000,
      refetchOnWindowFocus: true,
    }
  }))
}

export function useProducerOrderQuery(id: MaybeRefOrGetter<string>) {
  return useQuery(computed(() => {
    const currentId = toValue(id)
    return {
      queryKey: producerOrderKeys.detail(currentId),
      queryFn: async ({ signal }: { signal: AbortSignal }) => (await api.get<ProducerOrderShowResponse>(
        `/api/v1/producer/orders/${currentId}`, { signal },
      )).data.data,
      refetchInterval: 30_000,
      refetchOnWindowFocus: true,
    }
  }))
}
