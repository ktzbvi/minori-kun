<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import axios from 'axios'
import { ChevronLeft, Leaf } from 'lucide-vue-next'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { toast, UiButton, UiCard } from '@minorikun/ui'
import { authApi, buyerRegistrationApi } from '@/lib/api'

const router = useRouter()
const route = useRoute()

const registrationSchema = toTypedSchema(
  z.object({
    email: z
      .string()
      .trim()
      .min(
        1,
        '\u30e1\u30fc\u30eb\u30a2\u30c9\u30ec\u30b9\u3092\u5165\u529b\u3057\u3066\u304f\u3060\u3055\u3044\u3002',
      )
      .email(
        '\u6b63\u3057\u3044\u30e1\u30fc\u30eb\u30a2\u30c9\u30ec\u30b9\u3092\u5165\u529b\u3057\u3066\u304f\u3060\u3055\u3044\u3002',
      )
      .max(
        255,
        '\u30e1\u30fc\u30eb\u30a2\u30c9\u30ec\u30b9\u306f255\u6587\u5b57\u4ee5\u5185\u3067\u5165\u529b\u3057\u3066\u304f\u3060\u3055\u3044\u3002',
      ),
  }),
)

const { defineField, errors, handleSubmit, isSubmitting } = useForm({
  validationSchema: registrationSchema,
  initialValues: { email: '' },
})

const [email, emailAttrs] = defineField('email', (state) => ({
  validateOnBlur: false,
  validateOnChange: false,
  validateOnInput: false,
  validateOnModelUpdate: state.errors.length > 0,
}))

const submit = handleSubmit(
  async (values) => {
    try {
      await authApi.csrf()
      await buyerRegistrationApi.start(values.email)
      await router.push({ name: 'register-verify', query: registrationRedirectQuery() })
    } catch (error: unknown) {
      if (axios.isAxiosError(error) && error.response?.status === 429) {
        await router.push({ name: 'register-verify', query: registrationRedirectQuery() })
        return
      }

      toast.error(
        '\u8a8d\u8a3c\u30b3\u30fc\u30c9\u306e\u9001\u4fe1\u306b\u5931\u6557\u3057\u307e\u3057\u305f\u3002',
      )
    }
  },
  () => undefined,
)

function goBack() {
  router.back()
}

function registrationRedirectQuery() {
  return typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
    ? { redirect: route.query.redirect }
    : {}
}
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
          &#x65B0;&#x898F;&#x4F1A;&#x54E1;&#x767B;&#x9332;
        </h1>
      </header>

      <div class="px-[33px] pt-3 pb-12">
        <ol
          class="m-0 flex list-none items-center justify-between px-0 text-[10px] text-[#8a978e]"
          aria-label="Registration progress"
        >
          <li class="font-bold text-[#237f4b]">
            &#x2460; &#x30E1;&#x30FC;&#x30EB;&#x5165;&#x529B;
          </li>
          <li aria-hidden="true">&#x203A;</li>
          <li>&#x2461; &#x30E1;&#x30FC;&#x30EB;&#x78BA;&#x8A8D;</li>
          <li aria-hidden="true">&#x203A;</li>
          <li>&#x2462; &#x60C5;&#x5831;&#x5165;&#x529B;</li>
        </ol>

        <UiCard class="mt-3 !rounded-[11px] !border-[#dbe5dc] !bg-white !p-4.5 !shadow-none">
          <div class="mb-6 flex items-center justify-center gap-2.5">
            <span
              class="grid size-9 place-items-center rounded-full bg-[#e4f3e9] text-[#237d4a]"
              aria-hidden="true"
            >
              <Leaf :size="22" :stroke-width="2.5" />
            </span>
            <p class="m-0 text-[20px] font-extrabold tracking-[0.06em] text-[#217848]">
              &#x307F;&#x306E;&#x308A;&#x304F;&#x3093;
            </p>
          </div>

          <form class="grid gap-4" novalidate @submit.prevent="submit">
            <div class="grid gap-1.5">
              <label class="text-[13px] font-bold text-[#24372b]" for="registration-email">
                &#x30E1;&#x30FC;&#x30EB;&#x30A2;&#x30C9;&#x30EC;&#x30B9;
                <span class="rounded bg-[#d84444] px-1 py-px text-[10px] text-white">
                  &#x5FC5;&#x9808;
                </span>
              </label>
              <input
                id="registration-email"
                v-model="email"
                v-bind="emailAttrs"
                class="min-h-[39px] rounded-[6px] border border-[#dce5dc] bg-white px-2.5 text-[13px] outline-none placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                type="email"
                autocomplete="email"
                inputmode="email"
                placeholder="&#x30E1;&#x30FC;&#x30EB;&#x30A2;&#x30C9;&#x30EC;&#x30B9;&#x3092;&#x5165;&#x529B;&#x3057;&#x3066;&#x304F;&#x3060;&#x3055;&#x3044;&#x3002;"
                :aria-invalid="Boolean(errors.email)"
                :aria-describedby="errors.email ? 'registration-email-error' : undefined"
              />
              <p
                v-if="errors.email"
                id="registration-email-error"
                class="m-0 text-xs font-medium text-[#b33a2b]"
                role="alert"
              >
                {{ errors.email }}
              </p>
            </div>

            <UiButton
              class="!min-h-[37px] !rounded-[5px] !px-4 !text-[14px]"
              type="submit"
              :disabled="isSubmitting"
            >
              <span v-if="isSubmitting">&#x9001;&#x4FE1;&#x4E2D;...</span>
              <span v-else>&#x8A8D;&#x8A3C;&#x30B3;&#x30FC;&#x30C9;&#x3092;&#x9001;&#x4FE1;</span>
            </UiButton>
          </form>
        </UiCard>

        <p class="mt-5 text-center text-[12px] text-[#78867d]">
          &#x3059;&#x3067;&#x306B;&#x30A2;&#x30AB;&#x30A6;&#x30F3;&#x30C8;&#x3092;&#x304A;&#x6301;&#x3061;&#x306E;&#x65B9;
          <RouterLink
            class="ml-1 font-bold text-[#237f4b] underline underline-offset-2"
            to="/login"
          >
            &#x30ED;&#x30B0;&#x30A4;&#x30F3;
          </RouterLink>
        </p>
      </div>
    </section>
  </main>
</template>
