import axios from 'axios'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import api from '@/services/api'
import { queryClient as appQueryClient } from '@/lib/query'
import {
  addGuestCartItem,
  readGuestCart,
  removeGuestCartItem,
  setGuestCartMergeTarget,
  updateGuestCartItem,
} from '@/lib/guest-cart'
import { fetchBuyerCatalogueProductsByVariants } from '@/services/catalog/catalog.query'
import { projectGuestCart, type BuyerCart } from './cart.query'
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
    mutationFn: async ({ variantId, quantity }: AddBuyerCartItemInput) => {
      try {
        return (
          await api.post<{ data: BuyerCart }>('/api/v1/buyer/cart/items', {
            variant_id: variantId,
            quantity,
          })
        ).data.data
      } catch (error) {
        if (!axios.isAxiosError(error) || error.response?.status !== 401) throw error

        const products = await fetchBuyerCatalogueProductsByVariants([
          variantId,
          ...readGuestCart().map((item) => item.variant_id),
        ])
        const variant = products
          .flatMap((product) => product.variants)
          .find((item) => item.id === variantId)
        if (!variant) throw error

        addGuestCartItem(variantId, quantity, variant.stock_quantity)
        return projectGuestCart(products)
      }
    },
    onSuccess: (cart) => queryClient.setQueryData(buyerCartKeys.current(), cart),
  })
}

export function useUpdateBuyerCartItemMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ itemId, quantity }: UpdateBuyerCartItemInput) => {
      if (itemId.startsWith('guest:')) {
        updateGuestCartItem(itemId.slice('guest:'.length), quantity)
        const products = await fetchBuyerCatalogueProductsByVariants(
          readGuestCart().map((item) => item.variant_id),
        )
        const cachedCart = appQueryClient.getQueryData<BuyerCart>(buyerCartKeys.current())
        const cart = cachedCart && {
          ...cachedCart,
          items: cachedCart.items.filter((item) => !item.id.startsWith('guest:')),
        }
        return projectGuestCart(products, {
          serverCart: cart?.id === 'guest' ? undefined : cart,
          pendingMerge: cart?.id !== 'guest' && Boolean(cart),
        })
      }

      return (
        await api.patch<{ data: BuyerCart }>(`/api/v1/buyer/cart/items/${itemId}`, { quantity })
      ).data.data
    },
    onSuccess: (cart) => queryClient.setQueryData(buyerCartKeys.current(), cart),
  })
}

export function useRemoveBuyerCartItemMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (itemId: string) => {
      if (itemId.startsWith('guest:')) {
        removeGuestCartItem(itemId.slice('guest:'.length))
        const products = await fetchBuyerCatalogueProductsByVariants(
          readGuestCart().map((item) => item.variant_id),
        )
        const cachedCart = appQueryClient.getQueryData<BuyerCart>(buyerCartKeys.current())
        const cart = cachedCart && {
          ...cachedCart,
          items: cachedCart.items.filter((item) => !item.id.startsWith('guest:')),
        }
        return projectGuestCart(products, {
          serverCart: cart?.id === 'guest' ? undefined : cart,
          pendingMerge: cart?.id !== 'guest' && Boolean(cart),
        })
      }

      return (await api.delete<{ data: BuyerCart }>(`/api/v1/buyer/cart/items/${itemId}`)).data.data
    },
    onSuccess: (cart) => queryClient.setQueryData(buyerCartKeys.current(), cart),
  })
}

export async function mergeGuestCartAfterAuthentication() {
  let cart = (await api.get<{ data: BuyerCart }>('/api/v1/buyer/cart')).data.data
  let failedCount = 0

  for (const guestItem of readGuestCart()) {
    const existing = cart.items.find((item) => item.variant_id === guestItem.variant_id)
    const targetQuantity = guestItem.merge_target ?? (existing?.quantity ?? 0) + guestItem.quantity

    if (guestItem.merge_target === undefined) {
      setGuestCartMergeTarget(guestItem.variant_id, targetQuantity)
    }

    if ((existing?.quantity ?? 0) >= targetQuantity) {
      removeGuestCartItem(guestItem.variant_id)
      continue
    }

    try {
      const response = existing
        ? await api.patch<{ data: BuyerCart }>(`/api/v1/buyer/cart/items/${existing.id}`, {
            quantity: targetQuantity,
          })
        : await api.post<{ data: BuyerCart }>('/api/v1/buyer/cart/items', {
            variant_id: guestItem.variant_id,
            quantity: targetQuantity,
          })
      cart = response.data.data
      removeGuestCartItem(guestItem.variant_id)
    } catch {
      failedCount += 1
    }
  }

  return { cart, failedCount }
}
