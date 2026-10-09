import axios from 'axios'
import { computed, type Ref } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import type { components } from '@minorikun/api-contracts'
import type { CurrentSession } from '@/types/auth'
import { queryClient } from '@/lib/query'
import { currentSessionQuery } from '@/services/auth/auth.query'
import api from '@/services/api'
import {
  readGuestCart,
  removeGuestCartItem,
  setGuestCartMergeTarget,
  type GuestCartLine,
} from '@/lib/guest-cart'
import {
  fetchBuyerCatalogueProductsByVariants,
  type BuyerCatalogueProduct,
} from '@/services/catalog/catalog.query'
import { buyerCartKeys } from './cart.key'

export type BuyerCartItem = {
  id: string
  quantity: number
  variant_id: string
  variant_label: string
  stock_quantity: number
  discount_bps: number
  product_id: string
  product_name: string
  producer_id: string
  shop_name: string | null
  delivery_fee_honshu_yen: number
  delivery_fee_hokkaido_yen: number
  delivery_fee_okinawa_yen: number
  image_url: string | null
  unit_price_yen: number
  pending_merge?: boolean
  unavailable?: boolean
}

export type BuyerCart = {
  id: string
  items: BuyerCartItem[]
}

export function projectGuestCart(
  products: BuyerCatalogueProduct[],
  options: { serverCart?: BuyerCart; pendingMerge?: boolean } = {},
): BuyerCart {
  const guestItems = readGuestCart()
  const serverCart = options.serverCart
  const pendingItems: BuyerCartItem[] = []

  for (const guestItem of guestItems) {
    const serverItem = serverCart?.items.find((item) => item.variant_id === guestItem.variant_id)
    let quantity = guestItem.quantity

    if (options.pendingMerge) {
      const mergeTarget = guestItem.merge_target ?? (serverItem?.quantity ?? 0) + quantity
      if (guestItem.merge_target === undefined) {
        setGuestCartMergeTarget(guestItem.variant_id, mergeTarget)
      }
      quantity = Math.max(0, mergeTarget - (serverItem?.quantity ?? 0))
      if (!quantity) {
        removeGuestCartItem(guestItem.variant_id)
        continue
      }
    }

    const product = products.find((item) =>
      item.variants.some((variant) => variant.id === guestItem.variant_id),
    )
    const variant = product?.variants.find((item) => item.id === guestItem.variant_id)

    if (!product || !variant) {
      pendingItems.push({
        id: `guest:${guestItem.variant_id}`,
        quantity,
        variant_id: guestItem.variant_id,
        variant_label: '',
        stock_quantity: 0,
        discount_bps: 0,
        product_id: '',
        product_name: '販売終了または在庫切れの商品',
        producer_id: 'unavailable',
        shop_name: '購入できない商品',
        delivery_fee_honshu_yen: 0,
        delivery_fee_hokkaido_yen: 0,
        delivery_fee_okinawa_yen: 0,
        image_url: null,
        unit_price_yen: 0,
        pending_merge: options.pendingMerge,
        unavailable: true,
      })
      continue
    }

    const availableStock = options.pendingMerge
      ? Math.max(0, variant.stock_quantity - (serverItem?.quantity ?? 0))
      : variant.stock_quantity

    pendingItems.push({
      id: `guest:${variant.id}`,
      quantity,
      variant_id: variant.id,
      variant_label: variant.label,
      stock_quantity: availableStock,
      discount_bps: variant.discount_bps,
      product_id: product.id,
      product_name: product.name,
      producer_id: product.producer_id,
      shop_name: product.shop_name,
      delivery_fee_honshu_yen: 0,
      delivery_fee_hokkaido_yen: 0,
      delivery_fee_okinawa_yen: 0,
      image_url: product.image_url,
      unit_price_yen: variant.price_yen,
      pending_merge: options.pendingMerge,
      unavailable: quantity > availableStock || availableStock < 1,
    })
  }

  return {
    id: serverCart?.id ?? 'guest',
    items: [...(serverCart?.items ?? []), ...pendingItems],
  }
}

