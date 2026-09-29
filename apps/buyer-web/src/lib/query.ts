import { QueryClient } from '@tanstack/vue-query'
import { currentSessionQuery } from '@/services/auth/auth.query'

export const queryClient = new QueryClient({
  defaultOptions: { queries: { retry: false, staleTime: 30_000 } },
})

export { currentSessionQuery }
