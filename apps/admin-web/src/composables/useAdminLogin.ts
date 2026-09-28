import { toast } from '@minorikun/ui'
import { useRoute, useRouter } from 'vue-router'
import { getApiErrorMessage } from '@/services/api'
import { useAdminLoginMutation } from '@/services/auth/auth.mutation'

export function useAdminLogin(clearPassword: () => void) {
  const loginMutation = useAdminLoginMutation()
  const route = useRoute()
  const router = useRouter()

  function login(email: string, password: string) {
    loginMutation.mutate(
      { email, password },
      {
        onSuccess: async () => {
          const redirect =
            typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
              ? route.query.redirect
              : '/'
          await router.replace(redirect)
          toast.success('成功')
        },
        onError: (error) => {
          const message = getApiErrorMessage(error)
          if (message) toast.error(message)
          clearPassword()
        },
      },
    )
  }

  return { isLoggingIn: loginMutation.isPending, login }
}
