import { toTypedSchema } from '@vee-validate/zod'
import axios from 'axios'
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { ensureCsrfCookie } from '@/services/api'
import { completeBuyerPasswordReset } from '@/services/password-reset/password-reset.mutation'

export function useBuyerPasswordResetConfirm() {
  const route = useRoute()
  const router = useRouter()
  const submissionError = ref('')
  const email = typeof route.query.email === 'string' ? route.query.email : ''
  const token = typeof route.query.token === 'string' ? route.query.token : ''
  const linkIsComplete = computed(() => Boolean(email && token))
  const passwordPolicy =
    'パスワードは8〜64文字で、大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。'
  const schema = toTypedSchema(
    z
      .object({
        password: z.string().min(1, '新しいパスワードを入力してください。').max(64, passwordPolicy),
        password_confirmation: z.string().min(1, '確認用パスワードを入力してください。'),
      })
      .superRefine((values, context) => {
        if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,64}$/.test(values.password)) {
          context.addIssue({ code: 'custom', message: passwordPolicy, path: ['password'] })
        }

        if (
          values.password &&
          values.password_confirmation &&
          values.password !== values.password_confirmation
        ) {
          context.addIssue({
            code: 'custom',
            message: '新しいパスワードと確認用パスワードが一致しません。',
            path: ['password_confirmation'],
          })
        }
      }),
  )
  const { defineField, errors, handleSubmit, isSubmitting } = useForm({
    validationSchema: schema,
    initialValues: { password: '', password_confirmation: '' },
  })
  const [password, passwordAttrs] = defineField('password', (state) => ({
    validateOnBlur: false,
    validateOnChange: false,
    validateOnInput: false,
    validateOnModelUpdate: state.errors.length > 0,
  }))
  const [passwordConfirmation, passwordConfirmationAttrs] = defineField(
    'password_confirmation',
    (state) => ({
      validateOnBlur: false,
      validateOnChange: false,
      validateOnInput: false,
      validateOnModelUpdate: state.errors.length > 0,
    }),
  )
  const submit = handleSubmit(
    async (values) => {
      submissionError.value = ''

      if (!linkIsComplete.value) {
        submissionError.value = '再設定用リンクが無効です。もう一度メールを送信してください。'
        return
      }

      try {
        await completeBuyerPasswordReset({
          email,
          token,
          password: values.password,
          password_confirmation: values.password_confirmation,
        })
        await ensureCsrfCookie(true)
        await router.replace({ name: 'login', query: { passwordReset: 'success' } })
      } catch (error) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
          submissionError.value =
            error.response.data?.message ?? '入力内容を確認して、もう一度お試しください。'
          return
        }

        submissionError.value =
          '現在パスワードを変更できません。時間をおいてからもう一度お試しください。'
      }
    },
    () => undefined,
  )
  return {
    route,
    router,
    submissionError,
    email,
    token,
    linkIsComplete,
    passwordPolicy,
    schema,
    defineField,
    errors,
    handleSubmit,
    isSubmitting,
    password,
    passwordAttrs,
    passwordConfirmation,
    passwordConfirmationAttrs,
    submit,
  }
}
