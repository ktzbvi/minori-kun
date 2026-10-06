import { reactive, ref } from 'vue'

export type CheckoutDeliveryAddress = {
  name: string
  phone: string
  postalCode: string
  prefecture: string
  city: string
  addressLine1: string
  addressLine2: string
}

export const checkoutDeliveryAddress = reactive<CheckoutDeliveryAddress>({
  name: '田中 太郎',
  phone: '090-1234-5678',
  postalCode: '150-0002',
  prefecture: '東京都',
  city: '渋谷区',
  addressLine1: '渋谷2-1-3',
  addressLine2: '',
})

const hasCheckoutDeliveryAddress = ref(false)

export function initializeCheckoutDeliveryAddress(address: CheckoutDeliveryAddress) {
  if (hasCheckoutDeliveryAddress.value) return

  Object.assign(checkoutDeliveryAddress, address)
  hasCheckoutDeliveryAddress.value = true
}

export function updateCheckoutDeliveryAddress(address: CheckoutDeliveryAddress) {
  Object.assign(checkoutDeliveryAddress, address)
  hasCheckoutDeliveryAddress.value = true
}
