import { useMutation, useQueryClient } from '@tanstack/vue-query'
import type { CurrentSession } from '@minorikun/api-client'
import { producerAuthApi } from '../api'
import { producerAuthKeys } from './auth.key'

export type ProducerLoginCredentials = { email: string; password: string }

export function useProducerLoginMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ email, password }: ProducerLoginCredentials): Promise<CurrentSession> => {
      await producerAuthApi.csrf()
      const response = await producerAuthApi.login(email, password)
      return response.data.data
    },
    onSuccess: async (session) => {
      queryClient.setQueryData(producerAuthKeys.currentSession(), session)
    },
  })
}
