export type GuestCartLine = {
  variant_id: string
  quantity: number
  merge_target?: number
}

const storageKey = 'minorikun.buyer.guest-cart.v1'

export function readGuestCart(): GuestCartLine[] {
  if (typeof window === 'undefined') return []

  try {
    const value: unknown = JSON.parse(window.localStorage.getItem(storageKey) ?? '[]')

    if (!Array.isArray(value)) return []

    return value.filter(
      (item): item is GuestCartLine =>
        item !== null &&
        typeof item === 'object' &&
        typeof item.variant_id === 'string' &&
        Number.isInteger(item.quantity) &&
        item.quantity > 0 &&
        (item.merge_target === undefined ||
          (Number.isInteger(item.merge_target) && item.merge_target >= 0)),
    )
  } catch {
    return []
  }
}

function writeGuestCart(items: GuestCartLine[]) {
  if (typeof window === 'undefined') return

  if (items.length) {
    window.localStorage.setItem(storageKey, JSON.stringify(items))
  } else {
    window.localStorage.removeItem(storageKey)
  }
}

export function addGuestCartItem(variantId: string, quantity: number, stockQuantity: number) {
  const items = readGuestCart()
  const existing = items.find((item) => item.variant_id === variantId)
  const nextQuantity = (existing?.quantity ?? 0) + quantity

  if (nextQuantity > stockQuantity) {
    throw new Error('Guest cart quantity exceeds current stock.')
  }

  if (existing) {
    existing.quantity = nextQuantity
    delete existing.merge_target
  } else {
    items.push({ variant_id: variantId, quantity })
  }

  writeGuestCart(items)
}

export function updateGuestCartItem(variantId: string, quantity: number) {
  writeGuestCart(
    readGuestCart().map((item) =>
      item.variant_id === variantId ? { variant_id: variantId, quantity } : item,
    ),
  )
}

export function removeGuestCartItem(variantId: string) {
  writeGuestCart(readGuestCart().filter((item) => item.variant_id !== variantId))
}

export function setGuestCartMergeTarget(variantId: string, mergeTarget: number) {
  writeGuestCart(
    readGuestCart().map((item) =>
      item.variant_id === variantId ? { ...item, merge_target: mergeTarget } : item,
    ),
  )
}
