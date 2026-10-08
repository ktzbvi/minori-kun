<script setup lang="ts">
import { ChevronLeft, Download, Search, ShoppingCart } from 'lucide-vue-next'
import BuyerPageShell from '@/components/BuyerPageShell.vue'
import {
  orderStatusLabel,
  paymentStatusLabel,
  refundStatusLabel,
} from '@/services/orders/orders.status'
import { useBuyerOrderDetail } from '@/composables/useBuyerOrderDetail'
const {
  route,
  router,
  client,
  orderId,
  order,
  isCancelled,
  confirmCancel,
  cancellationKey,
  openSearch,
  openCart,
  cancelMutation,
  receiptMutation,
  yen,
  date,
  fulfillmentLabel,
} = useBuyerOrderDetail()
</script>

<template>
  <BuyerPageShell active="profile">
    <header
      class="flex h-[60px] shrink-0 items-center justify-between border-b border-[#e3e9e3] bg-white px-3"
    >
      <div class="flex items-center gap-2">
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="戻る"
          @click="router.back()"
        >
          <ChevronLeft :size="22" />
        </button>
        <h1 class="m-0 text-base font-bold text-[#237d4a]">注文詳細</h1>
      </div>
      <div class="flex gap-2">
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#627469]"
          type="button"
          aria-label="検索"
          @click="openSearch"
        >
          <Search :size="21" />
        </button>
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#627469]"
          type="button"
          aria-label="カート"
          @click="openCart"
        >
          <ShoppingCart :size="21" />
        </button>
      </div>
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
          注文：{{ orderStatusLabel(order.data.value.order_state) }}
        </span>
        <span v-if="!isCancelled" class="ml-1 rounded bg-[#edf3f8] px-2 py-1 text-[10px]">
          発送：{{ fulfillmentLabel(order.data.value.producer_orders[0]?.fulfillment_state) }}
        </span>
        <div v-if="isCancelled" class="mt-2 flex flex-wrap gap-x-3 text-[10px] text-[#68786e]">
          <span>支払：{{ paymentStatusLabel(order.data.value.payment_state) }}</span>
          <span v-if="refundStatusLabel(order.data.value.refund_state)">
            返金：{{ refundStatusLabel(order.data.value.refund_state) }}
          </span>
        </div>
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
        <button
          class="mt-2 flex min-h-9 w-full items-center justify-between border-0 border-t border-[#e5ebe5] bg-white pt-2 text-left text-[11px] font-bold text-[#237f4b]"
          type="button"
          @click="
            router.push({ name: 'producer-inquiry', params: { orderId: order.data.value.id } })
          "
        >
          生産者に問い合わせる
          <span aria-hidden="true" class="text-base text-[#68786e]">›</span>
        </button>
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
          {{ order.data.value.delivery_address.address_line2 }}
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
        v-else-if="
          order.data.value.order_state === '完了' && order.data.value.refund_state === 'none'
        "
        class="rounded-md border border-[#dce5dc] bg-white p-3"
      >
        <h2 class="m-0 text-xs font-bold">領収書</h2>
        <p class="my-2 text-[10px] text-[#68786e]">
          注文番号が記載されたPDFをダウンロードできます。
        </p>
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
      <section v-else-if="isCancelled" class="rounded-md border border-[#dce5dc] bg-[#eaf5ef] p-3">
        <strong class="text-xs">注文をキャンセルしました</strong>
        <p class="my-1 text-[10px]">返金額：{{ yen(order.data.value.total_yen) }}</p>
        <p
          v-if="['pending', 'requires_action'].includes(order.data.value.refund_state)"
          class="my-1 text-[10px] text-[#68786e]"
        >
          返金処理中です。カード明細への反映時期はカード会社により異なります。
        </p>
        <p
          v-else-if="order.data.value.refund_state === 'refunded'"
          class="my-1 text-[10px] text-[#68786e]"
        >
          返金処理が完了しました。カード明細への反映時期はカード会社により異なります。
        </p>
        <p
          v-else-if="['failed', 'canceled'].includes(order.data.value.refund_state)"
          class="my-1 text-[10px] text-[#b33a2b]"
        >
          返金に失敗したため、運営が確認しています。
        </p>
        <p v-else class="my-1 text-[10px] text-[#68786e]">
          返金{{
            refundStatusLabel(order.data.value.refund_state) ?? '状況確認中'
          }}です。カード会社により返金の反映時期が異なります。
        </p>
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
        class="w-full max-w-[17rem] rounded-md bg-white p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cancel-title"
      >
        <h2 id="cancel-title" class="m-0 text-center text-sm font-bold">
          注文をキャンセルしますか？
        </h2>
        <p class="my-2 text-[10px] text-[#68786e]">以下の注文をキャンセルします。</p>
        <div class="my-3 rounded-md bg-[#f3f6f3] p-3 text-[10px]">
          <strong class="block text-xs">{{ order.data.value?.shop_name }}</strong>
          <p class="my-1 text-[#68786e]">注文番号 {{ order.data.value?.order_number }}</p>
          <div class="mt-2 flex items-center justify-between gap-2">
            <span>返金対象額</span>
            <strong class="text-sm">
              {{ order.data.value ? yen(order.data.value.total_yen) : '' }}
            </strong>
          </div>
        </div>
        <p class="my-3 text-[10px] leading-relaxed text-[#68786e]">
          このショップの注文のみがキャンセルされます。
          <br />
          他のショップの注文はキャンセルされません。
        </p>
        <div class="grid grid-cols-1 gap-2">
          <button
            class="min-h-9 rounded border border-[#dce5dc] bg-white text-xs font-bold"
            type="button"
            @click="confirmCancel = false"
          >
            戻る
          </button>
          <button
            class="min-h-9 rounded border-0 bg-[#d6382f] text-xs font-bold text-white disabled:opacity-60"
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
