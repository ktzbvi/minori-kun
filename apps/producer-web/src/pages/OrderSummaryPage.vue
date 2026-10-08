<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { ChevronLeft, Circle, ImageOff, LoaderCircle } from 'lucide-vue-next'
import { z } from 'zod'
import { UiBadge, UiButton, UiCard, UiDialog, UiFormControl, UiFormItem, UiFormLabel, UiFormMessage, UiSelect, UiSelectTrigger, UiSelectContent, UiSelectItem, UiSelectValue, UiSkeleton } from '@minorikun/ui'
import OrderStatusBadge from '@/components/orders/OrderStatusBadge.vue'
import { useProducerOrderQuery } from '@/services/orders/order.query'
import { useUpdateProducerFulfillmentMutation } from '@/services/orders/order.mutation'
import { getApiErrorMessage } from '@/lib/api-error'


const route = useRoute()
const orderQuery = useProducerOrderQuery(() => String(route.params.id))
const mutation = useUpdateProducerFulfillmentMutation()
const order = computed(() => orderQuery.data.value)
const selected = ref('')
const confirmation = ref<z.infer<typeof schema> | null>(null)
const error = ref('')
const success = ref('')
const fieldError = ref('')
const failedImages = ref<string[]>([])
const schema = z.object({ fulfillment_state: z.enum(['received', 'processing', 'shipped']), expected_state: z.enum(['received', 'processing', 'shipped']) })
const statuses = { received: '受付', processing: '対応中', shipped: '発送済み' }
const tones = { received: 'bg-[#fff1dc] text-[#935c18]', processing: 'bg-[#e0f3e7] text-[#137c49]', shipped: 'bg-[#e3edff] text-[#245da8]' }
const dateFormatter = new Intl.DateTimeFormat('ja-JP', { timeZone: 'Asia/Tokyo', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false })
const yen = (value: number) => new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(value)
// P09-01: unit presentation applies the snapshot discount; totals remain authoritative.
const discountedUnitPrice = (item: { unit_price_yen: number; discount_bps: number }) => item.unit_price_yen - Math.round(item.unit_price_yen * item.discount_bps / 10000)
const canSubmit = computed(() => order.value?.can_update_fulfillment && selected.value && selected.value !== order.value.fulfillment_state && !mutation.isPending.value && !orderQuery.isFetching.value)
watch(() => route.params.id, () => { selected.value = ''; confirmation.value = null; error.value = ''; success.value = ''; failedImages.value = [] })
watch(() => order.value?.fulfillment_state, () => { selected.value = ''; confirmation.value = null })

function requestConfirmation() {
  error.value = ''; success.value = ''; fieldError.value = ''
  const parsed = schema.safeParse({ fulfillment_state: selected.value, expected_state: order.value?.fulfillment_state })
  if (!parsed.success) { fieldError.value = '配送対応状況を選択してください。'; return }
  if (canSubmit.value) confirmation.value = parsed.data
}

async function update() {
  if (!confirmation.value || !order.value || mutation.isPending.value) return
  const input = confirmation.value
  try {
    await mutation.mutateAsync({ id: order.value.id, ...input })
    selected.value = ''; confirmation.value = null
    success.value = '配送対応状況を更新しました。'
  } catch (cause) {
    confirmation.value = null
    error.value = getApiErrorMessage(cause) ?? '更新できませんでした。再読み込みしてから、もう一度お試しください。'
    await orderQuery.refetch()
  }
}
</script>

