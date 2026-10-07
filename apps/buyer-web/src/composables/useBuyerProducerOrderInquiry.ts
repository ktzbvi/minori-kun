import { computed, reactive, ref } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import { createBuyerProducerInquiry } from '@/services/orders/orders.api'
import { useBuyerOrderQuery } from '@/services/orders/orders.query'

export function useBuyerProducerOrderInquiry() {
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
  return {
    route,
    router,
    orderId,
    order,
    topics,
    form,
    submitted,
    isSubmitting,
    formError,
    idempotencyKey,
    producerOrder,
    visibleItems,
    additionalCount,
    validate,
    submit,
  }
}
