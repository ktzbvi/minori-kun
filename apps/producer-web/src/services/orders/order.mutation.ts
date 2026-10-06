import { useMutation, useQueryClient } from '@tanstack/vue-query'
import api from '@/services/api'
import type { ProducerOrderShowResponse, ProducerFulfillmentInput } from '@/types/order'
import { producerOrderKeys } from './order.key'
import { producerDashboardKeys } from '@/services/dashboard/dashboard.key'

export function useUpdateProducerFulfillmentMutation() {
  const client = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, ...input }: ProducerFulfillmentInput & { id: string }) =>
      (await api.patch<ProducerOrderShowResponse>(`/api/v1/producer/orders/${id}/fulfillment`, input)).data.data,
    onSuccess: async (order) => {
      client.setQueryData(producerOrderKeys.detail(order.id), order)
      await Promise.all([
        client.invalidateQueries({ queryKey: producerOrderKeys.all() }),
        client.invalidateQueries({ queryKey: producerDashboardKeys.all() }),
      ])
    },
  })
}
