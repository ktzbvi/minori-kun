<script setup lang="ts">
import axios from 'axios'
import { ChevronLeft, Leaf } from 'lucide-vue-next'
import { computed, onMounted, reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { toast, UiButton, UiCard } from '@minorikun/ui'
import { queryClient } from '@/lib/query'
import { buyerAuthKeys } from '@/services/auth/auth.key'
import { completeBuyerRegistration } from '@/services/registration/registration.mutation'
import { getBuyerRegistrationStatus } from '@/services/registration/registration.query'

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
    label: '\u304a\u540d\u524d',
    placeholder: '\u5c71\u7530 \u592a\u90ce',
    required: true,
  },
  {
    name: 'name_phonetic',
    label: '\u30d5\u30ea\u30ac\u30ca',
    placeholder: '\u30e4\u30de\u30c0 \u30bf\u30ed\u30a6',
    required: true,
  },
  {
    name: 'password',
    label: '\u30d1\u30b9\u30ef\u30fc\u30c9',
    placeholder: '\u534a\u89d2\u82f1\u6570\u5b578\u6587\u5b57\u4ee5\u4e0a',
    required: true,
  },
  {
    name: 'password_confirmation',
    label: '\u30d1\u30b9\u30ef\u30fc\u30c9\u78ba\u8a8d',
    placeholder: '\u3082\u3046\u4e00\u5ea6\u5165\u529b\u3057\u3066\u304f\u3060\u3055\u3044',
    required: true,
  },
  {
    name: 'phone',
    label: '\u96fb\u8a71\u756a\u53f7',
    placeholder: '090-0000-0000',
    required: true,
  },
  {
    name: 'postal_code',
    label: '\u90f5\u4fbf\u756a\u53f7',
    placeholder: '123-4567',
    required: true,
  },
  {
    name: 'prefecture',
    label: '\u90fd\u9053\u5e9c\u770c',
    placeholder: '\u6771\u4eac\u90fd',
    required: true,
  },
  {
    name: 'city',
    label: '\u5e02\u533a\u753a\u6751',
    placeholder: '\u65b0\u5bbf\u533a',
    required: true,
  },
  {
    name: 'address_line1',
    label: '\u4f4f\u6240',
    placeholder: '\u897f\u65b0\u5bbf1-2-3',
    required: true,
  },
  {
    name: 'address_line2',
    label: '\u5efa\u7269\u540d\u30fb\u90e8\u5c4b\u756a\u53f7',
    placeholder: '\u30b5\u30f3\u30d7\u30eb\u30d3\u30eb101',
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
  if (!form.name_phonetic || /^[\u30a1-\u30fa\u30fc\s]+$/u.test(form.name_phonetic)) {
    delete fieldErrors.name_phonetic
    return
  }

  fieldErrors.name_phonetic = '\u30d5\u30ea\u30ac\u30ca\u306f\u5168\u89d2\u30ab\u30bf\u30ab\u30ca\u3067\u5165\u529b\u3057\u3066\u304f\u3060\u3055\u3044\u3002'
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
    toast.success('\u767b\u9332\u3057\u307e\u3057\u305f\u3002')
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

    toast.error('\u767b\u9332\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section
      class="flex h-dvh w-full max-w-[375px] flex-col overflow-hidden bg-[#fbfcfa] sm:h-[728px] sm:shadow-sm"
    >
      <header class="flex h-[65px] shrink-0 items-center gap-3 border-b border-[#e1e8e1] px-5">
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#267c4a]"
          type="button"
          aria-label="Back"
          @click="router.back()"
        >
          <ChevronLeft :size="22" />
        </button>
        <h1 class="text-[16px] font-bold text-[#227644]">&#26032;&#35215;&#20250;&#21729;&#30331;&#37682;</h1>
      </header>

      <ol class="flex shrink-0 justify-between border-b border-[#e1e8e1] px-4 py-3 text-[10px] text-[#8a978e]">
        <li class="text-[#237f4b]">&#10003; &#12513;&#12540;&#12523;&#20837;&#21147;</li>
        <li class="text-[#237f4b]">&#10003; &#12513;&#12540;&#12523;&#30906;&#35469;</li>
        <li class="font-bold">&#9314; &#24773;&#22577;&#20837;&#21147;</li>
      </ol>

      <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-3 scroll-pb-5">
        <UiCard class="!rounded-[11px] !border-[#dbe5dc] !bg-white !p-3 !shadow-none">
          <div class="mb-4 flex items-center justify-center gap-2">
            <span class="grid size-8 place-items-center rounded-full bg-[#e4f3e9] text-[#237d4a]">
              <Leaf :size="19" />
            </span>
            <strong class="text-[17px] text-[#217848]">&#12415;&#12398;&#12426;&#12367;&#12435;</strong>
          </div>

          <form id="buyer-registration-details" class="grid gap-2" @submit.prevent="submit">
            <div>
              <label class="label">&#12513;&#12540;&#12523;&#12450;&#12489;&#12524;&#12473;</label>
              <p class="input verified">
                {{ email }}
                <b>&#10003;&#30906;&#35469;&#28168;&#12415;</b>
              </p>
            </div>

            <div v-for="field in fields" :key="field.name">
              <label class="label" :for="`registration-${field.name}`">
                {{ field.label }}
                <span v-if="field.required" class="required">&#24517;&#38920;</span>
              </label>
              <input
                :id="`registration-${field.name}`"
                v-model="form[field.name]"
                :type="field.name.includes('password') ? 'password' : 'text'"
                :required="field.required"
                :placeholder="field.placeholder"
                :class="['input', { 'input-error': fieldErrors[field.name] }]"
                :aria-describedby="
                  fieldErrors[field.name] ? `registration-${field.name}-error` : undefined
                "
                :aria-invalid="Boolean(fieldErrors[field.name])"
                @blur="field.name === 'name_phonetic' && validatePhonetic()"
              />
              <p
                v-if="fieldErrors[field.name]"
                :id="`registration-${field.name}-error`"
                class="field-error"
                role="alert"
              >
                {{ fieldErrors[field.name] }}
              </p>
            </div>
          </form>
        </UiCard>
      </div>

      <footer
        class="shrink-0 border-t border-[#e1e8e1] bg-white px-4 pt-2.5 pb-[max(0.75rem,env(safe-area-inset-bottom))]"
      >
        <label class="flex items-center gap-2 text-[11px]">
          <input v-model="form.terms_accepted" type="checkbox" class="size-4 accent-[#237f4b]" />
          &#21033;&#29992;&#35215;&#32004;&#12395;&#21516;&#24847;&#12377;&#12427;
        </label>
        <UiButton
          form="buyer-registration-details"
          class="mt-2 block w-full mx-auto disabled:!border-[#9cbca7] disabled:!bg-[#9cbca7] disabled:!text-white"
          type="submit"
          :disabled="!canSubmit"
        >
          <span v-if="submitting">&#30331;&#37682;&#20013;...</span>
          <span v-else>&#30331;&#37682;&#12377;&#12427;</span>
        </UiButton>
        <p class="mt-2 text-center text-[11px] text-[#78867d]">
          &#12377;&#12391;&#12395;&#12450;&#12459;&#12454;&#12531;&#12488;&#12434;&#12362;&#25345;&#12385;&#12398;&#26041;
          <RouterLink to="/login" class="font-bold text-[#237f4b] underline">
            &#12525;&#12464;&#12452;&#12531;
          </RouterLink>
        </p>
      </footer>
    </section>
  </main>
</template>

<style scoped>
.label {
  display: block;
  margin-bottom: 5px;
  font-size: 11px;
  font-weight: 700;
}

.required {
  border-radius: 3px;
  background: #d84444;
  padding: 1px 4px;
  font-size: 9px;
  color: #fff;
}

.input {
  box-sizing: border-box;
  width: 100%;
  min-height: 30px;
  border: 1px solid #dce5dc;
  border-radius: 5px;
  background: #fff;
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
  font-size: 11px;
  font-weight: 600;
}

.verified {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f4f7f2;
  color: #8a978e;
}

.verified b {
  font-size: 10px;
  color: #237f4b;
}
</style>
