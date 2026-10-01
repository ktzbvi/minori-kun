<script setup lang="ts">
import axios from 'axios'
import { ChevronLeft } from 'lucide-vue-next'
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast, UiButton } from '@minorikun/ui'
import { getBuyerAccountProfile, updateBuyerAccountProfile } from '@/services/account/account.api'
import type { BuyerAccountProfileUpdate } from '@/types/account'

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
  { key: 'name', label: '\u304a\u540d\u524d', required: true },
  { key: 'name_phonetic', label: '\u30d5\u30ea\u30ac\u30ca', required: true },
  {
    key: 'email',
    label: '\u30e1\u30fc\u30eb\u30a2\u30c9\u30ec\u30b9',
    required: true,
    type: 'email',
  },
  { key: 'phone', label: '\u96fb\u8a71\u756a\u53f7', required: true, type: 'tel' },
  { key: 'postal_code', label: '\u90f5\u4fbf\u756a\u53f7', required: true },
  { key: 'prefecture', label: '\u90fd\u9053\u5e9c\u770c', required: true },
  { key: 'city', label: '\u5e02\u533a\u753a\u6751', required: true },
  { key: 'address_line1', label: '\u4f4f\u6240', required: true },
  {
    key: 'address_line2',
    label: '\u5efa\u7269\u540d\u30fb\u90e8\u5c4b\u756a\u53f7',
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
  if (!form.name_phonetic || /^[\u30a1-\u30fa\u30fc\s]+$/u.test(form.name_phonetic)) {
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
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section
      class="flex h-dvh w-full max-w-[375px] flex-col overflow-hidden bg-[#f8faf6] sm:h-[728px] sm:shadow-sm"
    >
      <header
        class="flex h-[65px] shrink-0 items-center gap-3 border-b border-[#e3e9e3] bg-white px-4"
      >
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="Back"
          @click="router.back()"
        >
          <ChevronLeft :size="22" :stroke-width="2.5" />
        </button>
        <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">
          &#20250;&#21729;&#24773;&#22577;&#12398;&#22793;&#26356;
        </h1>
      </header>

      <p class="shrink-0 border-b border-[#e3e9e3] px-5 py-2 text-[10px] text-[#718075]">
        &#30331;&#37682;&#28168;&#12415;&#12398;&#20250;&#21729;&#24773;&#22577;&#12434;&#30906;&#35469;&#12539;&#22793;&#26356;&#12391;&#12365;&#12414;&#12377;&#12290;
      </p>

      <form class="min-h-0 flex-1 overflow-y-auto px-4 py-3" novalidate @submit.prevent="submit">
        <p
          v-if="message"
          class="mb-3 rounded-[5px] bg-[#e4f3e9] px-3 py-2 text-[11px] text-[#237d4a]"
          role="status"
        >
          {{ message }}
        </p>
        <div v-if="loading" class="grid h-40 place-items-center text-[12px] text-[#718075]">
          &#35501;&#12415;&#36796;&#12415;&#20013;...
        </div>
        <div v-else class="rounded-[8px] border border-[#dce5dc] bg-white p-3">
          <div v-for="field in fields" :key="field.key" class="mb-2.5 last:mb-0">
            <label class="mb-1 block text-[11px] font-bold" :for="`member-${field.key}`">
              {{ field.label }}
              <span v-if="field.required" class="required">&#24517;&#38920;</span>
            </label>
            <input
              :id="`member-${field.key}`"
              v-model="form[field.key]"
              :type="field.type ?? 'text'"
              :autocomplete="field.key === 'email' ? 'email' : undefined"
              :class="['input', { 'input-error': fieldErrors[field.key] }]"
              :aria-invalid="Boolean(fieldErrors[field.key])"
              @blur="field.key === 'name_phonetic' && validatePhonetic()"
            />
            <p v-if="fieldErrors[field.key]" class="field-error" role="alert">
              {{ fieldErrors[field.key] }}
            </p>
          </div>
        </div>
      </form>

      <footer
        class="shrink-0 border-t border-[#dce5dc] bg-white p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]"
      >
        <UiButton class="!w-full" type="button" :disabled="loading || submitting" @click="submit">
          <span v-if="submitting">&#20445;&#23384;&#20013;...</span>
          <span v-else>&#22793;&#26356;&#20869;&#23481;&#12434;&#20445;&#23384;</span>
        </UiButton>
      </footer>
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
  width: 100%;
  min-height: 33px;
  box-sizing: border-box;
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
