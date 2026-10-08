import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export function useBuyerContactComplete() {
  const route = useRoute()
  const router = useRouter()
  const referenceNumber = computed(() => String(route.query.reference ?? ''))
  const isProducerInquiry = computed(() => route.query.type === 'producer')
  function returnToMyPage() {
    void router.replace({ name: 'my-page' })
  }
  return { route, router, referenceNumber, isProducerInquiry, returnToMyPage }
}
