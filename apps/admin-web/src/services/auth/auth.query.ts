import { adminAuthApi } from '@/services/api'
import { adminAuthKeys } from './auth.key'

export const currentSessionQuery = {
  queryKey: adminAuthKeys.currentSession(),
  queryFn: async () => (await adminAuthApi.me()).data.data,
}
