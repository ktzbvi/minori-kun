import type { CurrentSession } from '@/types/auth'
import api from '../api'
import { buyerAuthKeys } from './auth.key'

export const currentSessionQuery = {
  queryKey: buyerAuthKeys.currentSession(),
  queryFn: async () => (await api.get<{ data: CurrentSession }>('/api/v1/buyer/me')).data.data,
}
