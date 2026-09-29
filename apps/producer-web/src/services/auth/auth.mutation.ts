import { useMutation, useQueryClient } from '@tanstack/vue-query'
import type { CurrentSession } from '@/types/auth'
import api from '../api'
import { producerAuthKeys } from './auth.key'

export type ProducerLoginCredentials = { email: string; password: string }

export function useProducerLoginMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    gcTime: 0,
    mutationFn: async ({ email, password }: ProducerLoginCredentials): Promise<CurrentSession> => {
      await api.get('/sanctum/csrf-cookie')
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
