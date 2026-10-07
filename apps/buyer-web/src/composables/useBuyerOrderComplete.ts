import { useRoute, useRouter } from 'vue-router'
import { useBuyerOrderQuery } from '@/services/orders/orders.query'

export function useBuyerOrderComplete() {
  const route = useRoute()
  const router = useRouter()
  const orderId = String(route.params.orderId ?? '')
  const order = useBuyerOrderQuery(orderId)
  return { route, router, orderId, order }
}
