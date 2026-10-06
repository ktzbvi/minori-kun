<script setup lang="ts">
import axios from 'axios'
import { ChevronLeft } from 'lucide-vue-next'
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast, UiButton } from '@minorikun/ui'
import {
  checkoutDeliveryAddress,
  type CheckoutDeliveryAddress,
  initializeCheckoutDeliveryAddress,
  updateCheckoutDeliveryAddress,
} from '@/lib/checkout'
import { getBuyerAccountProfile, updateBuyerAccountProfile } from '@/services/account/account.api'

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
  { key: 'addressLine2', label: '建物名・部屋番号', autocomplete: 'address-line2', required: false },
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
  query: typeof route.query.producer === 'string' ? { producer: route.query.producer } : undefined,
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
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="flex h-dvh w-full flex-col bg-[#f8faf6] sm:max-w-[375px] sm:shadow-sm">
      <header
        class="flex h-[65px] shrink-0 items-center gap-2 border-b border-[#e3e9e3] bg-white px-4"
      >
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="Back"
          @click="cancel"
        >
          <ChevronLeft :size="22" :stroke-width="2.5" />
        </button>
        <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">お届け先情報の変更</h1>
      </header>

      <form class="min-h-0 flex-1 overflow-y-auto px-4 py-4" novalidate @submit.prevent="submit">
        <section class="rounded-[8px] border border-[#dce5dc] bg-white p-3">
          <div v-for="field in fields" :key="field.key" class="mb-3 last:mb-0">
            <label class="mb-1 block text-[11px] font-bold" :for="`checkout-${field.key}`">
              {{ field.label }}
              <span v-if="field.required !== false" class="required">必須</span>
            </label>
            <input
              :id="`checkout-${field.key}`"
              v-model="form[field.key]"
              :type="field.type ?? 'text'"
              :inputmode="field.inputmode"
              :autocomplete="field.autocomplete"
              :class="['input', { 'input-error': fieldErrors[field.key] }]"
              :aria-invalid="Boolean(fieldErrors[field.key])"
              @input="clearFieldError(field.key)"
            />
            <p v-if="fieldErrors[field.key]" class="field-error" role="alert">
              {{ fieldErrors[field.key] }}
            </p>
          </div>

          <label class="mt-4 flex items-center gap-2 text-[11px] text-[#35463b]">
            <input v-model="saveAsDefault" class="size-4 accent-[#237f4b]" type="checkbox" />
            基本のお届け先として保存する
          </label>

          <UiButton class="mt-4 !w-full" type="submit" :disabled="submitting">
            <span v-if="submitting">保存中...</span>
            <span v-else>このお届け先を使用</span>
          </UiButton>
        </section>
      </form>
    </section>
  </main>
</template>

<style scoped>
.required {
  border-radius: 3px;
  background: #d84444;
  padding: 1px 4px;
  font-size: 9px;
  color: #fff;
}

.input {
  box-sizing: border-box;
  min-height: 34px;
  width: 100%;
  border: 1px solid #dce5dc;
  border-radius: 5px;
  padding: 0 9px;
  font-size: 12px;
  outline: none;
}

.input:focus {
  border-color: #237f4b;
  box-shadow: 0 0 0 3px rgb(35 127 75 / 15%);
}

.input-error {
  border-color: #d84444;
}

.field-error {
  margin: 4px 0 0;
  color: #b33a2b;
  font-size: 10px;
  font-weight: 600;
}
</style>