export function useBuyerCartQuery() {
  return useQuery({
    queryKey: buyerCartKeys.current(),
    queryFn: async ({ signal }) => {
      let requestBuyerId: string | undefined
      try {
        const session = await queryClient.fetchQuery(currentSessionQuery)
        if (signal.aborted) throw new Error('Cart request was cancelled.')
        requestBuyerId = session?.id
        if (!session) {
          const products = await fetchBuyerCatalogueProductsByVariants(
            readGuestCart().map((item) => item.variant_id),
            signal,
          )
          return projectGuestCart(products)
        }
        const serverCart = (await api.get<{ data: BuyerCart }>('/api/v1/buyer/cart', { signal }))
          .data.data
        const guestItems = readGuestCart()
        if (!guestItems.length) return serverCart

        const products = await fetchBuyerCatalogueProductsByVariants(
          readGuestCart().map((item) => item.variant_id),
          signal,
        )
        return projectGuestCart(products, { serverCart, pendingMerge: true })
      } catch (error) {
        if (signal.aborted) throw error
        if (!axios.isAxiosError(error) || ![401, 403].includes(error.response?.status ?? 0))
          throw error
        const session = queryClient.getQueryData<CurrentSession | null>(
          currentSessionQuery.queryKey,
        )
        if (session?.id === requestBuyerId)
          queryClient.setQueryData(currentSessionQuery.queryKey, null)

        const products = await fetchBuyerCatalogueProductsByVariants(
          readGuestCart().map((item) => item.variant_id),
          signal,
        )
        return projectGuestCart(products)
      }
    },
  })
}

type BuyerCartCount = components['schemas']['BuyerCartCountResource']

export function useBuyerCartCountQuery(
  buyerId: Ref<string | undefined>,
  guestItems: Ref<GuestCartLine[]>,
) {
  return useQuery({
    queryKey: computed(() => buyerCartKeys.count(buyerId.value ?? 'guest', guestItems.value)),
    enabled: computed(() => Boolean(buyerId.value)),
    queryFn: async ({ queryKey, signal }) => {
      try {
        return (
          await api.get<{ data: BuyerCartCount }>('/api/v1/buyer/cart/count', {
            params: queryKey[4].length ? { guest_items: queryKey[4] } : undefined,
            signal,
          })
        ).data.data
      } catch (error) {
        if (axios.isAxiosError(error) && [401, 403].includes(error.response?.status ?? 0)) {
          const session = queryClient.getQueryData<CurrentSession | null>(
            currentSessionQuery.queryKey,
          )
          if (session?.id === queryKey[3]) {
            queryClient.setQueryData(currentSessionQuery.queryKey, null)
            queryClient.removeQueries({ queryKey: buyerCartKeys.all() })
          }
        }
        throw error
      }
    },
  })
}

export function cacheBuyerCart(cart: BuyerCart) {
  queryClient.setQueryData(buyerCartKeys.current(), cart)
  const session = queryClient.getQueryData<CurrentSession | null>(currentSessionQuery.queryKey)
  if (!session || cart.id === 'guest') return

  const serverItems = cart.items.filter((item) => !item.id.startsWith('guest:'))
  const guestItems = readGuestCart()
  const serverCount = serverItems.reduce((total, item) => total + item.quantity, 0)
  const count =
    serverCount +
    guestItems.reduce((total, item) => {
      const serverQuantity =
        serverItems.find((serverItem) => serverItem.variant_id === item.variant_id)?.quantity ?? 0
      return (
        total +
        (item.merge_target === undefined
          ? item.quantity
          : Math.max(0, item.merge_target - serverQuantity))
      )
    }, 0)
  const countKey = buyerCartKeys.count(session.id, guestItems)
  void queryClient.cancelQueries({ queryKey: countKey, exact: true })
  queryClient.setQueryData(countKey, { count })
}
