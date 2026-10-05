<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import { ChevronLeft } from 'lucide-vue-next'
import { createBuyerProducerInquiry } from '@/services/orders/orders.api'
import { useBuyerOrderQuery } from '@/services/orders/orders.query'

const route = useRoute()
const router = useRouter()
const orderId = computed(() => String(route.params.orderId ?? ''))
const order = useBuyerOrderQuery(orderId)
const topics = ['商品について', '配送・到着予定について', '商品の不備・不足について', 'その他']
const form = reactive({ topic: '', message: '' })
const submitted = ref(false)
const isSubmitting = ref(false)
const formError = ref('')
const idempotencyKey = ref(crypto.randomUUID())
const producerOrder = computed(() => order.data.value?.producer_orders[0])
const visibleItems = computed(() => producerOrder.value?.items.slice(0, 2) ?? [])
const additionalCount = computed(() => Math.max((producerOrder.value?.items.length ?? 0) - 2, 0))

function validate() {
  submitted.value = true
  return Boolean(form.topic && form.message.trim())
}

async function submit() {
  formError.value = ''
  if (!validate() || isSubmitting.value) return

  isSubmitting.value = true
  try {
    const result = await createBuyerProducerInquiry(orderId.value, {
      topic: form.topic,
      message: form.message.trim(),
      idempotency_key: idempotencyKey.value,
    })
    await router.replace({
      name: 'contact-complete',
      query: { reference: result.reference_number, type: 'producer' },
    })
  } catch (error) {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
      formError.value = '入力内容をご確認ください。'
    } else {
      formError.value = '送信できませんでした。時間をおいてからもう一度お試しください。'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section
      class="relative flex h-dvh w-full max-w-[375px] flex-col overflow-hidden bg-[#f8faf6] sm:h-[728px] sm:shadow-sm"
    >
      <header class="flex h-[65px] shrink-0 items-center gap-2 border-b border-[#e3e9e3] bg-white px-3">
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="注文詳細へ戻る"
          @click="router.back()"
        >
          <ChevronLeft :size="21" :stroke-width="2.5" />
        </button>
        <h1 class="m-0 text-[14px] font-bold text-[#237d4a]">生産者に問い合わせる</h1>
      </header>

      <div v-if="order.data.value && producerOrder" class="min-h-0 flex-1 overflow-y-auto px-5 pt-4 pb-5">
        <p class="m-0 mb-4 text-[10px] text-[#718075]">
          この注文について、{{ producerOrder.shop_name }}へ問い合わせます。
        </p>

        <section class="rounded-[8px] border border-[#dce5dc] bg-white p-3">
          <form class="grid gap-3" novalidate @submit.prevent="submit">
            <fieldset class="m-0 grid gap-1.5 border-0 p-0">
              <legend class="mb-1 text-[11px] font-bold">この注文について</legend>
              <div class="grid gap-1 rounded-[5px] border border-[#dce5dc] bg-[#f3f7f3] p-1.5">
                <div v-for="item in visibleItems" :key="item.id" class="flex min-w-0 items-center gap-2 bg-white p-1">
                  <img v-if="item.image_url" :src="item.image_url" alt="" class="size-7 shrink-0 rounded object-cover" />
                  <span v-else class="size-7 shrink-0 rounded bg-[#e5eee7]" />
                  <strong class="min-w-0 truncate text-[10px]">{{ item.product_name }} × {{ item.quantity }}</strong>
                </div>
                <p v-if="additionalCount" class="m-0 px-1 text-[9px] text-[#68786e]">
                  ほか{{ additionalCount }}点
                </p>
                <p class="m-0 px-1 text-[9px] text-[#68786e]">
                  {{ producerOrder.shop_name }}　注文番号 {{ order.data.value.order_number }}
                </p>
              </div>
            </fieldset>

            <fieldset class="m-0 grid gap-1 border-0 p-0">
              <legend class="mb-1 text-[11px] font-bold">
                お問い合わせ内容を選択
                <span class="rounded bg-[#d84444] px-1 py-px text-[9px] text-white">必須</span>
              </legend>
              <label
                v-for="(topic, index) in topics"
                :key="topic"
                :for="`inquiry-topic-${index}`"
                class="flex min-h-[31px] items-center gap-2 rounded-[5px] border px-2 text-[10px]"
                :class="form.topic === topic ? 'border-[#237f4b] bg-[#f2f8f3]' : 'border-[#dce5dc] bg-white'"
              >
                <input :id="`inquiry-topic-${index}`" v-model="form.topic" type="radio" name="topic" :value="topic" class="size-3 accent-[#237f4b]" />
                {{ topic }}
              </label>
              <p v-if="submitted && !form.topic" class="m-0 text-[10px] text-[#b33a2b]" role="alert">
                お問い合わせ内容を選択してください。
              </p>
            </fieldset>

            <div class="grid gap-1.5">
              <label for="producer-inquiry-message" class="text-[11px] font-bold">
                お問い合わせ内容
                <span class="rounded bg-[#d84444] px-1 py-px text-[9px] text-white">必須</span>
              </label>
              <textarea
                id="producer-inquiry-message"
                v-model="form.message"
                class="min-h-[76px] resize-y rounded-[5px] border border-[#dce5dc] bg-white px-2.5 py-2 text-[11px] outline-none placeholder:text-[#a7b0aa] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15"
                maxlength="5000"
                placeholder="できるだけ具体的に入力してください。"
              />
              <p v-if="submitted && !form.message.trim()" class="m-0 text-[10px] text-[#b33a2b]" role="alert">
                お問い合わせ内容を入力してください。
              </p>
              <p class="m-0 text-[9px] text-[#718075]">カード番号などの決済情報は入力しないでください。</p>
            </div>

            <p v-if="formError" class="m-0 text-[10px] text-[#b33a2b]" role="alert">
              {{ formError }}
            </p>
            <button class="min-h-[38px] w-full rounded-[4px] border-0 bg-[#237f4b] text-[11px] font-bold text-white disabled:opacity-60" type="submit" :disabled="isSubmitting">
              {{ isSubmitting ? '送信中...' : '問い合わせを送信' }}
            </button>
          </form>
        </section>
      </div>
      <p v-else-if="order.isLoading.value" class="m-0 p-6 text-center text-xs text-[#68786e]">
        注文情報を読み込んでいます...
      </p>
      <p v-else class="m-0 p-6 text-center text-xs text-[#b33a2b]">
        注文情報を表示できません。
      </p>
    </section>
  </main>
</template>
