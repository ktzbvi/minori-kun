<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import axios from 'axios'
import { ChevronLeft } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { UiButton, UiCard } from '@minorikun/ui'
import { ensureCsrfCookie } from '@/services/api'
import { completeBuyerPasswordReset } from '@/services/password-reset/password-reset.mutation'

const route = useRoute()
const router = useRouter()
const submissionError = ref('')
const email = typeof route.query.email === 'string' ? route.query.email : ''
const token = typeof route.query.token === 'string' ? route.query.token : ''
const linkIsComplete = computed(() => Boolean(email && token))

const passwordPolicy =
  'パスワードは8〜64文字で、大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。'

const schema = toTypedSchema(
  z
    .object({
      password: z.string().min(1, '新しいパスワードを入力してください。').max(64, passwordPolicy),
      password_confirmation: z.string().min(1, '確認用パスワードを入力してください。'),
    })
    .superRefine((values, context) => {
      if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,64}$/.test(values.password)) {
        context.addIssue({ code: 'custom', message: passwordPolicy, path: ['password'] })
      }

      if (values.password && values.password_confirmation && values.password !== values.password_confirmation) {
        context.addIssue({
          code: 'custom',
          message: '新しいパスワードと確認用パスワードが一致しません。',
          path: ['password_confirmation'],
        })
      }
    }),
)

const { defineField, errors, handleSubmit, isSubmitting } = useForm({
  validationSchema: schema,
  initialValues: { password: '', password_confirmation: '' },
})

const [password, passwordAttrs] = defineField('password', (state) => ({
  validateOnBlur: false,
  validateOnChange: false,
  validateOnInput: false,
  validateOnModelUpdate: state.errors.length > 0,
}))
const [passwordConfirmation, passwordConfirmationAttrs] = defineField(
  'password_confirmation',
  (state) => ({
    validateOnBlur: false,
    validateOnChange: false,
    validateOnInput: false,
    validateOnModelUpdate: state.errors.length > 0,
  }),
)

const submit = handleSubmit(
  async (values) => {
    submissionError.value = ''

    if (!linkIsComplete.value) {
      submissionError.value = '再設定用リンクが無効です。もう一度メールを送信してください。'
      return
    }

    try {
      await completeBuyerPasswordReset({
        email,
        token,
        password: values.password,
        password_confirmation: values.password_confirmation,
      })
      await ensureCsrfCookie(true)
      await router.replace({ name: 'login', query: { passwordReset: 'success' } })
    } catch (error) {
      if (axios.isAxiosError(error) && error.response?.status === 422) {
        submissionError.value =
          error.response.data?.message ?? '入力内容を確認して、もう一度お試しください。'
        return
      }

      submissionError.value = '現在パスワードを変更できません。時間をおいてからもう一度お試しください。'
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
          &#12497;&#12473;&#12527;&#12540;&#12489;&#12398;&#22793;&#26356;
        </h1>
      </header>

      <p class="px-5 pt-3 text-[10px] text-[#78867d]">
        &#12525;&#12464;&#12452;&#12531;&#12497;&#12473;&#12527;&#12540;&#12489;&#12434;&#22793;&#26356;&#12375;&#12414;&#12377;&#12290;
      </p>

      <div class="px-5 pt-2 pb-12">
        <UiCard class="!rounded-[9px] !border-[#dbe5dc] !bg-white !p-3 !shadow-none">
          <form class="grid gap-3" novalidate @submit.prevent="submit">
            <div class="grid gap-1.5">
              <label class="text-[11px] font-bold text-[#24372b]" for="new-password">
                &#26032;&#12375;&#12356;&#12497;&#12473;&#12527;&#12540;&#12489;
                <span class="rounded bg-[#d84444] px-1 py-px text-[9px] text-white">
                  &#24517;&#38920;
                </span>
              </label>
              <input
                id="new-password"
                v-model="password"
                v-bind="passwordAttrs"
                class="min-h-[34px] rounded-[5px] border border-[#dce5dc] bg-white px-2.5 text-[12px] outline-none placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                type="password"
                autocomplete="new-password"
                placeholder="8&#25991;&#23383;&#20197;&#19978;&#12391;&#20837;&#21147;&#12375;&#12390;&#12367;&#12384;&#12373;&#12356;&#12290;"
                :aria-describedby="errors.password ? 'new-password-error' : undefined"
                :aria-invalid="Boolean(errors.password)"
              />
              <p v-if="errors.password" id="new-password-error" class="m-0 text-[11px] font-medium text-[#b33a2b]" role="alert">
                {{ errors.password }}
              </p>
            </div>

            <div class="grid gap-1.5">
              <label class="text-[11px] font-bold text-[#24372b]" for="new-password-confirmation">
                &#26032;&#12375;&#12356;&#12497;&#12473;&#12527;&#12540;&#12489; &#65288;&#30906;&#35469;&#65289;
                <span class="rounded bg-[#d84444] px-1 py-px text-[9px] text-white">
                  &#24517;&#38920;
                </span>
              </label>
              <input
                id="new-password-confirmation"
                v-model="passwordConfirmation"
                v-bind="passwordConfirmationAttrs"
                class="min-h-[34px] rounded-[5px] border border-[#dce5dc] bg-white px-2.5 text-[12px] outline-none placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                type="password"
                autocomplete="new-password"
                placeholder="&#12418;&#12358;&#19968;&#24230;&#20837;&#21147;&#12375;&#12390;&#12367;&#12384;&#12373;&#12356;&#12290;"
                :aria-describedby="errors.password_confirmation ? 'new-password-confirmation-error' : undefined"
                :aria-invalid="Boolean(errors.password_confirmation)"
              />
              <p v-if="errors.password_confirmation" id="new-password-confirmation-error" class="m-0 text-[11px] font-medium text-[#b33a2b]" role="alert">
                {{ errors.password_confirmation }}
              </p>
            </div>

            <p v-if="submissionError" class="m-0 text-[11px] font-medium text-[#b33a2b]" role="alert">
              {{ submissionError }}
            </p>

            <UiButton class="!w-full !min-h-[35px] !rounded-[4px] !text-[12px]" type="submit" :disabled="isSubmitting">
              <span v-if="isSubmitting">&#20445;&#23384;&#20013;...</span>
              <span v-else>&#22793;&#26356;&#20869;&#23481;&#12434;&#20445;&#23384;</span>
            </UiButton>
          </form>
        </UiCard>
      </div>
    </section>
  </main>
</template>
