import { useQuery } from '@tanstack/vue-query'
import api from '@/services/api'
import type { ProducerDashboardResponse } from '@/types/dashboard'
import { producerDashboardKeys } from './dashboard.key'

export function useProducerDashboardQuery() {
  return useQuery<ProducerDashboardResponse>({
    queryKey: producerDashboardKeys.detail(),
    queryFn: async () => {
      return (await api.get<ProducerDashboardResponse>('/api/v1/producer/dashboard')).data
    },
  })
}
