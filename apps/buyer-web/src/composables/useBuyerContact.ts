import axios from 'axios'
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { createBuyerInquiry } from '@/services/contact/contact.api'

export function useBuyerContact() {
  type ContactForm = {
    subject: string
    message: string
  }
  const router = useRouter()
  const form = reactive<ContactForm>({
    subject: '',
    message: '',
  })
  const fieldErrors = reactive<Record<string, string>>({})
  const isSubmitting = ref(false)
  const idempotencyKey = ref(crypto.randomUUID())
  function clearErrors() {
    for (const key of Object.keys(fieldErrors)) {
      delete fieldErrors[key]
    }
  }
  function validate() {
    clearErrors()

    if (!form.subject.trim()) {
      fieldErrors.subject = '件名を入力してください。'
    }
    if (!form.message.trim()) {
      fieldErrors.message = 'お問い合わせ内容を入力してください。'
    }

    return Object.keys(fieldErrors).length === 0
  }
  async function submit() {
    if (!validate()) return

    isSubmitting.value = true

    try {
      const inquiry = await createBuyerInquiry({
        subject: form.subject.trim(),
        message: form.message.trim(),
        idempotency_key: idempotencyKey.value,
      })

      await router.replace({
        name: 'contact-complete',
        query: { reference: inquiry.reference_number },
      })
    } catch (error) {
      if (axios.isAxiosError(error) && error.response?.status === 422) {
        const errors = error.response.data.errors as Record<string, string[]>

        for (const [field, messages] of Object.entries(errors)) {
          fieldErrors[field] = messages[0] ?? '入力内容を確認してください。'
        }
        return
      }

      fieldErrors.form = '送信できませんでした。時間をおいてからもう一度お試しください。'
    } finally {
      isSubmitting.value = false
    }
  }
  return { router, form, fieldErrors, isSubmitting, idempotencyKey, clearErrors, validate, submit }
}
