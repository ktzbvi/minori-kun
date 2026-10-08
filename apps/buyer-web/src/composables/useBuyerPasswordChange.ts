import axios from 'axios'
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { changeBuyerPassword } from '@/services/account/account.api'

export function useBuyerPasswordChange() {
  const router = useRouter()
  const submitting = ref(false)
  const fieldErrors = reactive<Record<string, string>>({})
  const form = reactive({ current_password: '', password: '', password_confirmation: '' })
  const passwordPolicy =
    'パスワードは8〜64文字で、大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。'
  function validatePassword() {
    delete fieldErrors.password
    delete fieldErrors.password_confirmation
    if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,64}$/.test(form.password)) {
      fieldErrors.password = passwordPolicy
    }
    if (
      form.password &&
      form.password_confirmation &&
      form.password !== form.password_confirmation
    ) {
      fieldErrors.password_confirmation = '新しいパスワードと確認用パスワードが一致しません。'
    }
  }
  async function submit() {
    Object.keys(fieldErrors).forEach((key) => delete fieldErrors[key])
    validatePassword()
    if (Object.keys(fieldErrors).length) return

    submitting.value = true
    try {
      await changeBuyerPassword(form)
      form.current_password = ''
      form.password = ''
      form.password_confirmation = ''
      toast.success('パスワードを変更しました。')
      await router.replace({ name: 'my-page' })
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
      toast.error('パスワードを変更できませんでした。')
    } finally {
      submitting.value = false
    }
  }
  return { router, submitting, fieldErrors, form, passwordPolicy, validatePassword, submit }
}
