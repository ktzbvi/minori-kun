import { toTypedSchema } from '@vee-validate/zod'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { toast } from '@minorikun/ui'
import { startBuyerRegistration } from '@/services/registration/registration.mutation'

export function useBuyerRegisterEmail() {
  const router = useRouter()
  const route = useRoute()
  const registrationSchema = toTypedSchema(
    z.object({
      email: z
        .string()
        .trim()
        .min(1, 'メールアドレスを入力してください。')
        .email('正しいメールアドレスを入力してください。')
        .max(255, 'メールアドレスは255文字以内で入力してください。'),
    }),
  )
  const { defineField, errors, handleSubmit, isSubmitting } = useForm({
    validationSchema: registrationSchema,
    initialValues: { email: '' },
  })
  const [email, emailAttrs] = defineField('email', (state) => ({
    validateOnBlur: false,
    validateOnChange: false,
    validateOnInput: false,
    validateOnModelUpdate: state.errors.length > 0,
  }))
  const submit = handleSubmit(
    async (values) => {
      try {
        await startBuyerRegistration(values.email)
        await router.push({ name: 'register-verify', query: registrationRedirectQuery() })
      } catch (error: unknown) {
        if (axios.isAxiosError(error) && error.response?.status === 429) {
          await router.push({ name: 'register-verify', query: registrationRedirectQuery() })
          return
        }

        toast.error('認証コードの送信に失敗しました。')
      }
    },
    () => undefined,
  )
  function goBack() {
    router.back()
  }
  function registrationRedirectQuery() {
    return typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
      ? { redirect: route.query.redirect }
      : {}
  }
  return {
    router,
    route,
    registrationSchema,
    defineField,
    errors,
    handleSubmit,
    isSubmitting,
    email,
    emailAttrs,
    submit,
    goBack,
    registrationRedirectQuery,
  }
}
