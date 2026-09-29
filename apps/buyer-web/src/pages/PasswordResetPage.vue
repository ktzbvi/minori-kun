<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import axios from 'axios'
import { ChevronLeft, Leaf } from 'lucide-vue-next'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { UiButton, UiCard } from '@minorikun/ui'
import { startBuyerPasswordReset } from '@/services/password-reset/password-reset.mutation'

const router = useRouter()
const requestMessage = ref('')
const serviceError = ref('')

const schema = toTypedSchema(
  z.object({
    email: z
      .string()
      .trim()
      .min(1, 'メールアドレスを入力してください。')
      .email('正しいメールアドレスを入力してください。')
      .max(255, 'メールアドレスは255文字以内で入力してください。'),
  }),
)

const { defineField, errors, handleSubmit, isSubmitting } = useForm({
  validationSchema: schema,
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
    requestMessage.value = ''
    serviceError.value = ''

    try {
      const data = await startBuyerPasswordReset(values.email)
      requestMessage.value = data.message
    } catch (error) {
      if (axios.isAxiosError(error) && error.response?.status === 422) {
        return
      }

      serviceError.value = '現在メールを送信できません。時間をおいてからもう一度お試しください。'
    }
  },
  () => undefined,
)
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="min-h-screen w-full max-w-[375px] bg-[#fbfcfa] sm:min-h-[728px] sm:shadow-sm">
      <header class="flex h-[65px] items-center gap-3 border-b border-[#e1e8e1] px-5">
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#267c4a]"
          type="button"
          aria-label="Back"
          @click="router.back()"
        >
          <ChevronLeft :size="22" :stroke-width="2.5" aria-hidden="true" />
        </button>
        <h1 class="text-[16px] font-bold text-[#227644]">
          &#12497;&#12473;&#12527;&#12540;&#12489;&#12398;&#20877;&#35373;&#23450;
        </h1>
      </header>

      <div class="px-5 pt-3 pb-12">
        <UiCard class="!rounded-[9px] !border-[#dbe5dc] !bg-white !p-3 !shadow-none">
          <div class="mb-4 flex items-center justify-center gap-2">
            <span class="grid size-8 place-items-center rounded-full bg-[#e4f3e9] text-[#237d4a]">
              <Leaf :size="19" :stroke-width="2.5" aria-hidden="true" />
            </span>
            <strong class="text-[16px] text-[#217848]">&#12415;&#12398;&#12426;&#12367;&#12435;</strong>
          </div>

          <form class="grid gap-3" novalidate @submit.prevent="submit">
            <div class="grid gap-1.5">
              <label class="text-[11px] font-bold text-[#24372b]" for="password-reset-email">
                &#12513;&#12540;&#12523;&#12450;&#12489;&#12524;&#12473;
                <span class="rounded bg-[#d84444] px-1 py-px text-[9px] text-white">
                  &#24517;&#38920;
                </span>
              </label>
              <input
                id="password-reset-email"
                v-model="email"
                v-bind="emailAttrs"
                class="min-h-[34px] rounded-[5px] border border-[#dce5dc] bg-white px-2.5 text-[12px] outline-none placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                type="email"
                autocomplete="email"
                inputmode="email"
                placeholder="&#12513;&#12540;&#12523;&#12450;&#12489;&#12524;&#12473;&#12434;&#20837;&#21147;&#12375;&#12390;&#12367;&#12384;&#12373;&#12356;&#12290;"
                :aria-describedby="errors.email ? 'password-reset-email-error' : undefined"
                :aria-invalid="Boolean(errors.email)"
              />
              <p
                v-if="errors.email"
                id="password-reset-email-error"
                class="m-0 text-[11px] font-medium text-[#b33a2b]"
                role="alert"
              >
                {{ errors.email }}
              </p>
            </div>

            <UiButton class="!w-full !min-h-[35px] !rounded-[4px] !text-[12px]" type="submit" :disabled="isSubmitting">
              <span v-if="isSubmitting">&#36865;&#20449;&#20013;...</span>
              <span v-else>&#20877;&#35373;&#23450;&#12513;&#12540;&#12523;&#12434;&#36865;&#20449;</span>
            </UiButton>
          </form>
        </UiCard>

        <p v-if="requestMessage" class="mt-4 text-center text-[11px] font-medium text-[#237f4b]" role="status">
          {{ requestMessage }}
        </p>
        <p v-else class="mt-4 text-center text-[10px] leading-5 text-[#78867d]">
          &#20837;&#21147;&#12375;&#12383;&#12513;&#12540;&#12523;&#12450;&#12489;&#12524;&#12473;&#12395;&#20877;&#35373;&#23450;&#29992;&#12522;&#12531;&#12463;&#12434;&#36865;&#20449;&#12375;&#12414;&#12377;&#12290;
        </p>
        <p v-if="serviceError" class="mt-2 text-center text-[11px] font-medium text-[#b33a2b]" role="alert">
          {{ serviceError }}
        </p>
      </div>
    </section>
  </main>
</template>
