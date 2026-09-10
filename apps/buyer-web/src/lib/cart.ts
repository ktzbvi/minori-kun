import { computed, ref } from 'vue'

type CartLine = {
  productId: string
  variantId?: string
  quantity: number
}

const lines = ref<CartLine[]>([])

const cartItemCount = computed(() => lines.value.reduce((total, line) => total + line.quantity, 0))

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

function reset() {
  lines.value = []
}

export function useCart() {
  return {
    cartItemCount,
    addItem,
    reset,
  }
}
