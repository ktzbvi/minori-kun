<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { ArrowLeft, LoaderCircle } from 'lucide-vue-next'
import { UiButton, UiCard } from '@minorikun/ui'
import OrderStatusBadge from '@/components/orders/OrderStatusBadge.vue'
import { useProducerOrderQuery } from '@/services/orders/order.query'

const route = useRoute()
const orderQuery = useProducerOrderQuery(() => String(route.params.id))
const order = computed(() => orderQuery.data.value)
const dateFormatter = new Intl.DateTimeFormat('ja-JP', {
  timeZone: 'Asia/Tokyo', dateStyle: 'medium', timeStyle: 'short',
})
</script>

<template>
  <section>
    <RouterLink to="/orders" class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-[#237f4b] underline underline-offset-4"><ArrowLeft class="size-4" aria-hidden="true" />注文一覧へ</RouterLink>
    <h1 class="mt-3 text-xl font-bold lg:text-2xl">注文の概要</h1>
    <UiCard v-if="orderQuery.isPending.value" class="mt-5 grid min-h-48 place-items-center p-5" role="status"><LoaderCircle class="size-6 animate-spin" aria-hidden="true" /><span class="sr-only">注文を読み込んでいます。</span></UiCard>
    <UiCard v-else-if="orderQuery.isError.value" class="mt-5 grid gap-4 p-5">
      <p class="text-sm text-[#7b3329]" role="alert">注文を確認できませんでした。注文一覧へ戻るか、再試行してください。</p>
      <UiButton variant="outline" :disabled="orderQuery.isFetching.value" @click="orderQuery.refetch()">再試行</UiButton>
    </UiCard>
    <UiCard v-else-if="order" class="mt-5 grid gap-5 p-5">
      <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0"><h2 class="break-all text-lg font-bold">#{{ order.display_id }}</h2><p class="mt-2 text-sm text-[#687a70]">注文日 {{ order.ordered_at ? dateFormatter.format(new Date(order.ordered_at)) : '未設定' }}</p></div>
        <OrderStatusBadge :order="order" />
      </header>
      <ul class="grid gap-3 border-t border-[#d6e2da] pt-4"><li v-for="item in order.items" :key="item.id" class="break-words text-sm font-medium">{{ item.product_name }} ×{{ item.quantity }}</li></ul>
      <p class="rounded-lg bg-[#f3f6f3] p-3 text-sm leading-relaxed text-[#687a70]" role="status">配送情報の確認・配送対応状態の更新は現在準備中です。</p>
    </UiCard>
  </section>
</template>
