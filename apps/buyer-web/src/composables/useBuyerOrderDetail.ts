import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { cancelBuyerOrder, downloadBuyerReceipt } from '@/services/orders/orders.api'
import { buyerOrderKeys } from '@/services/orders/orders.key'
import { useBuyerOrderQuery } from '@/services/orders/orders.query'

export function useBuyerOrderDetail() {
  const route = useRoute()
  const router = useRouter()
  const client = useQueryClient()
  const orderId = computed(() => String(route.params.orderId ?? ''))
  const order = useBuyerOrderQuery(orderId)
  const isCancelled = computed(() => order.data.value?.order_state === 'cancelled')
  const confirmCancel = ref(false)
  const cancellationKey = crypto.randomUUID()
  function openSearch() {
    void router.push({ name: 'search' })
  }
  function openCart() {
    void router.push({ name: 'cart' })
  }
  const cancelMutation = useMutation({
    mutationFn: () => cancelBuyerOrder(orderId.value, cancellationKey),
    onSuccess: (data) => {
      client.setQueryData(buyerOrderKeys.detail(orderId.value), data)
      void client.invalidateQueries({ queryKey: buyerOrderKeys.all() })
      confirmCancel.value = false
      toast.success('注文をキャンセルしました。返金状況は注文詳細で確認できます。')
    },
    onError: () => toast.error('キャンセルできませんでした。注文状態をご確認ください。'),
  })
  const receiptMutation = useMutation({
    mutationFn: () =>
      downloadBuyerReceipt(orderId.value, order.data.value?.order_number ?? 'receipt'),
    onError: () =>
      toast.error('領収書をダウンロードできませんでした。時間をおいて再度お試しください。'),
  })
  function yen(value: number) {
    return `${value.toLocaleString('ja-JP')}円`
  }
  function date(value: string) {
    return new Date(value).toLocaleString('ja-JP')
  }
  function fulfillmentLabel(value?: string) {
    return (
      ({ received: '受付', processing: '対応中', shipped: '発送済み' } as Record<string, string>)[
        value ?? ''
      ] ??
      value ??
      '-'
    )
  }
  return {
    route,
    router,
    client,
    orderId,
    order,
    isCancelled,
    confirmCancel,
    cancellationKey,
    openSearch,
    openCart,
    cancelMutation,
    receiptMutation,
    yen,
    date,
    fulfillmentLabel,
  }
}
