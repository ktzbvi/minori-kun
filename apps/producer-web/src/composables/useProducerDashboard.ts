import { computed } from 'vue'
import { useCurrentSessionQuery } from '@/services/auth/auth.query'
import { useProducerDashboardQuery } from '@/services/dashboard/dashboard.query'

export function useProducerDashboard() {
  // FR-P-005 / SCR-P-005: derive the dashboard from the existing owned-data queries.
  const sessionQuery = useCurrentSessionQuery()
  const dashboardQuery = useProducerDashboardQuery()
  const dashboard = computed(() => dashboardQuery.data.value?.data)
  const producerName = computed(() => sessionQuery.data.value?.display_name || '生産者')

  const numberFormatter = new Intl.NumberFormat('ja-JP')
  const currencyFormatter = new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY', maximumFractionDigits: 0 })
  const dateTimeFormatter = new Intl.DateTimeFormat('ja-JP', { month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit' })
  const dateFormatter = new Intl.DateTimeFormat('ja-JP', { year: 'numeric', month: 'numeric', day: 'numeric' })

  function formatCount(value: number | string | null | undefined) {
    return numberFormatter.format(Number(value ?? 0))
  }

  const summary = computed(() => ({
    products: {
      value: `${formatCount(dashboard.value?.products.total)}件`,
      meta: `公開中 ${formatCount(dashboard.value?.products.published)}件`,
    },
    orders: {
      value: `${formatCount(dashboard.value?.orders.requiring_action)}件`,
      meta: `受付 ${formatCount(dashboard.value?.orders.received)}件・対応中 ${formatCount(dashboard.value?.orders.processing)}件`,
    },
    sales: {
      value: currencyFormatter.format(dashboard.value?.sales.total_yen ?? 0),
      meta: dashboard.value?.sales.period_label ?? '',
    },
  }))

  function fulfillmentLabel(state: string) {
    if (state === 'received') return '受付'
    if (state === 'processing') return '対応中'
    if (state === 'shipped') return '発送済み'
    return '確認中'
  }

  const actionOrders = computed(() => (dashboard.value?.action_orders ?? []).map(order => ({
    ...order,
    orderedAtLabel: order.ordered_at ? dateTimeFormatter.format(new Date(order.ordered_at)) : '日時未設定',
    fulfillmentLabel: fulfillmentLabel(order.fulfillment_state),
  })))

  const payoutAlert = computed(() => {
    const alert = dashboard.value?.payout_alert
    return {
      stateLabel: alert
        ? ({ scheduled: '振込予定', carry_forward: '繰越', processing: '処理中', failed: '要確認' }[alert.state] ?? '確認中')
        : '振込予定',
      amountLabel: currencyFormatter.format(alert?.expected_payout_yen ?? 0),
      dueOnLabel: alert?.due_on ? `振込予定日：${dateFormatter.format(new Date(alert.due_on))}` : '振込予定日未定',
    }
  })

  function reloadDashboard() {
    return dashboardQuery.refetch()
  }

  return {
    producerName,
    summary,
    actionOrders,
    payoutAlert,
    isLoading: dashboardQuery.isLoading,
    isError: dashboardQuery.isError,
    reloadDashboard,
  }
}
