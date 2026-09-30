import { useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { getApiErrorMessage } from '@/lib/api-error'
import { useProducerLoginMutation } from '@/services/auth/auth.mutation'

export function useProducerLogin(clearPassword: () => void) {
  const router = useRouter()
  const loginMutation = useProducerLoginMutation()

  function login(email: string, password: string) {
    loginMutation.mutate(
      { email, password },
      {
        onSuccess: (session) => {
          loginMutation.reset()
          void router.replace({ name: session.producer?.eligible_to_sell ? 'dashboard' : 'onboarding' })
        },
        onError: (error) => {
          const message = getApiErrorMessage(error)
          if (message) toast.error(message)
          clearPassword()
          loginMutation.reset()
        },
      },
    )
  }

  return { isLoggingIn: loginMutation.isPending, login }
}
