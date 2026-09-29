import { producerAuthKeys } from './auth.key'
import type { CurrentSession } from '@/types/auth'
import api from '../api'

export const currentSessionQuery = {
  queryKey: producerAuthKeys.currentSession(),
  queryFn: async () => (await api.get<{ data: CurrentSession }>('/api/v1/producer/me')).data.data,
}
