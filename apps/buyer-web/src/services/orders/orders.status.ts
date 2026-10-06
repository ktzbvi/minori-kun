export function orderStatusLabel(state: string) {
  return state === 'cancelled' ? 'キャンセル済み' : state
}

export function paymentStatusLabel(state: string) {
  return (
    ({ succeeded: '完了', refunded: '返金済み', failed: '失敗' } as Record<string, string>)[state] ??
    '処理中'
  )
}

export function refundStatusLabel(state: string) {
  return (
    ({
      pending: '処理中',
      requires_action: '処理中',
      partial: '一部返金',
      refunded: '完了',
      failed: '失敗',
      canceled: '失敗',
    } as Record<string, string>)[state] ?? null
  )
}
