import { useMutation, useQueryClient } from '@tanstack/vue-query'
import api from '@/services/api'
import type { ProducerProductDetail } from '@/types/product'
import { producerProductKeys } from './product.key'

export interface SaveProducerProductInput {
  id?: string
  formData: FormData
}

export function useSaveProducerProductMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ id, formData }: SaveProducerProductInput) => {
      const url = id ? `/api/v1/producer/products/${id}` : '/api/v1/producer/products'
      return (await api.post<{ data: ProducerProductDetail }>(url, formData)).data.data
    },
    onSuccess: async (product) => {
      queryClient.setQueryData(producerProductKeys.detail(product.id), product)
      await queryClient.invalidateQueries({ queryKey: producerProductKeys.all() })
    },
  })
}
