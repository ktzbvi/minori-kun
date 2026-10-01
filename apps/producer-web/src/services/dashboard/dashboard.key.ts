import { producerKeys } from '@/services/producer.key'

export const producerDashboardKeys = {
  all: () => [...producerKeys.all(), 'dashboard'] as const,
  detail: () => [...producerDashboardKeys.all(), 'detail'] as const,
}
