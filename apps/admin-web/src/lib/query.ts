import { QueryClient } from '@tanstack/vue-query'
import { authApi } from './api'

export const queryClient = new QueryClient({
  defaultOptions: { queries: { retry: false, staleTime: 30_000 } },
})

export const currentSessionQuery = {
  queryKey: ['current-session'] as const,
  queryFn: async () => (await authApi.me()).data.data,
}
