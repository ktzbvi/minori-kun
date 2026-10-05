<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ChevronLeft, Download } from 'lucide-vue-next'
import { toast } from '@minorikun/ui'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import BuyerPageShell from '@/components/BuyerPageShell.vue'
import { cancelBuyerOrder, downloadBuyerReceipt } from '@/services/orders/orders.api'
import { buyerOrderKeys } from '@/services/orders/orders.key'
import { useBuyerOrderQuery } from '@/services/orders/orders.query'

const route = useRoute()
const router = useRouter()
const client = useQueryClient()
const orderId = computed(() => String(route.params.orderId ?? ''))
const order = useBuyerOrderQuery(orderId)
const confirmCancel = ref(false)
const cancellationKey = crypto.randomUUID()
const cancelMutation = useMutation({
  mutationFn: () => cancelBuyerOrder(orderId.value, cancellationKey),
  onSuccess: (data) => {
    client.setQueryData(buyerOrderKeys.detail(orderId.value), data)
    void client.invalidateQueries({ queryKey: buyerOrderKeys.all() })
    confirmCancel.value = false
    toast.success('注文をキャンセルしました。返金処理が完了しました。')
  },
  onError: () => toast.error('キャンセルできませんでした。注文状態をご確認ください。'),
})
const receiptMutation = useMutation({
  mutationFn: () =>
    downloadBuyerReceipt(orderId.value, order.data.value?.order_number ?? 'receipt'),
  onError: () => toast.error('領収書をダウンロードできませんでした。時間をおいて再度お試しください。'),
})

function yen(value: number) {
  return `${value.toLocaleString('ja-JP')}円`
}
function date(value: string) {
  return new Date(value).toLocaleString('ja-JP')
}
function fulfillmentLabel(value?: string) {
  return (
    ({ received: '受付', processing: '対応中', shipped: '発送済み' } as Record<string, string>)[
      value ?? ''
    ] ??
    value ??
    '-'
  )
}
</script>

