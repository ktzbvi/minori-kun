import axios from 'axios'
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import {
  checkoutDeliveryAddress,
  type CheckoutDeliveryAddress,
  initializeCheckoutDeliveryAddress,
  updateCheckoutDeliveryAddress,
} from '@/lib/checkout'
import { getBuyerAccountProfile, updateBuyerAccountProfile } from '@/services/account/account.api'

export function useBuyerCheckoutAddress() {
  type AddressField = {
    key: keyof CheckoutDeliveryAddress
    label: string
    required?: boolean
    autocomplete?: string
    inputmode?: 'numeric' | 'tel'
    type?: 'tel' | 'text'
  }
  const router = useRouter()
  const route = useRoute()
  const submitting = ref(false)
  const saveAsDefault = ref(false)
  const fieldErrors = reactive<Partial<Record<keyof CheckoutDeliveryAddress, string>>>({})
  const form = reactive<CheckoutDeliveryAddress>({ ...checkoutDeliveryAddress })
  const fields: AddressField[] = [
    { key: 'name', label: '宛名', autocomplete: 'name' },
    { key: 'phone', label: '電話番号', autocomplete: 'tel', inputmode: 'tel', type: 'tel' },
    { key: 'postalCode', label: '郵便番号', autocomplete: 'postal-code', inputmode: 'numeric' },
    { key: 'prefecture', label: '都道府県', autocomplete: 'address-level1' },
    { key: 'city', label: '市区町村', autocomplete: 'address-level2' },
    { key: 'addressLine1', label: '番地・町名', autocomplete: 'address-line1' },
    {
      key: 'addressLine2',
      label: '建物名・部屋番号',
      autocomplete: 'address-line2',
      required: false,
    },
  ]
  onMounted(async () => {
    try {
      const profile = await getBuyerAccountProfile()

      initializeCheckoutDeliveryAddress({
        name: profile.name,
        phone: profile.phone,
        postalCode: profile.postal_code,
        prefecture: profile.prefecture,
        city: profile.city,
        addressLine1: profile.address_line1,
        addressLine2: profile.address_line2,
      })
      Object.assign(form, checkoutDeliveryAddress)
    } catch {
      toast.error('お届け先情報を読み込めませんでした。時間をおいてからもう一度お試しください。')
    }
  })
  const orderConfirmationLocation = computed(() => ({
    name: 'order-confirmation',
    query:
      typeof route.query.producer === 'string' ? { producer: route.query.producer } : undefined,
  }))
  function clearFieldError(field: keyof CheckoutDeliveryAddress) {
    delete fieldErrors[field]
  }
  function validate() {
    Object.keys(fieldErrors).forEach(
      (key) => delete fieldErrors[key as keyof CheckoutDeliveryAddress],
    )

    for (const field of fields) {
      if (field.required !== false && !form[field.key].trim()) {
        fieldErrors[field.key] = `${field.label}を入力してください。`
      }
    }

    if (form.phone && !/^0\d{2}-?\d{4}-?\d{4}$/.test(form.phone)) {
      fieldErrors.phone = '電話番号を正しい形式で入力してください。'
    }

    if (form.postalCode && !/^\d{3}-?\d{4}$/.test(form.postalCode)) {
      fieldErrors.postalCode = '郵便番号を7桁の数字で入力してください。'
    }

    return Object.keys(fieldErrors).length === 0
  }
  function applyServerErrors(error: unknown) {
    if (!axios.isAxiosError(error) || error.response?.status !== 422) return false

    const errors = error.response.data?.errors
    if (!errors || typeof errors !== 'object') return false

    const fieldMap: Record<string, keyof CheckoutDeliveryAddress> = {
      name: 'name',
      phone: 'phone',
      postal_code: 'postalCode',
      prefecture: 'prefecture',
      city: 'city',
      address_line1: 'addressLine1',
      address_line2: 'addressLine2',
    }

    Object.entries(errors).forEach(([field, messages]) => {
      const target = fieldMap[field]
      if (target && Array.isArray(messages) && typeof messages[0] === 'string') {
        fieldErrors[target] = messages[0]
      }
    })

    return true
  }
  async function saveDefaultAddress() {
    const profile = await getBuyerAccountProfile()

    await updateBuyerAccountProfile({
      name: form.name,
      name_phonetic: profile.name_phonetic,
      email: profile.email,
      phone: form.phone,
      postal_code: form.postalCode,
      prefecture: form.prefecture,
      city: form.city,
      address_line1: form.addressLine1,
      address_line2: form.addressLine2,
    })
  }
  async function submit() {
    if (!validate()) return

    submitting.value = true

    try {
      if (saveAsDefault.value) {
        await saveDefaultAddress()
      }

      updateCheckoutDeliveryAddress({ ...form })
      toast.success('お届け先情報を更新しました。')
      await router.replace(orderConfirmationLocation.value)
    } catch (error) {
      if (applyServerErrors(error)) return

      toast.error('お届け先情報を更新できませんでした。時間をおいてからもう一度お試しください。')
    } finally {
      submitting.value = false
    }
  }
  function cancel() {
    void router.replace(orderConfirmationLocation.value)
  }
  return {
    router,
    route,
    submitting,
    saveAsDefault,
    fieldErrors,
    form,
    fields,
    orderConfirmationLocation,
    clearFieldError,
    validate,
    applyServerErrors,
    saveDefaultAddress,
    submit,
    cancel,
  }
}
