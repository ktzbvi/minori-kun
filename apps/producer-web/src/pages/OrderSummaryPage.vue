<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { ArrowLeft, Circle, ImageOff, LoaderCircle } from 'lucide-vue-next'
import { z } from 'zod'
import { UiButton, UiCard, UiDialog, UiFormItem, UiFormLabel, UiFormMessage, UiSelect, UiSelectTrigger, UiSelectContent, UiSelectItem, UiSelectValue, UiSkeleton } from '@minorikun/ui'
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
  <section class="min-w-0">
    <RouterLink to="/orders" class="inline-flex min-h-11 items-center gap-2 text-sm font-bold text-[#237f4b]"><ArrowLeft class="size-4" aria-hidden="true" />注文一覧へ</RouterLink>
    <h1 class="mt-2 text-2xl font-bold">注文詳細</h1>
    <div v-if="order" class="mt-2 flex flex-wrap gap-x-8 gap-y-1 text-sm text-[#687a70]">
      <p class="break-all">サブ注文番号 #{{ order.display_id }}</p>
      <p>注文日 {{ order.ordered_at ? dateFormatter.format(new Date(order.ordered_at)) : '未設定' }}</p>
    </div>
    <div v-if="orderQuery.isPending.value" class="mt-6 grid gap-5 lg:grid-cols-3" role="status" aria-label="注文を読み込んでいます。">
      <UiSkeleton class="h-80 lg:col-span-2" /><UiSkeleton class="h-80" />
    </div>
    <UiCard v-else-if="orderQuery.isError.value" class="mt-6 grid gap-4 p-6">
      <p role="alert" class="text-sm">注文を確認できませんでした。注文一覧へ戻るか、再試行してください。</p>
      <UiButton variant="outline" :disabled="orderQuery.isFetching.value" @click="orderQuery.refetch()">再試行</UiButton>
    </UiCard>
    <div v-else-if="order" class="mt-6 grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_20rem] xl:gap-6">
      <div class="grid min-w-0 gap-5 xl:gap-6">
        <UiCard class="min-w-0 rounded-xl border-[#d2e1d8] p-5 shadow-none sm:p-6">
          <header class="flex items-center justify-between gap-3"><h2 class="text-lg font-bold sm:text-xl">注文商品</h2><span class="text-sm text-[#687a70]">{{ order.items.length }}商品</span></header>
          <p v-if="!order.items.length" class="mt-6 text-sm text-[#687a70]">注文明細がありません。</p>
          <ul v-else class="mt-6 divide-y divide-[#d6e2da]">
            <li v-for="item in order.items" :key="item.id" class="min-w-0 py-5 first:pt-0 last:pb-0">
              <div class="flex items-center gap-4">
                <img v-if="item.image_url && !failedImages.includes(item.id)" :src="item.image_url" alt="" class="size-20 shrink-0 rounded-lg object-cover sm:size-24" @error="failedImages.push(item.id)">
                <div v-else class="grid size-20 shrink-0 place-items-center rounded-lg bg-[#f3f6f3] text-[#687a70] sm:size-24" aria-label="商品画像なし"><ImageOff class="size-6" aria-hidden="true" /></div>
                <div class="min-w-0"><h3 class="break-words text-base font-bold sm:text-lg">{{ item.product_name }}</h3><p v-if="item.discount_bps > 0" class="mt-1 text-sm text-[#ce503e]">{{ item.discount_bps / 100 }}% OFF</p></div>
              </div>
              <dl class="mt-5 grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] gap-3 text-sm">
                <div><dt class="text-[#687a70]">単価（税込）</dt><dd class="mt-2 font-bold">{{ yen(item.unit_price_yen) }}</dd><dd v-if="item.discount_bps > 0" class="mt-1 text-xs text-[#687a70]">割引前</dd></div>
                <div><dt class="text-[#687a70]">数量</dt><dd class="mt-2 font-bold">{{ item.quantity }}</dd></div>
                <div class="text-right"><dt class="text-[#687a70]">小計（税込）</dt><dd class="mt-2 font-bold">{{ yen(item.line_total_yen) }}</dd><dd v-if="item.discount_bps > 0" class="mt-1 text-xs text-[#687a70]">割引適用済み</dd></div>
              </dl>
            </li>
          </ul>
        </UiCard>
        <UiCard class="rounded-xl border-[#d2e1d8] p-5 shadow-none sm:p-6">
          <header class="flex flex-wrap items-center justify-between gap-2"><h2 class="text-lg font-bold sm:text-xl">配送先</h2><p class="text-xs text-[#687a70] sm:text-sm">配送に必要な情報のみ</p></header>
          <dl v-if="order.delivery_address" class="mt-6 grid gap-6 text-sm sm:gap-8">
            <div class="grid grid-cols-[6rem_minmax(0,1fr)] gap-3 sm:grid-cols-[8rem_minmax(0,1fr)]"><dt class="text-[#687a70]">受取人</dt><dd class="break-words font-bold">{{ order.delivery_address.recipient_name }}</dd></div>
            <div class="grid grid-cols-[6rem_minmax(0,1fr)] gap-3 sm:grid-cols-[8rem_minmax(0,1fr)]"><dt class="text-[#687a70]">配送先住所</dt><dd class="break-words leading-relaxed">〒{{ order.delivery_address.postal_code }}<br>{{ order.delivery_address.address }}<template v-if="order.delivery_address.address_line2"><br>{{ order.delivery_address.address_line2 }}</template></dd></div>
            <div class="grid grid-cols-[6rem_minmax(0,1fr)] gap-3 sm:grid-cols-[8rem_minmax(0,1fr)]"><dt class="text-[#687a70]">電話番号</dt><dd class="break-all font-bold">{{ order.delivery_address.phone }}</dd></div>
          </dl>
          <p v-else class="mt-6 text-sm text-[#687a70]">配送情報を確認できません。再読み込みしてください。</p>
        </UiCard>
      </div>
      <UiCard class="min-w-0 rounded-xl border-[#d2e1d8] p-5 shadow-none sm:p-6">
        <h2 class="text-lg font-bold sm:text-xl">配送対応状況</h2>
        <p class="mt-6 text-sm text-[#687a70]">現在の状態</p>
        <span class="mt-2 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold" :class="tones[order.fulfillment_state]"><Circle class="size-2.5 fill-current" aria-hidden="true" />{{ statuses[order.fulfillment_state] }}</span>
        <div v-if="order.status_owner === 'system'" class="mt-4 rounded-lg bg-[#f3f6f3] p-3"><p class="mb-2 text-xs text-[#687a70]">注文・返金状況</p><OrderStatusBadge :order="order" /></div>
        <p v-if="!order.can_update_fulfillment" class="mt-5 text-sm leading-relaxed text-[#687a70]" role="status">キャンセル・返金などのシステム管理中は、配送対応状況を更新できません。</p>
        <form class="mt-6 grid gap-4" @submit.prevent="requestConfirmation">
          <UiFormItem><UiFormLabel for="fulfillment">変更後の状態</UiFormLabel>
            <UiSelect v-model="selected" :disabled="!order.can_update_fulfillment || mutation.isPending.value" @update:model-value="fieldError = ''; success = ''">
              <UiSelectTrigger id="fulfillment" class="min-h-12 w-full" :aria-invalid="Boolean(fieldError)" :aria-describedby="fieldError ? 'fulfillment-error' : undefined"><UiSelectValue placeholder="状態を選択してください" /></UiSelectTrigger>
              <UiSelectContent><UiSelectItem v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</UiSelectItem></UiSelectContent>
            </UiSelect><UiFormMessage v-if="fieldError" id="fulfillment-error">{{ fieldError }}</UiFormMessage>
          </UiFormItem>
          <UiButton type="submit" class="min-h-12 w-full" :disabled="!canSubmit"><LoaderCircle v-if="mutation.isPending.value" class="size-4 animate-spin" aria-hidden="true" />{{ mutation.isPending.value ? '更新中…' : '更新する' }}</UiButton>
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
