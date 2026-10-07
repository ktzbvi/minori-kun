import axios from 'axios'
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { queryClient } from '@/lib/query'
import { buyerAuthKeys } from '@/services/auth/auth.key'
import { completeBuyerRegistration } from '@/services/registration/registration.mutation'
import { getBuyerRegistrationStatus } from '@/services/registration/registration.query'

export function useBuyerRegisterDetails() {
  type FieldName =
    | 'name'
    | 'name_phonetic'
    | 'password'
    | 'password_confirmation'
    | 'phone'
    | 'postal_code'
    | 'prefecture'
    | 'city'
    | 'address_line1'
    | 'address_line2'
  type FieldDefinition = {
    name: FieldName
    label: string
    placeholder: string
    required: boolean
  }
  const router = useRouter()
  const route = useRoute()
  const email = ref('')
  const submitting = ref(false)
  const fieldErrors = reactive<Record<string, string>>({})
  const form = reactive({
    name: '',
    name_phonetic: '',
    phone: '',
    postal_code: '',
    prefecture: '',
    city: '',
    address_line1: '',
    address_line2: '',
    password: '',
    password_confirmation: '',
    terms_accepted: false,
  })
  const fields: FieldDefinition[] = [
    {
      name: 'name',
      label: 'お名前',
      placeholder: '山田 太郎',
      required: true,
    },
    {
      name: 'name_phonetic',
      label: 'フリガナ',
      placeholder: 'ヤマダ タロウ',
      required: true,
    },
    {
      name: 'password',
      label: 'パスワード',
      placeholder: '半角英数字8文字以上',
      required: true,
    },
    {
      name: 'password_confirmation',
      label: 'パスワード確認',
      placeholder: 'もう一度入力してください',
      required: true,
    },
    {
      name: 'phone',
      label: '電話番号',
      placeholder: '090-0000-0000',
      required: true,
    },
    {
      name: 'postal_code',
      label: '郵便番号',
      placeholder: '123-4567',
      required: true,
    },
    {
      name: 'prefecture',
      label: '都道府県',
      placeholder: '東京都',
      required: true,
    },
    {
      name: 'city',
      label: '市区町村',
      placeholder: '新宿区',
      required: true,
    },
    {
      name: 'address_line1',
      label: '住所',
      placeholder: '西新宿1-2-3',
      required: true,
    },
    {
      name: 'address_line2',
      label: '建物名・部屋番号',
      placeholder: 'サンプルビル101',
      required: false,
    },
  ]
  const canSubmit = computed(() => form.terms_accepted && !submitting.value)
  onMounted(async () => {
    try {
      const data = await getBuyerRegistrationStatus()

      if (!data.verified) {
        throw new Error('Registration email is not verified.')
      }

      email.value = data.email
    } catch {
      await router.replace({ name: 'register' })
    }
  })
  function validatePhonetic(): void {
    if (!form.name_phonetic || /^[ァ-ヺー\s]+$/u.test(form.name_phonetic)) {
      delete fieldErrors.name_phonetic
      return
    }

    fieldErrors.name_phonetic = 'フリガナは全角カタカナで入力してください。'
  }
  async function submit(): Promise<void> {
    if (!canSubmit.value) return

    Object.keys(fieldErrors).forEach((key) => delete fieldErrors[key])
    submitting.value = true

    try {
      const data = await completeBuyerRegistration(form)

      await queryClient.invalidateQueries({ queryKey: buyerAuthKeys.currentSession() })
      await router.replace(
        typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
          ? route.query.redirect
          : data.redirect,
      )
      toast.success('登録しました。')
    } catch (error) {
      if (axios.isAxiosError(error) && error.response?.status === 422) {
        const errors = error.response.data?.errors

        if (errors && typeof errors === 'object') {
          Object.entries(errors).forEach(([field, messages]) => {
            if (Array.isArray(messages) && typeof messages[0] === 'string') {
              fieldErrors[field] = messages[0]
            }
          })
        }

        return
      }

      toast.error('登録に失敗しました。')
    } finally {
      submitting.value = false
    }
  }
  return {
    router,
    route,
    email,
    submitting,
    fieldErrors,
    form,
    fields,
    canSubmit,
    validatePhonetic,
    submit,
  }
}
