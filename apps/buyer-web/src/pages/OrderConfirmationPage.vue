<script setup lang="ts">
import { ChevronLeft, Pencil } from 'lucide-vue-next'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { checkoutDeliveryAddress } from '@/lib/checkout'
import { useBuyerOrderConfirmation } from '@/composables/useBuyerOrderConfirmation'
const {
  router,
  route,
  cartQuery,
  producerGroups,
  selectedProducerGroup,
  total,
  canProceedToPayment,
  changeAddress,
  proceedToPayment,
  formatYen,
  unitPrice,
  lineTotal,
  regularLineTotal,
  deliveryFee,
  deliveryFeeItem,
  discountedDeliveryFee,
  shopTotal,
  lineAmount,
  regularLineAmount,
  discountedFee,
} = useBuyerOrderConfirmation()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="relative flex h-dvh w-full flex-col bg-[#f8faf6] sm:max-w-[375px] sm:shadow-sm">
      <header class="flex h-[65px] shrink-0 items-center border-b border-[#e3e9e3] bg-white px-4">
        <button
          class="grid size-9 place-items-center rounded-full border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="Back"
          @click="router.back()"
        >
          <ChevronLeft :size="22" stroke-width="2.5" />
        </button>
        <h1 class="m-0 ml-1 text-[16px] font-bold text-[#237d4a]">
          注文内容の確認
        </h1>
      </header>

      <section class="min-h-0 flex-1 overflow-y-auto px-3 pt-3 pb-[82px]">
        <section
          class="rounded-[7px] border border-[#dce5dc] bg-white p-3"
          aria-label="Delivery address"
        >
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="m-0 text-[11px] font-bold">お届け先</p>
              <p class="mt-1 mb-0 text-[13px] font-bold">{{ checkoutDeliveryAddress.name }}</p>
              <p class="mt-0.5 mb-0 text-[10px] leading-[1.55] text-[#647468]">
                〒{{ checkoutDeliveryAddress.postalCode }}
                <br />
                {{ checkoutDeliveryAddress.prefecture }}{{ checkoutDeliveryAddress.city
                }}{{ checkoutDeliveryAddress.addressLine1 }}
                {{ checkoutDeliveryAddress.addressLine2 }}
                <br />
                {{ checkoutDeliveryAddress.phone }}
              </p>
            </div>
            <button
              class="flex min-h-8 items-center gap-1 rounded-[4px] border-0 bg-transparent px-1 text-[11px] font-bold text-[#237f4b]"
              type="button"
              @click="changeAddress"
            >
              <Pencil :size="14" />
              変更
            </button>
          </div>
        </section>

        <section
          class="mt-3 rounded-[7px] border border-[#dce5dc] bg-white p-3"
          aria-label="Order summary"
        >
          <h2 class="m-0 text-[12px] font-bold">ご注文内容</h2>
          <section v-if="selectedProducerGroup" class="mt-2">
            <p class="m-0 text-[10px] font-bold text-[#237f4b]">
              {{ selectedProducerGroup.shopName }}
            </p>
            <div
              v-for="item in selectedProducerGroup.items"
              :key="item.id"
              class="mt-1 flex items-end justify-between gap-3 text-[11px]"
            >
              <p class="m-0 min-w-0 text-[#526259]">
                {{ item.product_name }} {{ item.variant_label }} × {{ item.quantity }}
              </p>
              <p class="m-0 shrink-0 whitespace-nowrap text-right">
                <span v-if="item.discount_bps" class="mr-1 text-[#8b978f] line-through">
                  {{ formatYen(regularLineAmount(item, selectedProducerGroup.items)) }}
                </span>
                <strong class="text-[#d94339]">
                  {{ formatYen(lineAmount(item, selectedProducerGroup.items)) }}
                </strong>
              </p>
            </div>
          </section>
          <div class="mt-3 border-t border-[#e6ece6] pt-2">
            <div class="flex justify-between text-[11px]">
              <span>商品合計（税込・送料込み）</span>
              <strong>{{ formatYen(total) }}</strong>
            </div>
            <div class="mt-2 flex justify-between text-[14px] font-bold">
              <span>お支払い合計</span>
              <strong class="text-[#d94339]">{{ formatYen(total) }}</strong>
            </div>
          </div>
        </section>

        <button
          class="mt-3 min-h-10 w-full rounded-[5px] border-0 bg-[#237f4b] text-[13px] font-bold text-white disabled:bg-[#9cbca7]"
          type="button"
          :disabled="!canProceedToPayment"
          @click="proceedToPayment"
        >
          決済画面へ進む
        </button>
      </section>

      <BuyerBottomNavigation active="cart" />
    </section>
  </main>
</template>
