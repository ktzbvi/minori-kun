import api from '@/services/api'
import type { ProducerDashboardResponse } from '@/types/dashboard'
import { producerDashboardKeys } from './dashboard.key'

export const producerDashboardQuery = {
  queryKey: producerDashboardKeys.detail(),
  queryFn: async () => (await api.get<ProducerDashboardResponse>('/api/v1/producer/dashboard')).data,
  staleTime: 0,
}
