import axios from 'axios'
import { useQuery } from '@tanstack/vue-query'
import type { CurrentSession } from '@/types/auth'
import api from '../api'
import { buyerAuthKeys } from './auth.key'

export const currentSessionQuery = {
  queryKey: buyerAuthKeys.currentSession(),
  queryFn: async ({ signal }: { signal: AbortSignal }): Promise<CurrentSession | null> => {
    try {
      return (await api.get<{ data: CurrentSession }>('/api/v1/buyer/me', { signal })).data.data
    } catch (error) {
      if (axios.isAxiosError(error) && [401, 404].includes(error.response?.status ?? 0)) return null
      throw error
    }
  },
}

export function useBuyerSessionQuery() {
  return useQuery(currentSessionQuery)
}
