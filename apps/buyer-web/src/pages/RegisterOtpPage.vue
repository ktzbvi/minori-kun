<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import axios from 'axios'
import { ChevronLeft } from 'lucide-vue-next'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { toast, UiButton, UiCard } from '@minorikun/ui'
import { resendBuyerRegistrationOtp, verifyBuyerRegistrationOtp } from '@/services/registration/registration.mutation'
import { getBuyerRegistrationStatus } from '@/services/registration/registration.query'

const router = useRouter()
const route = useRoute()
const email = ref('')
const resendAvailableAt = ref<Date | null>(null)
const now = ref(Date.now())
let timer: ReturnType<typeof setInterval> | undefined

const otpSchema = toTypedSchema(
  z.object({
    code: z
      .string()
      .regex(
        /^\d{6}$/,
        '\u78ba\u8a8d\u30b3\u30fc\u30c9\u30926\u6841\u306e\u6570\u5b57\u3067\u5165\u529b\u3057\u3066\u304f\u3060\u3055\u3044\u3002',
      ),
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
  if (canResend.value) return '\u8a8d\u8a3c\u30b3\u30fc\u30c9\u3092\u518d\u9001\u3059\u308b'

  const minutes = Math.floor(secondsUntilResend.value / 60)
  const seconds = secondsUntilResend.value % 60
  return `\u518d\u9001\u3067\u304d\u308b\u307e\u3067 ${minutes}:${String(seconds).padStart(2, '0')}`
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
        toast.error(
          '\u78ba\u8a8d\u30b3\u30fc\u30c9\u304c\u6b63\u3057\u304f\u306a\u3044\u304b\u3001\u6709\u52b9\u671f\u9650\u304c\u5207\u308c\u3066\u3044\u307e\u3059\u3002',
        )
        return
      }

      toast.error(
        '\u78ba\u8a8d\u30b3\u30fc\u30c9\u306e\u78ba\u8a8d\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002',
      )
      return
    }

    if (!router.hasRoute('register-details')) {
      toast.success(
        '\u30e1\u30fc\u30eb\u30a2\u30c9\u30ec\u30b9\u3092\u78ba\u8a8d\u3057\u307e\u3057\u305f\u3002',
      )
      return
    }

    await router.push({ name: 'register-details', query: registrationRedirectQuery() })
    toast.success(
      '\u30e1\u30fc\u30eb\u30a2\u30c9\u30ec\u30b9\u3092\u78ba\u8a8d\u3057\u307e\u3057\u305f\u3002',
    )
  },
  () => undefined,
)

async function resend() {
  if (!canResend.value) return

  try {
    setStatus(await resendBuyerRegistrationOtp())
    setFieldValue('code', '', false)
    toast.success('\u8a8d\u8a3c\u30b3\u30fc\u30c9\u3092\u518d\u9001\u3057\u307e\u3057\u305f\u3002')
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.status === 429) {
      const availableAt = error.response.data?.data?.resend_available_at
      if (typeof availableAt === 'string') resendAvailableAt.value = new Date(availableAt)
      return
    }

    toast.error(
      '\u8a8d\u8a3c\u30b3\u30fc\u30c9\u306e\u518d\u9001\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002',
    )
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
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] py-0 text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="min-h-screen w-full max-w-[375px] bg-[#fbfcfa] sm:min-h-[728px] sm:shadow-sm">
      <header class="flex h-[65px] items-center gap-3 border-b border-[#e1e8e1] px-5">
        <button
          class="grid size-9 place-items-center rounded-full border-0 bg-transparent text-[#267c4a]"
          type="button"
          aria-label="Back"
          @click="goBack"
        >
          <ChevronLeft :size="22" :stroke-width="2.5" aria-hidden="true" />
        </button>
        <h1 class="m-0 text-[16px] font-bold text-[#227644]">
          &#x30E1;&#x30FC;&#x30EB;&#x78BA;&#x8A8D;
        </h1>
      </header>

      <div class="px-[33px] pt-3 pb-12">
        <ol
          class="m-0 flex list-none items-center justify-between px-0 text-[10px] text-[#8a978e]"
          aria-label="Registration progress"
        >
          <li class="font-bold text-[#237f4b]">
            &#x2713; &#x30E1;&#x30FC;&#x30EB;&#x5165;&#x529B;
          </li>
          <li aria-hidden="true">&#x203A;</li>
          <li class="font-bold text-[#237f4b]">
            &#x2461; &#x30E1;&#x30FC;&#x30EB;&#x78BA;&#x8A8D;
          </li>
          <li aria-hidden="true">&#x203A;</li>
          <li>&#x2462; &#x60C5;&#x5831;&#x5165;&#x529B;</li>
        </ol>

        <UiCard class="mt-3 !rounded-[11px] !border-[#dbe5dc] !bg-white !p-4.5 !shadow-none">
          <p class="m-0 text-center text-[12px] leading-5 text-[#69776e]">
            <span class="break-all">{{ email }}</span>
            &#x306B;&#x78BA;&#x8A8D;&#x30B3;&#x30FC;&#x30C9;&#x3092;&#x9001;&#x4FE1;&#x3057;&#x307E;&#x3057;&#x305F;&#x3002;
          </p>

          <form class="mt-4 grid gap-4" novalidate @submit.prevent="submit">
            <div class="grid gap-1.5">
              <label class="text-[13px] font-bold text-[#24372b]" for="registration-otp">
                &#x78BA;&#x8A8D;&#x30B3;&#x30FC;&#x30C9; (6&#x6841;)
                <span class="rounded bg-[#d84444] px-1 py-px text-[10px] text-white">
                  &#x5FC5;&#x9808;
                </span>
              </label>
              <input
                id="registration-otp"
                v-model="code"
                v-bind="codeAttrs"
                class="min-h-[39px] rounded-[6px] border border-[#dce5dc] bg-white px-2.5 text-left text-[16px] tracking-normal outline-none placeholder:text-left placeholder:tracking-normal placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                placeholder="6&#x6841;&#x306E;&#x6570;&#x5B57;"
                :aria-invalid="Boolean(errors.code)"
                :aria-describedby="errors.code ? 'registration-otp-error' : undefined"
              />
              <p
                v-if="errors.code"
                id="registration-otp-error"
                class="m-0 text-xs font-medium text-[#b33a2b]"
                role="alert"
              >
                {{ errors.code }}
              </p>
            </div>

            <UiButton
              class="!min-h-[37px] !rounded-[5px] !px-4 !text-[14px]"
              type="submit"
              :disabled="isSubmitting"
            >
              <span v-if="isSubmitting">&#x78BA;&#x8A8D;&#x4E2D;...</span>
              <span v-else>&#x78BA;&#x8A8D;&#x3057;&#x3066;&#x6B21;&#x3078;&#x9032;&#x3080;</span>
            </UiButton>
          </form>
        </UiCard>

        <div class="mt-5 grid justify-items-center gap-3 text-[12px]">
          <button
            class="border-0 bg-transparent p-0 font-bold text-[#237f4b] underline underline-offset-2 disabled:text-[#87958c]"
            type="button"
            :disabled="!canResend"
            @click="resend"
          >
            {{ resendLabel }}
          </button>
          <button
            class="border-0 bg-transparent p-0 font-medium text-[#6e7e74] underline underline-offset-2"
            type="button"
            @click="changeEmail"
          >
            &#x30E1;&#x30FC;&#x30EB;&#x30A2;&#x30C9;&#x30EC;&#x30B9;&#x3092;&#x5909;&#x66F4;&#x3059;&#x308B;
          </button>
        </div>
      </div>
    </section>
  </main>
</template>
