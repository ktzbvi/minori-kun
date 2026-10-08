import { useMutation, useQueryClient } from '@tanstack/vue-query'
import api from '@/services/api'
import type { ProducerAccountSummary } from '@/types/account'
import { producerAccountKeys } from './account.key'

export function useUpdateShopPhotoMutation() {
  const queryClient = useQueryClient()
  return useMutation({
    gcTime: 0,
    mutationFn: async ({ photo, expectedPhotoId }: { photo: File; expectedPhotoId: string | null }) => {
      const body = new FormData()
      body.append('photo', photo)
      body.append('expected_photo_id', expectedPhotoId ?? '')
      return (await api.post<{ data: ProducerAccountSummary }>('/api/v1/producer/account/photo', body)).data.data
    },
    onSuccess: data => queryClient.setQueryData(producerAccountKeys.summary(), data),
  })
}