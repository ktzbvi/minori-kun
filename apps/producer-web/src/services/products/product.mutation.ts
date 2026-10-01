import { useMutation, useQueryClient } from '@tanstack/vue-query'
import api from '@/services/api'
import type { ProducerProductSaveResponse } from '@/types/product'
import { producerDashboardKeys } from '@/services/dashboard/dashboard.key'
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
      const response = (await api.post<ProducerProductSaveResponse>(url, formData)).data
      return { product: response.data, rejectedImages: response.meta.rejected_images }
    },
    onSuccess: async ({ product }) => {
      queryClient.setQueryData(producerProductKeys.detail(product.id), product)
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: producerProductKeys.all() }),
        queryClient.invalidateQueries({ queryKey: producerDashboardKeys.all() }),
      ])
    },
  })
}