<template>
  <section class="min-w-0" :aria-busy="orderQuery.isFetching.value">
    <RouterLink to="/orders" class="inline-flex min-h-11 items-center gap-2 text-sm font-bold text-[#237f4b] sm:text-base"><ChevronLeft class="size-5" aria-hidden="true" />注文一覧へ</RouterLink>
    <h1 class="mt-2 text-2xl font-extrabold text-[#17241d] sm:text-3xl">注文詳細</h1>
    <div v-if="order" class="mt-2 flex flex-wrap gap-x-6 gap-y-2 text-sm text-[#687a70] sm:text-base">
      <p class="break-all">注文番号 #{{ order.display_id }}</p>
      <p>注文日 {{ order.ordered_at ? dateFormatter.format(new Date(order.ordered_at)) : '未設定' }}</p>
    </div>
    <div v-if="orderQuery.isPending.value" class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1.9fr)_minmax(18rem,1fr)] xl:gap-6" role="status">
      <span class="sr-only">注文を読み込んでいます。</span>
      <div class="grid gap-5" aria-hidden="true"><UiSkeleton class="h-72 rounded-2xl" /><UiSkeleton class="h-80 rounded-2xl" /></div>
      <UiSkeleton class="h-80 rounded-2xl" aria-hidden="true" />
    </div>
    <UiCard v-else-if="orderQuery.isError.value" class="mt-6 grid gap-4 p-6">
      <p role="alert" class="text-sm">注文を確認できませんでした。注文一覧へ戻るか、再試行してください。</p>
      <UiButton variant="outline" :disabled="orderQuery.isFetching.value" @click="orderQuery.refetch()">再試行</UiButton>
    </UiCard>
    <div v-else-if="order" class="mt-6 grid items-start gap-5 sm:gap-6 xl:grid-cols-[minmax(0,1.9fr)_minmax(18rem,1fr)]">
      <div class="grid min-w-0 gap-5 sm:gap-6">
        <UiCard class="min-w-0 rounded-2xl border-[#d2e1d8] p-5 shadow-none sm:p-6 xl:p-7">
          <header class="flex items-center justify-between gap-3 xl:border-b xl:border-[#dce5df] xl:pb-4"><h2 class="text-xl font-bold">注文商品</h2><span class="text-sm text-[#687a70]">{{ order.items.length }}商品</span></header>
          <p v-if="!order.items.length" class="mt-6 text-sm text-[#687a70]">注文明細がありません。</p>
          <template v-else>
            <div class="mt-4 hidden grid-cols-[minmax(0,1fr)_6rem_3rem_6rem] items-center gap-3 rounded-xl bg-[#f3f6f3] px-3 py-3 text-xs text-[#687a70] xl:grid" aria-hidden="true">
              <span>商品</span><span class="text-right">単価（税込）</span><span class="text-center">数量</span><span class="text-right">小計（税込）</span>
            </div>
            <ul class="mt-6 divide-y divide-[#d6e2da] xl:mt-4">
              <li v-for="item in order.items" :key="item.id" class="grid min-w-0 gap-5 py-5 first:pt-0 last:pb-0 xl:grid-cols-[minmax(0,1fr)_6rem_3rem_6rem] xl:items-center xl:gap-3 xl:px-3 xl:pb-6">
                <div class="flex min-w-0 items-center gap-4 xl:gap-3">
                  <img v-if="item.image_url && !failedImages.includes(item.id)" :src="item.image_url" alt="" class="size-20 shrink-0 rounded-lg object-cover sm:size-24 xl:size-16 2xl:size-20" @error="failedImages.push(item.id)">
                  <div v-else class="grid size-20 shrink-0 place-items-center rounded-lg bg-[#f3f6f3] text-[#687a70] sm:size-24 xl:size-16 2xl:size-20" aria-label="商品画像なし"><ImageOff class="size-6" aria-hidden="true" /></div>
                  <div class="min-w-0">
                    <h3 class="break-words text-base font-bold sm:text-lg">{{ item.product_name }}</h3>
                    <UiBadge v-if="item.discount_bps > 0" variant="secondary" class="mt-2 border-transparent bg-[#ffe9e4] text-xs text-[#ce503e] sm:text-sm">{{ item.discount_bps / 100 }}%OFF適用</UiBadge>
                  </div>
                </div>
                <dl class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] gap-3 text-sm xl:contents">
                  <div class="min-w-0 xl:text-right"><dt class="text-[#687a70] xl:sr-only">単価（税込）</dt><dd class="mt-2 break-all text-base font-bold tabular-nums xl:mt-0">{{ yen(discountedUnitPrice(item)) }}</dd><dd v-if="item.discount_bps > 0" class="mt-1 text-xs text-[#687a70]"><span class="sr-only">割引前 </span><s>{{ yen(item.unit_price_yen) }}</s></dd></div>
                  <div class="xl:text-center"><dt class="text-[#687a70] xl:sr-only">数量</dt><dd class="mt-2 text-base font-bold tabular-nums xl:mt-0">{{ item.quantity }}</dd></div>
                  <div class="min-w-0 text-right"><dt class="text-[#687a70] xl:sr-only">小計（税込）</dt><dd class="mt-2 break-all text-base font-bold tabular-nums xl:mt-0">{{ yen(item.line_total_yen) }}</dd></div>
                </dl>
              </li>
            </ul>
          </template>
        </UiCard>
        <UiCard class="min-w-0 rounded-2xl border-[#d2e1d8] p-5 shadow-none sm:p-6 xl:p-7">
          <header class="flex flex-wrap items-center justify-between gap-2 xl:border-b xl:border-[#dce5df] xl:pb-4"><h2 class="text-xl font-bold">配送先</h2><p class="text-xs text-[#687a70] sm:text-sm">配送に必要な情報のみ</p></header>
          <dl v-if="order.delivery_address" class="mt-6 grid gap-7 text-sm sm:text-base xl:mt-0 xl:gap-0 xl:divide-y xl:divide-[#e0e9e3]">
            <div class="grid grid-cols-[5rem_minmax(0,1fr)] gap-3 sm:grid-cols-[7rem_minmax(0,1fr)] xl:py-6"><dt class="text-[#687a70]">受取人</dt><dd class="break-words font-bold">{{ order.delivery_address.recipient_name }}</dd></div>
            <div class="grid grid-cols-[5rem_minmax(0,1fr)] gap-3 sm:grid-cols-[7rem_minmax(0,1fr)] xl:py-6"><dt class="text-[#687a70]">配送先住所</dt><dd class="break-words leading-relaxed">〒{{ order.delivery_address.postal_code }}<br>{{ order.delivery_address.address }}<template v-if="order.delivery_address.address_line2"><br>{{ order.delivery_address.address_line2 }}</template></dd></div>
            <div class="grid grid-cols-[5rem_minmax(0,1fr)] gap-3 sm:grid-cols-[7rem_minmax(0,1fr)] xl:py-6"><dt class="text-[#687a70]">電話番号</dt><dd class="break-all font-bold xl:font-normal">{{ order.delivery_address.phone }}</dd></div>
          </dl>
          <p v-else class="mt-6 text-sm text-[#687a70]">配送情報を確認できません。再読み込みしてください。</p>
        </UiCard>
      </div>
      <UiCard class="min-w-0 rounded-2xl border-[#d2e1d8] p-5 shadow-none sm:p-6 xl:p-7">
        <h2 class="text-xl font-bold xl:border-b xl:border-[#dce5df] xl:pb-4">配送対応状況</h2>
        <p class="mt-6 text-sm text-[#687a70]">現在の状態</p>
        <UiBadge variant="secondary" class="mt-3 min-w-24 gap-2 border-transparent px-4 py-2 text-sm font-bold" :class="tones[order.fulfillment_state]"><Circle class="size-2.5 fill-current" aria-hidden="true" />{{ statuses[order.fulfillment_state] }}</UiBadge>
        <div v-if="order.status_owner === 'system'" class="mt-4 rounded-lg bg-[#f3f6f3] p-3"><p class="mb-2 text-xs text-[#687a70]">注文・返金状況</p><OrderStatusBadge :order="order" /></div>
        <p v-if="!order.can_update_fulfillment" class="mt-5 text-sm leading-relaxed text-[#687a70]" role="status">キャンセル・返金などのシステム管理中は、配送対応状況を更新できません。</p>
        <form class="mt-6 grid gap-5" @submit.prevent="requestConfirmation">
          <UiFormItem>
            <UiFormLabel for="fulfillment" class="text-sm font-normal text-[#687a70]">変更後の状態</UiFormLabel>
            <UiFormControl>
              <UiSelect v-model="selected" :disabled="!order.can_update_fulfillment || mutation.isPending.value" @update:model-value="fieldError = ''; success = ''">
                <UiSelectTrigger id="fulfillment" class="min-h-12 w-full rounded-xl text-sm" :aria-invalid="Boolean(fieldError)" :aria-describedby="fieldError ? 'fulfillment-error' : undefined"><UiSelectValue placeholder="状態を選択してください" /></UiSelectTrigger>
                <UiSelectContent><UiSelectItem v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</UiSelectItem></UiSelectContent>
              </UiSelect>
            </UiFormControl>
            <UiFormMessage v-if="fieldError" id="fulfillment-error">{{ fieldError }}</UiFormMessage>
          </UiFormItem>
          <UiButton type="submit" class="min-h-12 w-full rounded-xl text-base font-bold disabled:bg-[#e2e9e5] disabled:text-[#687a70] disabled:opacity-100" :disabled="!canSubmit"><LoaderCircle v-if="mutation.isPending.value" class="size-4 animate-spin" aria-hidden="true" />{{ mutation.isPending.value ? '更新中…' : '更新する' }}</UiButton>
        </form>
        <p v-if="success" class="mt-4 text-sm text-[#237f4b]" role="status">{{ success }}</p>
        <div v-if="error" class="mt-4 grid gap-3"><p class="text-sm text-[#a63c2c]" role="alert">{{ error }}</p><UiButton variant="outline" :disabled="orderQuery.isFetching.value" @click="orderQuery.refetch()">再読み込み</UiButton></div>
      </UiCard>
    </div>
    <UiDialog :open="Boolean(confirmation)" title="配送対応状況を更新しますか？" :description="confirmation ? `${statuses[confirmation.expected_state]}から${statuses[confirmation.fulfillment_state]}に変更します。購入者にも反映されます。` : undefined" @update:open="value => { if (!value && !mutation.isPending.value) confirmation = null }">
      <template #footer><UiButton variant="outline" :disabled="mutation.isPending.value" @click="confirmation = null">戻る</UiButton><UiButton :disabled="mutation.isPending.value" @click="update">{{ mutation.isPending.value ? '更新中…' : '変更を確定する' }}</UiButton></template>
    </UiDialog>
  </section>
</template>