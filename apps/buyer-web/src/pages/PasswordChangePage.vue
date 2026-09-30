<script setup lang="ts">
import axios from 'axios'
import { ChevronLeft } from 'lucide-vue-next'
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { toast, UiButton } from '@minorikun/ui'
import { changeBuyerPassword } from '@/services/account/account.api'

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
  if (form.password && form.password_confirmation && form.password !== form.password_confirmation) {
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
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="min-h-screen w-full max-w-[375px] bg-[#f8faf6] sm:min-h-[728px] sm:shadow-sm">
      <header class="flex h-[65px] items-center gap-3 border-b border-[#e3e9e3] bg-white px-4">
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="Back"
          @click="router.back()"
        >
          <ChevronLeft :size="22" :stroke-width="2.5" />
        </button>
        <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">
          &#12497;&#12473;&#12527;&#12540;&#12489;&#12398;&#22793;&#26356;
        </h1>
      </header>

      <p class="px-5 pt-3 text-[10px] text-[#718075]">
        &#12525;&#12464;&#12452;&#12531;&#12497;&#12473;&#12527;&#12540;&#12489;&#12434;&#22793;&#26356;&#12375;&#12414;&#12377;&#12290;
      </p>
      <form
        class="mx-5 mt-3 rounded-[8px] border border-[#dce5dc] bg-white p-3"
        novalidate
        @submit.prevent="submit"
      >
        <div class="field">
          <label for="current-password">
            &#29694;&#22312;&#12398;&#12497;&#12473;&#12527;&#12540;&#12489;
            <span class="required">&#24517;&#38920;</span>
          </label>
          <input
            id="current-password"
            v-model="form.current_password"
            class="input"
            type="password"
            autocomplete="current-password"
          />
          <p v-if="fieldErrors.current_password" class="field-error" role="alert">
            {{ fieldErrors.current_password }}
          </p>
        </div>
        <div class="field">
          <label for="password">
            &#26032;&#12375;&#12356;&#12497;&#12473;&#12527;&#12540;&#12489;
            <span class="required">&#24517;&#38920;</span>
          </label>
          <input
            id="password"
            v-model="form.password"
            class="input"
            type="password"
            autocomplete="new-password"
            @blur="validatePassword"
          />
          <p v-if="fieldErrors.password" class="field-error" role="alert">
            {{ fieldErrors.password }}
          </p>
        </div>
        <div class="field">
          <label for="password-confirmation">
            &#26032;&#12375;&#12356;&#12497;&#12473;&#12527;&#12540;&#12489;
            &#65288;&#30906;&#35469;&#65289;
            <span class="required">&#24517;&#38920;</span>
          </label>
          <input
            id="password-confirmation"
            v-model="form.password_confirmation"
            class="input"
            type="password"
            autocomplete="new-password"
            @blur="validatePassword"
          />
          <p v-if="fieldErrors.password_confirmation" class="field-error" role="alert">
            {{ fieldErrors.password_confirmation }}
          </p>
        </div>
        <UiButton class="!mt-1 !w-full" type="submit" :disabled="submitting">
          <span v-if="submitting">&#20445;&#23384;&#20013;...</span>
          <span v-else>&#22793;&#26356;&#20869;&#23481;&#12434;&#20445;&#23384;</span>
        </UiButton>
      </form>
    </section>
  </main>
</template>

<style scoped>
.field {
  margin-bottom: 11px;
}
.field label {
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
.field-error {
  margin: 4px 0 0;
  color: #b33a2b;
  font-size: 10px;
  font-weight: 600;
}
</style>
