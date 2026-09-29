import { useMutation, useQueryClient } from '@tanstack/vue-query'
import api, { ensureCsrfCookie } from '@/services/api'
import type { CurrentSession } from '@/types/auth'
import type {
  ProducerRegistrationCompletion,
  ProducerRegistrationDetails,
  ProducerRegistrationPhoto,
  ProducerRegistrationState,
} from '@/types/registration'
import { producerAuthKeys } from '@/services/auth/auth.key'
import { producerRegistrationKeys } from './registration.key'

export async function recoverCompletedProducerRegistration() {
  await ensureCsrfCookie()
  return (await api.post<{ data: CurrentSession }>('/api/v1/producer/registration/recover')).data.data
}

export function useRequestRegistrationCodeMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (email: string) => {
      await ensureCsrfCookie()
      return (await api.post<{ data: ProducerRegistrationState }>('/api/v1/producer/registration/code', { email })).data.data
    },
    onSuccess: (state) => queryClient.setQueryData(producerRegistrationKeys.status(), state),
  })
}

export function useResendRegistrationCodeMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async () => {
      await ensureCsrfCookie()
      return (await api.post<{ data: ProducerRegistrationState }>('/api/v1/producer/registration/code/resend')).data.data
    },
    onSuccess: (state) => queryClient.setQueryData(producerRegistrationKeys.status(), state),
  })
}

export function useVerifyRegistrationCodeMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    gcTime: 0,
    mutationFn: async (code: string) => {
      await ensureCsrfCookie()
      return (await api.post<{ data: ProducerRegistrationState }>('/api/v1/producer/registration/code/verify', { code })).data.data
    },
    onSuccess: (state) => queryClient.setQueryData(producerRegistrationKeys.status(), state),
  })
}

export function useResetRegistrationMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async () => {
      await ensureCsrfCookie()
      return (await api.delete<{ data: { deleted: boolean } }>('/api/v1/producer/registration')).data.data
    },
    onSuccess: async () => {
      queryClient.removeQueries({ queryKey: producerRegistrationKeys.status() })
      queryClient.removeQueries({ queryKey: producerRegistrationKeys.details() })
    },
  })
}

export function useUploadRegistrationPhotoMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (photo: File) => {
      await ensureCsrfCookie()
      const body = new FormData()
      body.append('photo', photo)
      return (await api.post<{ data: ProducerRegistrationPhoto }>('/api/v1/producer/registration/photo', body)).data.data
    },
    onSuccess: (photo) => {
      queryClient.setQueryData(producerRegistrationKeys.details(), (details: ProducerRegistrationDetails | undefined) =>
        details ? { ...details, photo } : details,
      )
    },
  })
}

export function useDeleteRegistrationPhotoMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (id: string) => {
      await ensureCsrfCookie()
      return (await api.delete<{ data: { deleted: boolean } }>(`/api/v1/producer/registration/photo/${encodeURIComponent(id)}`)).data.data
    },
    onSuccess: (_result, deletedId) => {
      queryClient.setQueryData(producerRegistrationKeys.details(), (details: ProducerRegistrationDetails | undefined) =>
        details?.photo?.id === deletedId ? { ...details, photo: null } : details,
      )
    },
  })
}

export function useCompleteRegistrationMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    gcTime: 0,
    mutationFn: async (payload: ProducerRegistrationCompletion) => {
      await ensureCsrfCookie()
      return (await api.post<{ data: CurrentSession }>('/api/v1/producer/registration/complete', payload)).data.data
    },
    onSuccess: async (session) => {
      queryClient.setQueryData(producerAuthKeys.currentSession(), session)
      queryClient.removeQueries({ queryKey: producerRegistrationKeys.status() })
      queryClient.removeQueries({ queryKey: producerRegistrationKeys.details() })
    },
  })
}