<template>
  <BuyerPageShell active="profile">
    <header
      class="flex h-[60px] shrink-0 items-center gap-2 border-b border-[#e3e9e3] bg-white px-3"
    >
      <button
        class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
        type="button"
        aria-label="戻る"
        @click="router.back()"
      >
        <ChevronLeft :size="22" />
      </button>
      <h1 class="m-0 text-base font-bold text-[#237d4a]">注文詳細</h1>
    </header>
    <section
      v-if="order.data.value"
      class="min-h-0 flex-1 space-y-2 overflow-y-auto px-3 py-3 pb-[78px]"
    >
      <section class="rounded-md border border-[#dce5dc] bg-white p-3">
        <strong class="text-xs">{{ order.data.value.shop_name }}</strong>
        <p class="my-1 text-[10px]">注文番号 {{ order.data.value.order_number }}</p>
        <p class="my-1 text-[10px] text-[#68786e]">
          注文日時 {{ date(order.data.value.placed_at) }}
        </p>
        <span class="rounded bg-[#edf3f8] px-2 py-1 text-[10px]">
          注文：{{ order.data.value.order_state }}
        </span>
        <span class="ml-1 rounded bg-[#edf3f8] px-2 py-1 text-[10px]">
          発送：{{ fulfillmentLabel(order.data.value.producer_orders[0]?.fulfillment_state) }}
        </span>
        <p v-if="order.data.value.refund_state !== 'none'" class="mt-2 mb-0 text-xs text-[#237f4b]">
          返金：{{
            order.data.value.refund_state === 'refunded' ? '完了' : order.data.value.refund_state
          }}
        </p>
      </section>
      <section class="rounded-md border border-[#dce5dc] bg-white p-3">
        <h2 class="m-0 text-xs font-bold">ご注文内容</h2>
        <div
          v-for="item in order.data.value.producer_orders.flatMap((group) => group.items)"
          :key="item.id"
          class="flex gap-2 border-b border-[#e5ebe5] py-2 last:border-0"
        >
          <img
            v-if="item.image_url"
            :src="item.image_url"
            class="size-10 rounded object-cover"
            alt=""
          />
          <span v-else class="size-10 rounded bg-[#edf2ed]" />
          <div class="min-w-0 flex-1">
            <strong class="block text-[11px]">{{ item.product_name }} × {{ item.quantity }}</strong>
            <span v-if="item.discount_bps" class="text-[10px] text-[#237f4b]">
              {{ yen(item.unit_price_yen) }} → 割引適用
            </span>
            <span v-else class="text-[10px] text-[#68786e]">{{ yen(item.unit_price_yen) }}</span>
          </div>
          <strong class="text-[11px]">{{ yen(item.line_total_yen) }}</strong>
        </div>
        <p class="mt-2 mb-0 flex justify-between border-t border-[#e5ebe5] pt-2 text-sm font-bold">
          <span>合計（税込）</span>
          <span>{{ yen(order.data.value.total_yen) }}</span>
        </p>
      </section>
      <section class="rounded-md border border-[#dce5dc] bg-white p-3">
        <h2 class="m-0 text-xs font-bold">お届け先</h2>
        <p class="mb-0 text-[11px] leading-5">
          {{ order.data.value.delivery_address.recipient_name }}
          <br />
          〒{{ order.data.value.delivery_address.postal_code }}
          {{ order.data.value.delivery_address.prefecture
          }}{{ order.data.value.delivery_address.city
          }}{{ order.data.value.delivery_address.address_line1 }}
          <br />
          {{ order.data.value.delivery_address.phone }}
        </p>
      </section>
      <section
        v-if="order.data.value.can_cancel"
        class="rounded-md border border-[#dce5dc] bg-white p-3"
      >
        <p class="m-0 text-xs font-bold">
          キャンセル期限：{{ date(order.data.value.cancellation_deadline_at) }}
        </p>
        <p class="mt-1 mb-2 text-[10px] text-[#68786e]">注文から30分以内はキャンセルできます。</p>
        <button
          class="min-h-10 w-full rounded-md border border-[#d6382f] bg-white text-xs font-bold text-[#d6382f]"
          type="button"
          @click="confirmCancel = true"
        >
          注文をキャンセル
        </button>
      </section>
      <section
        v-else-if="order.data.value.order_state === '完了' && order.data.value.refund_state === 'none'"
        class="rounded-md border border-[#dce5dc] bg-white p-3"
      >
        <h2 class="m-0 text-xs font-bold">領収書</h2>
        <p class="my-2 text-[10px] text-[#68786e]">注文番号が記載されたPDFをダウンロードできます。</p>
        <button
          class="flex min-h-10 w-full items-center justify-center gap-2 rounded-md border border-[#237f4b] bg-white text-xs font-bold text-[#237f4b] disabled:opacity-60"
          type="button"
          :disabled="receiptMutation.isPending.value"
          @click="receiptMutation.mutate()"
        >
          <Download :size="16" />
          {{ receiptMutation.isPending.value ? 'ダウンロード中...' : '領収書をダウンロード' }}
        </button>
      </section>
      <section
        v-else-if="order.data.value.refund_state === 'refunded'"
        class="rounded-md bg-[#e8f4ec] p-3"
      >
        <strong class="text-xs">注文をキャンセルしました</strong>
        <p class="my-1 text-[10px]">返金額：{{ yen(order.data.value.total_yen) }}</p>
        <button
          class="mt-2 min-h-9 w-full rounded-md border-0 bg-[#237f4b] text-xs font-bold text-white"
          type="button"
          @click="router.replace({ name: 'order-history' })"
        >
          注文履歴に戻る
        </button>
      </section>
    </section>
    <p v-else-if="order.isLoading.value" class="flex-1 p-6 text-center text-xs text-[#68786e]">
      注文情報を読み込んでいます...
    </p>
    <p v-else class="flex-1 p-6 text-center text-xs text-[#b33a2b]">注文情報を表示できません。</p>
    <div
      v-if="confirmCancel"
      class="absolute inset-0 z-10 grid place-items-center bg-black/40 px-5"
      role="presentation"
    >
      <section
        class="w-full rounded-md bg-white p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cancel-title"
      >
        <h2 id="cancel-title" class="m-0 text-center text-sm font-bold">
          注文をキャンセルしますか？
        </h2>
        <p class="my-3 text-xs text-[#68786e]">
          このショップの注文のみキャンセルされ、返金されます。
        </p>
        <p class="my-3 rounded bg-[#f3f6f3] p-3 text-xs">
          {{ order.data.value?.shop_name }}
          <strong class="float-right">
            {{ order.data.value ? yen(order.data.value.total_yen) : '' }}
          </strong>
        </p>
        <div class="grid grid-cols-2 gap-2">
          <button
            class="min-h-10 rounded border border-[#dce5dc] bg-white text-xs font-bold"
            type="button"
            @click="confirmCancel = false"
          >
            戻る
          </button>
          <button
            class="min-h-10 rounded border-0 bg-[#d6382f] text-xs font-bold text-white disabled:opacity-60"
            type="button"
            :disabled="cancelMutation.isPending.value"
            @click="cancelMutation.mutate()"
          >
            キャンセルを確定する
          </button>
        </div>
      </section>
    </div>
  </BuyerPageShell>
</template>
