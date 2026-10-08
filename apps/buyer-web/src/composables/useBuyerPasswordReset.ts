import { toTypedSchema } from '@vee-validate/zod'
import axios from 'axios'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { startBuyerPasswordReset } from '@/services/password-reset/password-reset.mutation'

export function useBuyerPasswordReset() {
  const router = useRouter()
  const requestMessage = ref('')
  const requestSent = ref(false)
  const serviceError = ref('')
  const schema = toTypedSchema(
    z.object({
      email: z
        .string()
        .trim()
        .min(1, 'メールアドレスを入力してください。')
        .email('正しいメールアドレスを入力してください。')
        .max(255, 'メールアドレスは255文字以内で入力してください。'),
    }),
  )
  const { defineField, errors, handleSubmit, isSubmitting, setErrors } = useForm({
    validationSchema: schema,
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
      requestMessage.value = ''
      serviceError.value = ''

      try {
        const data = await startBuyerPasswordReset(values.email)
        requestMessage.value = data.message
        requestSent.value = true
      } catch (error) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
          return
        }

        serviceError.value = '現在メールを送信できません。時間をおいてからもう一度お試しください。'
      }
    },
    () => undefined,
  )
  function requestAnotherLink() {
    requestSent.value = false
    requestMessage.value = ''
    serviceError.value = ''
    setErrors({})
  }
  return {
    router,
    requestMessage,
    requestSent,
    requestAnotherLink,
    serviceError,
    schema,
    defineField,
    errors,
    handleSubmit,
    isSubmitting,
    email,
    emailAttrs,
    submit,
  }
}
