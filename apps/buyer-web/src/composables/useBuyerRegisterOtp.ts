import { toTypedSchema } from '@vee-validate/zod'
import axios from 'axios'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { toast } from '@minorikun/ui'
import {
  resendBuyerRegistrationOtp,
  verifyBuyerRegistrationOtp,
} from '@/services/registration/registration.mutation'
import { getBuyerRegistrationStatus } from '@/services/registration/registration.query'

export function useBuyerRegisterOtp() {
  const router = useRouter()
  const route = useRoute()
  const email = ref('')
  const resendAvailableAt = ref<Date | null>(null)
  const now = ref(Date.now())
  let timer: ReturnType<typeof setInterval> | undefined
  const otpSchema = toTypedSchema(
    z.object({
      code: z.string().regex(/^\d{6}$/, '確認コードを6桁の数字で入力してください。'),
    }),
  )
  const { defineField, errors, handleSubmit, isSubmitting, setFieldValue } = useForm({
    validationSchema: otpSchema,
    initialValues: { code: '' },
  })
  const [code, codeAttrs] = defineField('code', (state) => ({
    validateOnBlur: false,
    validateOnChange: false,
    validateOnInput: false,
    validateOnModelUpdate: state.errors.length > 0,
  }))
  const secondsUntilResend = computed(() => {
    if (!resendAvailableAt.value) return 0

    return Math.max(0, Math.ceil((resendAvailableAt.value.getTime() - now.value) / 1000))
  })
  const canResend = computed(() => secondsUntilResend.value === 0)
  const resendLabel = computed(() => {
    if (canResend.value) return '認証コードを再送する'

    const minutes = Math.floor(secondsUntilResend.value / 60)
    const seconds = secondsUntilResend.value % 60
    return `再送できるまで ${minutes}:${String(seconds).padStart(2, '0')}`
  })
  function setStatus(status: { email: string; resend_available_at: string }) {
    email.value = status.email
    resendAvailableAt.value = new Date(status.resend_available_at)
  }
  async function loadStatus() {
    try {
      setStatus(await getBuyerRegistrationStatus())
    } catch {
      await router.replace({ name: 'register' })
    }
  }
  const submit = handleSubmit(
    async (values) => {
      try {
        await verifyBuyerRegistrationOtp(values.code)
      } catch (error: unknown) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
          setFieldValue('code', '', false)
          toast.error('確認コードが正しくないか、有効期限が切れています。')
          return
        }

        toast.error('確認コードの確認に失敗しました。')
        return
      }

      if (!router.hasRoute('register-details')) {
        toast.success('メールアドレスを確認しました。')
        return
      }

      await router.push({ name: 'register-details', query: registrationRedirectQuery() })
      toast.success('メールアドレスを確認しました。')
    },
    () => undefined,
  )
  async function resend() {
    if (!canResend.value) return

    try {
      setStatus(await resendBuyerRegistrationOtp())
      setFieldValue('code', '', false)
      toast.success('認証コードを再送しました。')
    } catch (error: unknown) {
      if (axios.isAxiosError(error) && error.response?.status === 429) {
        const availableAt = error.response.data?.data?.resend_available_at
        if (typeof availableAt === 'string') resendAvailableAt.value = new Date(availableAt)
        return
      }

      toast.error('認証コードの再送に失敗しました。')
    }
  }
  function changeEmail() {
    router.push({ name: 'register' })
  }
  function goBack() {
    router.back()
  }
  function registrationRedirectQuery() {
    return typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
      ? { redirect: route.query.redirect }
      : {}
  }
  onMounted(async () => {
    await loadStatus()
    timer = setInterval(() => {
      now.value = Date.now()
    }, 1000)
  })
  onBeforeUnmount(() => {
    if (timer) clearInterval(timer)
  })
  return {
    router,
    route,
    email,
    resendAvailableAt,
    now,
    timer,
    otpSchema,
    defineField,
    errors,
    handleSubmit,
    isSubmitting,
    setFieldValue,
    code,
    codeAttrs,
    secondsUntilResend,
    canResend,
    resendLabel,
    setStatus,
    loadStatus,
    submit,
    resend,
    changeEmail,
    goBack,
    registrationRedirectQuery,
  }
}
