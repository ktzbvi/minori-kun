import axios from 'axios'
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { getBuyerAccountProfile, updateBuyerAccountProfile } from '@/services/account/account.api'
import type { BuyerAccountProfileUpdate } from '@/types/account'

export function useBuyerMemberInfo() {
  const router = useRouter()
  const route = useRoute()
  const loading = ref(true)
  const submitting = ref(false)
  const message = ref('')
  const fieldErrors = reactive<Record<string, string>>({})
  const form = reactive<BuyerAccountProfileUpdate>({
    name: '',
    name_phonetic: '',
    email: '',
    phone: '',
    postal_code: '',
    prefecture: '',
    city: '',
    address_line1: '',
    address_line2: '',
  })
  const fields: Array<{
    key: keyof BuyerAccountProfileUpdate
    label: string
    required: boolean
    type?: string
  }> = [
    { key: 'name', label: 'お名前', required: true },
    { key: 'name_phonetic', label: 'フリガナ', required: true },
    {
      key: 'email',
      label: 'メールアドレス',
      required: true,
      type: 'email',
    },
    { key: 'phone', label: '電話番号', required: true, type: 'tel' },
    { key: 'postal_code', label: '郵便番号', required: true },
    { key: 'prefecture', label: '都道府県', required: true },
    { key: 'city', label: '市区町村', required: true },
    { key: 'address_line1', label: '住所', required: true },
    {
      key: 'address_line2',
      label: '建物名・部屋番号',
      required: false,
    },
  ]
  onMounted(async () => {
    try {
      const profile = await getBuyerAccountProfile()
      Object.assign(form, {
        name: profile.name,
        name_phonetic: profile.name_phonetic,
        email: profile.email,
        phone: profile.phone,
        postal_code: profile.postal_code,
        prefecture: profile.prefecture,
        city: profile.city,
        address_line1: profile.address_line1,
        address_line2: profile.address_line2,
      })

      if (route.query.emailChange === 'success') {
        message.value = 'メールアドレスを変更しました。'
      } else if (route.query.emailChange === 'invalid') {
        message.value = 'メールアドレス変更用リンクが無効または有効期限切れです。'
      }
    } catch {
      await router.replace({ name: 'login', query: { redirect: '/my-page/member-info' } })
    } finally {
      loading.value = false
    }
  })
  function validatePhonetic() {
    if (!form.name_phonetic || /^[ァ-ヺー\s]+$/u.test(form.name_phonetic)) {
      delete fieldErrors.name_phonetic
      return
    }

    fieldErrors.name_phonetic = 'フリガナは全角カタカナで入力してください。'
  }
  async function submit() {
    Object.keys(fieldErrors).forEach((key) => delete fieldErrors[key])
    validatePhonetic()
    if (Object.keys(fieldErrors).length) return

    submitting.value = true
    message.value = ''

    try {
      const data = await updateBuyerAccountProfile(form)
      Object.assign(form, data.profile)
      message.value = data.email_change_pending
        ? '確認メールを送信しました。メール内のリンクを開くと変更が完了します。'
        : '会員情報を更新しました。'
      toast.success('会員情報を更新しました。')
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

      toast.error('会員情報を更新できませんでした。')
    } finally {
      submitting.value = false
    }
  }
  return {
    router,
    route,
    loading,
    submitting,
    message,
    fieldErrors,
    form,
    fields,
    validatePhonetic,
    submit,
  }
}
