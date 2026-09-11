import { computed, ref } from 'vue'

export type CartLine = {
  productId: string
  variantId?: string
  quantity: number
}

const lines = ref<CartLine[]>([])

const cartItemCount = computed(() => lines.value.reduce((total, line) => total + line.quantity, 0))
const cartLines = computed(() => lines.value)

function addItem(productId: string, quantity = 1, variantId?: string) {
  const existingLine = lines.value.find(
    (line) => line.productId === productId && line.variantId === variantId,
  )

  if (existingLine) {
    existingLine.quantity += quantity
    return
  }

  lines.value.push({ productId, variantId, quantity })
}

function setItemQuantity(productId: string, quantity: number, variantId?: string) {
  const line = lines.value.find(
    (item) => item.productId === productId && item.variantId === variantId,
  )

  if (!line) return
  if (quantity <= 0) {
    removeItem(productId, variantId)
    return
  }

  line.quantity = quantity
}

function removeItem(productId: string, variantId?: string) {
  lines.value = lines.value.filter(
    (line) => line.productId !== productId || line.variantId !== variantId,
  )
}

function reset() {
  lines.value = []
}

export function useCart() {
  return {
    cartItemCount,
    cartLines,
    addItem,
    setItemQuantity,
    removeItem,
    reset,
  }
}
