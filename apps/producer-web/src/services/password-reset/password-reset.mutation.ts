import { useMutation } from '@tanstack/vue-query'
import type {
  ProducerPasswordResetStart,
  ProducerPasswordResetValidation,
  ProducerPasswordResetCompletion,
} from '@/types/password-reset'
import api from '../api'
export function useStartPasswordResetMutation() {
  return useMutation({
    gcTime: 0,
    mutationFn: async (payload: ProducerPasswordResetStart) => {
      await api.post('/api/v1/producer/password-reset/start', payload)
    },
  })
}
export function useValidatePasswordResetMutation() {
  return useMutation({
    gcTime: 0,
    mutationFn: async (payload: ProducerPasswordResetValidation) => {
      await api.post('/api/v1/producer/password-reset/validate', payload)
    },
  })
}
export function useCompletePasswordResetMutation() {
  return useMutation({
    gcTime: 0,
    mutationFn: async (payload: ProducerPasswordResetCompletion) => {
      await api.post('/api/v1/producer/password-reset/complete', payload)
    },
  })
}
