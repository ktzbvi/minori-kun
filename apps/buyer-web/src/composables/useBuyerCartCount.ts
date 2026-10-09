import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import {
  guestCartRevision,
  guestCartStorageKey,
  readGuestCart,
  refreshGuestCart,
} from '@/lib/guest-cart'
import { useBuyerSessionQuery } from '@/services/auth/auth.query'
import { useBuyerCartCountQuery } from '@/services/cart/cart.query'

export function useBuyerCartCount() {
  const sessionQuery = useBuyerSessionQuery()
  const guestItems = ref(readGuestCart())
  watch(
    guestCartRevision,
    () => {
      guestItems.value = readGuestCart()
    },
    { flush: 'sync' },
  )
  const buyerId = computed(() => sessionQuery.data.value?.id)
  const countQuery = useBuyerCartCountQuery(buyerId, guestItems)
  const cartItemCount = computed(() =>
    buyerId.value
      ? (countQuery.data.value?.count ?? 0)
      : guestItems.value.reduce((total, item) => total + item.quantity, 0),
  )
  function handleStorage(event: StorageEvent) {
    if (event.key === guestCartStorageKey || event.key === null) refreshGuestCart()
  }
  onMounted(() => window.addEventListener('storage', handleStorage))
  onBeforeUnmount(() => window.removeEventListener('storage', handleStorage))
  return { cartItemCount }
}
