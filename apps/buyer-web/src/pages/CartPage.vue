<script setup lang="ts">
import { ChevronLeft, PackageOpen, Search, ShoppingCart, Store, Trash2 } from 'lucide-vue-next'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useBuyerCart } from '@/composables/useBuyerCart'
const {
  router,
  cartQuery,
  updateCartItemMutation,
  removeCartItemMutation,
  cartItems,
  deliveryPrefecture,
  producerGroups,
  decreaseQuantity,
  increaseQuantity,
  removeLine,
  openSearch,
  goHome,
  proceedToOrderConfirmation,
  formatYen,
  unitPrice,
  lineTotal,
  deliveryFee,
  deliveryFeeItem,
  discountedDeliveryFee,
  shopSubtotal,
  lineAmount,
  regularLineAmount,
  discountedFee,
} = useBuyerCart()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="relative flex h-dvh w-full flex-col bg-[#f8faf6] sm:max-w-[375px] sm:shadow-sm">
      <header
        class="flex h-[65px] shrink-0 items-center justify-between border-b border-[#e3e9e3] bg-white px-4"
      >
        <div class="flex items-center gap-2">
          <button
            class="grid size-9 place-items-center rounded-full border-0 bg-transparent text-[#237d4a]"
            type="button"
            aria-label="Back"
            @click="router.back()"
          >
            <ChevronLeft :size="22" stroke-width="2.5" />
          </button>
          <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">カート</h1>
        </div>
        <div class="flex gap-2">
          <button
            class="grid size-9 place-items-center rounded-full border-0 bg-transparent text-[#627469]"
            type="button"
            aria-label="Search"
            @click="openSearch"
          >
            <Search :size="21" />
          </button>
          <span class="grid size-9 place-items-center text-[#627469]" aria-label="Cart">
            <ShoppingCart :size="21" />
          </span>
        </div>
      </header>

      <template v-if="cartItems.length">
        <section class="min-h-0 flex-1 overflow-y-auto px-3 pt-3 pb-[82px]">
          <p class="mb-2 text-[10px] text-[#708076]">
            ショップごとにお支払い手続きを行います。
          </p>
          <section
            v-for="group in producerGroups"
            :key="group.shopName"
            class="mb-2.5 rounded-[7px] border border-[#dce5dc] bg-white p-2"
            :aria-label="group.shopName"
          >
            <h2 class="mb-1.5 flex items-center gap-1 text-[12px] font-bold text-[#237f4b]">
              <Store :size="13" stroke-width="2.25" />
              {{ group.shopName }}
            </h2>
            <article
              v-for="(item, index) in group.items"
              :key="item.id"
              class="flex gap-2 py-1.5"
              :class="{ 'border-b border-[#e6ece6]': index < group.items.length - 1 }"
            >
              <img
                v-if="item.image_url"
                :src="item.image_url"
                :alt="item.product_name"
                class="size-[52px] shrink-0 rounded-[4px] object-cover"
              />
              <span v-else class="size-[52px] shrink-0 rounded-[4px] bg-[#e5eee7]" />
              <div class="min-w-0 flex-1">
                <h2 class="m-0 truncate text-[12px] font-bold">{{ item.product_name }}</h2>
                <p class="mt-0.5 mb-0 text-[10px] text-[#66776c]">{{ item.variant_label }}</p>
                <p class="mt-1 mb-0 text-[12px]">
                  <span v-if="item.discount_bps" class="mr-1 text-[#89968e] line-through">
                    {{ formatYen(regularLineAmount(item, group.items)) }}
                  </span>
                  <strong class="text-[#d94339]">
                    {{ formatYen(lineAmount(item, group.items)) }}
                  </strong>
                </p>
                <div class="mt-1 flex items-center gap-2">
                  <div class="flex h-6 overflow-hidden rounded-[3px] border border-[#dce5de]">
                    <button
                      class="grid w-6 place-items-center border-0 border-r border-[#dce5de] bg-white text-[#547064] disabled:text-[#c2cdc5]"
                      type="button"
                      :disabled="item.quantity === 1 || updateCartItemMutation.isPending.value"
                      aria-label="Decrease quantity"
                      @click="decreaseQuantity(item)"
                    >
                      -
                    </button>
                    <span class="grid min-w-6 place-items-center text-[12px] font-bold">
                      {{ item.quantity }}
                    </span>
                    <button
                      class="grid w-6 place-items-center border-0 border-l border-[#dce5de] bg-white text-[#547064] disabled:text-[#c2cdc5]"
                      type="button"
                      :disabled="
                        item.quantity === item.stock_quantity ||
                        updateCartItemMutation.isPending.value
                      "
                      aria-label="Increase quantity"
                      @click="increaseQuantity(item)"
                    >
                      +
                    </button>
                  </div>
                </div>
              </div>
              <button
                class="grid size-8 shrink-0 place-items-center rounded-full border-0 bg-transparent text-[#708076]"
                type="button"
                :disabled="removeCartItemMutation.isPending.value"
                :aria-label="item.product_name + 'を削除'"
                @click="removeLine(item)"
              >
                <Trash2 :size="17" />
              </button>
            </article>
            <div class="mt-1 flex items-center justify-between text-[11px] text-[#53645a]">
              <span>ショップ小計（{{ group.itemCount }}点・税込・送料込み）</span>
              <strong class="text-[#33443a]">{{ formatYen(group.subtotal) }}</strong>
            </div>
            <button
              class="mt-2 min-h-8 w-full rounded-[4px] border-0 bg-[#237f4b] text-[11px] font-bold text-white"
              type="button"
              @click="proceedToOrderConfirmation(group.producerId)"
            >
              このショップの商品を購入する
            </button>
          </section>
        </section>
      </template>

      <section v-else class="grid min-h-0 flex-1 place-items-center px-6 pb-[82px] text-center">
        <div>
          <span
            class="mx-auto mb-5 grid size-24 place-items-center rounded-[12px] border border-[#dce5dc] bg-white text-[#a9b8ad]"
          >
            <PackageOpen :size="52" stroke-width="1.25" />
          </span>
          <h2 class="m-0 text-[15px] font-bold text-[#37483d]">
            カートに商品がありません
          </h2>
          <p class="mt-2 mb-4 text-[11px] text-[#718075]">
            商品一覧から商品を選んでください
          </p>
          <button
            class="min-h-10 w-[205px] rounded-[5px] border-0 bg-[#237f4b] text-[13px] font-bold text-white"
            type="button"
            @click="goHome"
          >
            商品一覧へ戻る
          </button>
        </div>
      </section>

      <BuyerBottomNavigation active="cart" />
    </section>
  </main>
</template>
