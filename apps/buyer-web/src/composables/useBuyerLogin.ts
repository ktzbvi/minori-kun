import { toTypedSchema } from '@vee-validate/zod'
import axios from 'axios'
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { toast } from '@minorikun/ui'
import { queryClient } from '@/lib/query'
import { cacheBuyerCart } from '@/services/cart/cart.query'
import { buyerCartKeys } from '@/services/cart/cart.key'
import { mergeGuestCartAfterAuthentication } from '@/services/cart/cart.mutation'
import { loginBuyer } from '@/services/auth/auth.mutation'
import { buyerAuthKeys } from '@/services/auth/auth.key'

export function useBuyerLogin() {
  const errorMessage = ref('')
  const route = useRoute()
  const router = useRouter()
  const authenticationError = 'メールアドレスまたはパスワードを確認してください。'
  const rateLimitError = '試行回数が多すぎます。しばらくしてからもう一度お試しください。'
  const serviceError = '現在ログインできません。時間をおいてからもう一度お試しください。'
  const loginSchema = toTypedSchema(
    z.object({
      email: z
        .string()
        .trim()
        .min(1, 'メールアドレスを入力してください。')
        .email('正しいメールアドレスを入力してください。')
        .max(255, 'メールアドレスは255文字以内で入力してください。'),
      password: z
        .string()
        .min(1, 'パスワードを入力してください。')
        .max(4096, 'パスワードは4096文字以内で入力してください。'),
    }),
  )
  const { defineField, errors, handleSubmit, isSubmitting, setFieldValue } = useForm({
    validationSchema: loginSchema,
    initialValues: { email: '', password: '' },
  })
  const [email, emailAttrs] = defineField('email', (state) => ({
    validateOnBlur: false,
    validateOnChange: false,
    validateOnInput: false,
    validateOnModelUpdate: state.errors.length > 0,
  }))
  const [password, passwordAttrs] = defineField('password', (state) => ({
    validateOnBlur: false,
    validateOnChange: false,
    validateOnInput: false,
    validateOnModelUpdate: state.errors.length > 0,
  }))
  const submit = handleSubmit(
    async (values) => {
      errorMessage.value = ''
      try {
        const session = await loginBuyer(values)
        await queryClient.cancelQueries({ queryKey: buyerCartKeys.all() })
        queryClient.removeQueries({ queryKey: buyerCartKeys.all() })
        queryClient.setQueryData(buyerAuthKeys.currentSession(), session)
        let guestCartMergeFailed = false
        try {
          const result = await mergeGuestCartAfterAuthentication()
          guestCartMergeFailed = result.failedCount > 0
          if (result.cart) cacheBuyerCart(result.cart)
        } catch {
          guestCartMergeFailed = true
        }
        await queryClient.invalidateQueries({ queryKey: buyerCartKeys.all() })

        const redirect =
          typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
            ? route.query.redirect
            : '/'
        await router.replace(redirect)
        if (guestCartMergeFailed) {
          toast.warning('一部の商品をカートに反映できませんでした。商品と在庫をご確認ください。')
        } else {
          toast.success('ログインしました。')
        }
      } catch (error: unknown) {
        if (axios.isAxiosError(error) && error.response?.status === 429) {
          errorMessage.value = rateLimitError
        } else if (axios.isAxiosError(error) && error.response?.status !== 422) {
          errorMessage.value = serviceError
        } else {
          errorMessage.value = authenticationError
        }
        setFieldValue('password', '', false)
      }
    },
    () => {
      errorMessage.value = ''
    },
  )
  function goBack() {
    router.back()
  }
  return {
    errorMessage,
    route,
    router,
    authenticationError,
    rateLimitError,
    serviceError,
    loginSchema,
    defineField,
    errors,
    handleSubmit,
    isSubmitting,
    setFieldValue,
    email,
    emailAttrs,
    password,
    passwordAttrs,
    submit,
    goBack,
  }
}
