import { queryOptions, useQuery } from '@tanstack/vue-query'
import { producerAuthKeys } from './auth.key'
import type { CurrentSession } from '@/types/auth'
import api from '../api'

export function currentSessionQueryOptions() {
  return queryOptions({
    queryKey: producerAuthKeys.currentSession(),
    queryFn: async () => {
      return (await api.get<{ data: CurrentSession }>('/api/v1/producer/me')).data.data
    },
  })
}

export function useCurrentSessionQuery() {
  return useQuery(currentSessionQueryOptions())
}
