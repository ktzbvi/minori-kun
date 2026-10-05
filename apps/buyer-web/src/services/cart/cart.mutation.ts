import { useMutation, useQueryClient } from '@tanstack/vue-query'
import api from '@/services/api'
import type { BuyerCart } from './cart.query'
import { buyerCartKeys } from './cart.key'

type AddBuyerCartItemInput = {
  variantId: string
  quantity: number
}

type UpdateBuyerCartItemInput = {
  itemId: string
  quantity: number
}

export function useAddBuyerCartItemMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ variantId, quantity }: AddBuyerCartItemInput) =>
      (
        await api.post<{ data: BuyerCart }>('/api/v1/buyer/cart/items', {
          variant_id: variantId,
          quantity,
        })
      ).data.data,
    onSuccess: (cart) => queryClient.setQueryData(buyerCartKeys.current(), cart),
  })
}

export function useUpdateBuyerCartItemMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ itemId, quantity }: UpdateBuyerCartItemInput) =>
      (await api.patch<{ data: BuyerCart }>(`/api/v1/buyer/cart/items/${itemId}`, { quantity }))
        .data.data,
    onSuccess: (cart) => queryClient.setQueryData(buyerCartKeys.current(), cart),
  })
}

export function useRemoveBuyerCartItemMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (itemId: string) =>
      (await api.delete<{ data: BuyerCart }>(`/api/v1/buyer/cart/items/${itemId}`)).data.data,
    onSuccess: (cart) => queryClient.setQueryData(buyerCartKeys.current(), cart),
  })
}
