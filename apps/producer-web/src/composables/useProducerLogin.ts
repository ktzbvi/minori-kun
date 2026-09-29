import { useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { getApiErrorMessage } from '@/services/api'
import { useProducerLoginMutation } from '@/services/auth/auth.mutation'

export function useProducerLogin(clearPassword: () => void) {
  const router = useRouter()
  const loginMutation = useProducerLoginMutation()

  function login(email: string, password: string) {
    loginMutation.mutate(
      { email, password },
      {
        onSuccess: () => {
          void router.replace({ name: 'home' })
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
