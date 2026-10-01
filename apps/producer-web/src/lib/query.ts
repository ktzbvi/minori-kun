import { QueryClient } from '@tanstack/vue-query'
import { producerAuthKeys } from '@/services/auth/auth.key'

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: false,
      staleTime: 30_000,
      refetchOnWindowFocus: false,
    },
  },
})

queryClient.setQueryDefaults(producerAuthKeys.currentSession(), {
  staleTime: Infinity,
  gcTime: 30 * 60_000,
})
