import { producerAuthKeys } from './auth.key'
import { producerAuthApi } from '../api'

export const currentSessionQuery = {
  queryKey: producerAuthKeys.currentSession(),
  queryFn: async () => (await producerAuthApi.me()).data.data,
}
