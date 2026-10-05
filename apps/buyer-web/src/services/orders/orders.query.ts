import { useQuery } from '@tanstack/vue-query'
import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { getBuyerOrder, getBuyerOrders, getPaymentMode } from './orders.api'
import { buyerOrderKeys } from './orders.key'

export function usePaymentModeQuery() {
  return useQuery({ queryKey: buyerOrderKeys.paymentMode(), queryFn: getPaymentMode })
}

export function useBuyerOrdersQuery(filters: MaybeRefOrGetter<Record<string, string>>) {
  return useQuery({
    queryKey: computed(() => buyerOrderKeys.list(toValue(filters))),
    queryFn: () => getBuyerOrders(toValue(filters)),
    refetchInterval: (query) =>
      (query.state.data?.some((order) => ['pending', 'requires_action'].includes(order.refund_state)) ?? false) ||
      query.state.data?.some(
        (order) =>
          order.order_state === '注文確定'
          && new Date(order.cancellation_deadline_at).getTime() > Date.now(),
      )
        ? 15_000
        : false,
  })
}

export function useBuyerOrderQuery(id: MaybeRefOrGetter<string>) {
  return useQuery({
    queryKey: computed(() => buyerOrderKeys.detail(toValue(id))),
    queryFn: () => getBuyerOrder(toValue(id)),
    enabled: computed(() => Boolean(toValue(id))),
    refetchInterval: (query) =>
      query.state.data?.can_cancel ||
      ['pending', 'requires_action'].includes(query.state.data?.refund_state ?? '')
        ? 15_000
        : false,
  })
}
