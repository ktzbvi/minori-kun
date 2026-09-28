import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { adminAuthApi } from '@/services/api'
import { adminAuthKeys } from './auth.key'

export interface AdminLoginCredentials {
  email: string
  password: string
}

export function useAdminLoginMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ email, password }: AdminLoginCredentials) => {
      await adminAuthApi.csrf()
      const response = await adminAuthApi.login(email, password)
      return response.data.data
    },
    onSuccess: () =>
      queryClient.invalidateQueries({ queryKey: adminAuthKeys.currentSession() }),
  })
}

export function useAdminLogoutMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: () => adminAuthApi.logout(),
    onSettled: () => {
      queryClient.removeQueries({ queryKey: adminAuthKeys.currentSession() })
    },
  })
}
