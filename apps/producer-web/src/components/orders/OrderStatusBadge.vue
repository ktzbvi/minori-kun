<script setup lang="ts">
import { computed } from 'vue'
import { Circle } from 'lucide-vue-next'
import type { ProducerOrderListItem } from '@/types/order'

const props = defineProps<{ order: ProducerOrderListItem }>()
const presentation = computed(() => {
  switch (props.order.operational_state) {
    case 'confirmed': return { label: '注文確定', tone: 'bg-[#ffefd4] text-[#a46112]' }
    case 'processing': return { label: '対応中', tone: 'bg-[#e0f3e7] text-[#137c49]' }
    case 'shipped': return { label: '発送済み', tone: 'bg-[#e3edff] text-[#245da8]' }
    case 'cancelled': return { label: 'キャンセル', tone: 'bg-[#edf1ee] text-[#687a70]' }
    default: return { label: '受付', tone: 'bg-[#fff1dc] text-[#a46112]' }
  }
})
const ownerLabel = computed(() => props.order.status_owner === 'producer'
  ? '手動更新可'
  : props.order.operational_state === 'confirmed' ? '自動更新' : 'システム管理')
const refundLabel = computed(() => {
  switch (props.order.refund_state) {
    case 'pending': return '返金：処理中'
    case 'requires_action': return '返金：確認が必要です'
    case 'canceled': return '返金：取消済み'
    case 'partial': return '返金：一部返金済み'
    case 'refunded': return '返金：返金済み'
    case 'failed': return '返金：確認が必要です'
    default: return ''
  }
})
</script>

<template>
  <div class="grid justify-items-start gap-1.5">
    <span class="inline-flex min-h-7 items-center justify-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold sm:text-sm" :class="presentation.tone">
      <Circle class="size-2.5 shrink-0 fill-current" aria-hidden="true" />
      {{ presentation.label }}
    </span>
    <span class="text-xs font-medium text-[#687a70]">{{ ownerLabel }}</span>
    <span v-if="refundLabel" class="text-xs font-medium text-[#687a70]">{{ refundLabel }}</span>
  </div>
</template>
