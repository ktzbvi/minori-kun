import { useQuery } from '@tanstack/vue-query'
import api from '@/services/api'
import type { ProducerAccountSummary } from '@/types/account'
import { producerAccountKeys } from './account.key'

export function useProducerAccountQuery() {
  return useQuery({
    queryKey: producerAccountKeys.summary(),
    queryFn: async () => (await api.get<{ data: ProducerAccountSummary }>('/api/v1/producer/account')).data.data,
  })
}