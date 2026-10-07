<script setup lang="ts">
import { Check } from 'lucide-vue-next'
import BuyerPageShell from '@/components/BuyerPageShell.vue'
import { useBuyerOrderComplete } from '@/composables/useBuyerOrderComplete'
const { route, router, orderId, order } = useBuyerOrderComplete()
</script>

<template>
  <BuyerPageShell active="profile">
    <header class="flex h-[65px] shrink-0 items-center border-b border-[#e3e9e3] bg-white px-4">
      <h1 class="m-0 text-base font-bold text-[#237d4a]">ご注文完了</h1>
    </header>
    <section class="flex flex-1 flex-col items-center px-5 pt-12 text-center">
      <div class="grid size-16 place-items-center rounded-full bg-[#e8f4ec] text-[#237f4b]">
        <Check :size="32" />
      </div>
      <h2 class="mt-5 mb-1 text-lg font-bold">ご注文を受け付けました</h2>
      <p v-if="order.data.value" class="text-xs text-[#68786e]">
        注文番号：{{ order.data.value.order_number }}
      </p>
      <p v-if="order.isLoading.value" class="text-xs text-[#68786e]">注文情報を確認しています...</p>
      <div class="mt-6 grid w-full gap-2">
        <button
          class="min-h-11 rounded-md border-0 bg-[#237f4b] text-sm font-bold text-white"
          type="button"
          @click="router.replace({ name: 'order-detail', params: { orderId } })"
        >
          注文詳細を見る
        </button>
        <button
          class="min-h-11 rounded-md border border-[#237f4b] bg-white text-sm font-bold text-[#237f4b]"
          type="button"
          @click="router.replace({ name: 'order-history' })"
        >
          注文履歴へ
        </button>
      </div>
    </section>
  </BuyerPageShell>
</template>
