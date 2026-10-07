import { useMutation, useQueryClient } from '@tanstack/vue-query'
import type { CurrentSession, ProducerLoginCredentials } from '@/types/auth'
import api from '../api'
import { producerAuthKeys } from './auth.key'

export function useProducerLoginMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    gcTime: 0,
    mutationFn: async ({ email, password }: ProducerLoginCredentials): Promise<CurrentSession> => {
      const response = await api.post<{ data: CurrentSession }>('/api/v1/producer/auth/login', {
        email,
        password,
      })
      return response.data.data
    },
    onSuccess: async (session) => {
      queryClient.setQueryData(producerAuthKeys.currentSession(), session)
    },
  })
}

export function useProducerLogoutMutation() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async () => { await api.post('/api/v1/producer/auth/logout') },
    onSuccess: async () => {
      await queryClient.cancelQueries()
      queryClient.clear()
    },
  })
}