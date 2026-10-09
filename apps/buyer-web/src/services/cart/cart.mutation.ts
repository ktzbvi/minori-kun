import axios from 'axios'
import { useMutation } from '@tanstack/vue-query'
import { currentSessionQuery } from '@/services/auth/auth.query'
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
import { cacheBuyerCart, projectGuestCart, type BuyerCart } from './cart.query'
import { buyerCartKeys } from './cart.key'

type AddBuyerCartItemInput = {
  variantId: string
  quantity: number
}

type UpdateBuyerCartItemInput = {
  itemId: string
  quantity: number
}

async function addGuestCartVariant(variantId: string, quantity: number) {
  const products = await fetchBuyerCatalogueProductsByVariants([
    variantId,
    ...readGuestCart().map((item) => item.variant_id),
  ])
  const variant = products
    .flatMap((product) => product.variants)
    .find((item) => item.id === variantId)
  if (!variant) throw new Error('This product is unavailable.')

  addGuestCartItem(variantId, quantity, variant.stock_quantity)
  return projectGuestCart(products)
}

async function prepareCartMutation() {
  await appQueryClient.cancelQueries({ queryKey: buyerCartKeys.all() })
  const session = await appQueryClient.fetchQuery(currentSessionQuery)
  return { buyerId: session?.id ?? null }
}

function cacheCartMutation(
  cart: BuyerCart,
  _variables: unknown,
  context?: { buyerId: string | null },
) {
  const session = appQueryClient.getQueryData<{ id: string } | null>(currentSessionQuery.queryKey)
  if (cart.id === 'guest' ? !session : context?.buyerId === session?.id) cacheBuyerCart(cart)
}

function recoverCartMutation() {
  return appQueryClient.invalidateQueries({ queryKey: buyerCartKeys.all() })
}

export function useAddBuyerCartItemMutation() {
  return useMutation({
    onMutate: prepareCartMutation,
    mutationFn: async ({ variantId, quantity }: AddBuyerCartItemInput) => {
      try {
        const session = await appQueryClient.fetchQuery(currentSessionQuery)
        if (!session) return addGuestCartVariant(variantId, quantity)
        return (
          await api.post<{ data: BuyerCart }>('/api/v1/buyer/cart/items', {
            variant_id: variantId,
            quantity,
          })
        ).data.data
      } catch (error) {
        if (!axios.isAxiosError(error) || error.response?.status !== 401) throw error

        appQueryClient.setQueryData(currentSessionQuery.queryKey, null)
        return addGuestCartVariant(variantId, quantity)
      }
    },
    onSuccess: cacheCartMutation,
    onError: recoverCartMutation,
  })
}

export function useUpdateBuyerCartItemMutation() {
  return useMutation({
    onMutate: prepareCartMutation,
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
    onSuccess: cacheCartMutation,
    onError: recoverCartMutation,
  })
}

export function useRemoveBuyerCartItemMutation() {
  return useMutation({
    onMutate: prepareCartMutation,
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
    onSuccess: cacheCartMutation,
    onError: recoverCartMutation,
  })
}

export async function mergeGuestCartAfterAuthentication() {
  if (!readGuestCart().length) return { cart: undefined, failedCount: 0 }
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
