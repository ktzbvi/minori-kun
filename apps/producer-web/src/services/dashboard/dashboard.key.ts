export const producerDashboardKeys = {
  all: () => ['producer-dashboard'] as const,
  detail: () => [...producerDashboardKeys.all(), 'detail'] as const,
}
