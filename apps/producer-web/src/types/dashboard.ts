import type { operations } from '@minorikun/api-contracts'

type ProducerDashboardOperation = operations['producerDashboard.show']

export type ProducerDashboardResponse =
  ProducerDashboardOperation['responses'][200]['content']['application/json']

export type ProducerDashboardData = ProducerDashboardResponse['data']
export type ProducerDashboardActionOrder = ProducerDashboardData['action_orders'][number]
