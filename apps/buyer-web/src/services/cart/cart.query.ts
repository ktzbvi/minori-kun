import axios from 'axios'
import { useQuery } from '@tanstack/vue-query'
import api from '@/services/api'
import { queryClient } from '@/lib/query'
import { readGuestCart, removeGuestCartItem, setGuestCartMergeTarget } from '@/lib/guest-cart'
import { buyerCatalogKeys } from '@/services/catalog/catalog.key'
import { fetchBuyerCatalogueProducts, type BuyerCatalogueProduct } from '@/services/catalog/catalog.query'
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
    queryFn: async () => {
      try {
        const serverCart = (await api.get<{ data: BuyerCart }>('/api/v1/buyer/cart')).data.data
        const guestItems = readGuestCart()
        if (!guestItems.length) return serverCart

        const products = await queryClient.ensureQueryData({
          queryKey: buyerCatalogKeys.products(),
          queryFn: fetchBuyerCatalogueProducts,
        })
        return projectGuestCart(products, { serverCart, pendingMerge: true })
      } catch (error) {
        if (!axios.isAxiosError(error) || error.response?.status !== 401) throw error

        const products = await queryClient.ensureQueryData({
          queryKey: buyerCatalogKeys.products(),
          queryFn: fetchBuyerCatalogueProducts,
        })
        return projectGuestCart(products)
      }
    },
  })
}
