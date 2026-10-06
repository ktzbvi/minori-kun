<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ChevronLeft } from 'lucide-vue-next'
import { toast } from '@minorikun/ui'
import BuyerPageShell from '@/components/BuyerPageShell.vue'
import { checkoutDeliveryAddress, initializeCheckoutDeliveryAddress } from '@/lib/checkout'
import { queryClient } from '@/lib/query'
import { getBuyerAccountProfile } from '@/services/account/account.api'
import { buyerCartKeys } from '@/services/cart/cart.key'
import { useBuyerCartQuery } from '@/services/cart/cart.query'
import { createBuyerOrder } from '@/services/orders/orders.api'
import { usePaymentModeQuery } from '@/services/orders/orders.query'

const route = useRoute()
const router = useRouter()
const cart = useBuyerCartQuery()
const paymentMode = usePaymentModeQuery()
const submitting = ref(false)
const producerId = computed(() =>
  typeof route.query.producer === 'string' ? route.query.producer : '',
)
const lines = computed(() =>
  (cart.data.value?.items ?? []).filter((item) => item.producer_id === producerId.value),
)
const shopName = computed(() => lines.value[0]?.shop_name ?? '')
const idempotencyKey = ref(crypto.randomUUID())

onMounted(async () => {
  try {
    const profile = await getBuyerAccountProfile()
    initializeCheckoutDeliveryAddress({
      name: profile.name,
      phone: profile.phone,
      postalCode: profile.postal_code,
      prefecture: profile.prefecture,
      city: profile.city,
      addressLine1: profile.address_line1,
      addressLine2: profile.address_line2,
    })
  } catch {
    await router.replace({ name: 'login', query: { redirect: route.fullPath } })
  }
})

function itemTotal(item: (typeof lines.value)[number]) {
  return Math.round((item.unit_price_yen * (10000 - item.discount_bps)) / 10000) * item.quantity
}

function deliveryFee(item: (typeof lines.value)[number]) {
  const base =
    checkoutDeliveryAddress.prefecture === '北海道'
      ? item.delivery_fee_hokkaido_yen
      : checkoutDeliveryAddress.prefecture === '沖縄県'
        ? item.delivery_fee_okinawa_yen
        : item.delivery_fee_honshu_yen
  return Math.round((base * (10000 - item.discount_bps)) / 10000)
}

const totalYen = computed(
  () =>
    lines.value.reduce((sum, item) => sum + itemTotal(item), 0) +
    Math.max(0, ...lines.value.map(deliveryFee)),
)

async function completeFakePayment() {
  if (!producerId.value || !lines.value.length || submitting.value) return
  submitting.value = true
  try {
    const order = await createBuyerOrder({
      producer_id: producerId.value,
      idempotency_key: idempotencyKey.value,
      delivery_address: {
        name: checkoutDeliveryAddress.name,
        phone: checkoutDeliveryAddress.phone,
        postal_code: checkoutDeliveryAddress.postalCode,
        prefecture: checkoutDeliveryAddress.prefecture,
        city: checkoutDeliveryAddress.city,
        address_line1: checkoutDeliveryAddress.addressLine1,
        address_line2: checkoutDeliveryAddress.addressLine2,
      },
    })
    await queryClient.invalidateQueries({ queryKey: buyerCartKeys.all() })
    await router.replace({ name: 'order-complete', params: { orderId: order.id } })
  } catch {
    toast.error('決済を完了できませんでした。カートの内容と在庫をご確認ください。')
    idempotencyKey.value = crypto.randomUUID()
  } finally {
    submitting.value = false
  }
}
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
