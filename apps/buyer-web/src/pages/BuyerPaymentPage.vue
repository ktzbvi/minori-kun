<script setup lang="ts">
import { ChevronLeft } from 'lucide-vue-next'
import BuyerPageShell from '@/components/BuyerPageShell.vue'
import { useBuyerPayment } from '@/composables/useBuyerPayment'
const {
  route,
  router,
  cart,
  paymentMode,
  submitting,
  producerId,
  lines,
  shopName,
  idempotencyKey,
  itemTotal,
  deliveryFee,
  totalYen,
  completeFakePayment,
} = useBuyerPayment()
</script>

<template>
  <BuyerPageShell active="cart">
    <header class="flex h-[65px] shrink-0 items-center border-b border-[#e3e9e3] bg-white px-4">
      <button
        class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
        type="button"
        aria-label="戻る"
        @click="router.back()"
      >
        <ChevronLeft :size="22" />
      </button>
      <h1 class="m-0 ml-1 text-base font-bold text-[#237d4a]">決済</h1>
    </header>
    <section class="flex-1 overflow-y-auto px-4 py-4 pb-20">
      <section class="rounded-lg border border-[#dce5dc] bg-white p-4">
        <h2 class="m-0 text-sm font-bold">{{ shopName }} のご注文</h2>
        <p class="mt-3 mb-1 text-xs text-[#68786e]">{{ lines.length }} 点の商品</p>
        <p class="m-0 text-lg font-bold">{{ totalYen.toLocaleString('ja-JP') }}円</p>
      </section>
      <section
        v-if="paymentMode.data.value?.fake_enabled"
        class="mt-3 rounded-lg border border-[#e8d99a] bg-[#fffdf4] p-4"
      >
        <h2 class="m-0 text-sm font-bold text-[#7b5c05]">ローカルテスト決済</h2>
        <p class="mt-2 mb-0 text-xs leading-5 text-[#665b38]">
          これは開発・テスト用の決済です。実際の請求は発生せず、カード情報の入力もありません。
        </p>
        <button
          class="mt-4 min-h-11 w-full rounded-md border-0 bg-[#237f4b] text-sm font-bold text-white disabled:opacity-60"
          type="button"
          :disabled="submitting || !lines.length"
          @click="completeFakePayment"
        >
          {{ submitting ? '決済処理中...' : 'テスト決済を成功させる' }}
        </button>
      </section>
      <p v-else-if="paymentMode.isLoading.value" class="mt-4 text-center text-xs text-[#68786e]">
        決済設定を確認しています...
      </p>
      <p v-else class="mt-4 text-sm text-[#b33a2b]">
        決済を利用できません。時間をおいてもう一度お試しください。
      </p>
    </section>
  </BuyerPageShell>
</template>
