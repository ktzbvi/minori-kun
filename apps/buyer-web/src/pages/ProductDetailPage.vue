<script setup lang="ts">
import { ChevronLeft, Leaf, Search } from 'lucide-vue-next'
import BuyerCartButton from '@/components/layout/BuyerCartButton.vue'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useBuyerProductDetail } from '@/composables/useBuyerProductDetail'
const {
  router,
  catalogueQuery,
  addCartItemMutation,
  product,
  selectedVariantId,
  quantity,
  selectedVariant,
  canPurchase,
  decreaseQuantity,
  increaseQuantity,
  addToCart,
  openSearch,
  buyNow,
  formatYen,
  discountedPrice,
} = useBuyerProductDetail()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section
      class="relative min-h-screen w-full bg-[#f8faf6] pb-[64px] sm:min-h-[728px] sm:max-w-[375px] sm:shadow-sm"
    >
      <header
        class="flex h-[65px] items-center justify-between border-b border-[#e3e9e3] bg-white px-4"
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
          <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">商品詳細</h1>
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
          <BuyerCartButton />
        </div>
      </header>
      <template v-if="product && selectedVariant">
        <div class="aspect-[1.52] overflow-hidden bg-[#e5eee7]">
          <img
            v-if="product.image_url"
            :src="product.image_url"
            :alt="product.name"
            class="size-full object-cover"
          />
        </div>
        <main class="px-4 pt-3 pb-6">
          <h2 class="m-0 text-[22px] leading-[1.35] font-extrabold">{{ product.name }}</h2>
          <p class="mt-1 mb-0 text-[13px]">
            <span v-if="selectedVariant.discount_bps" class="mr-1 text-[#819086] line-through">
              {{ selectedVariant.price_yen.toLocaleString('ja-JP') }}円
            </span>
            <strong class="text-[17px] text-[#d94339]">
              {{ formatYen(discountedPrice(selectedVariant)) }}
            </strong>
          </p>
          <p class="mt-2 mb-0 text-[12px] leading-[1.65] text-[#63746a]">
            {{ product.description }}
          </p>
          <p class="mt-1 mb-6 flex items-center gap-1 text-[12px] font-bold text-[#237f4b]">
            <Leaf :size="14" />
            {{ product.shop_name }}
          </p>
          <section class="border-t border-[#e1e8e2] pt-4">
            <h3 class="m-0 text-[14px] font-bold">商品オプション</h3>
            <p class="mt-1 mb-2 text-[12px] text-[#617269]">セットタイプ</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="variant in product.variants"
                :key="variant.id"
                class="min-h-7 rounded-full border px-3 text-[12px] font-bold"
                :class="
                  selectedVariantId === variant.id
                    ? 'border-[#237f4b] bg-[#237f4b] text-white'
                    : 'border-[#dce5de] bg-white text-[#405047]'
                "
                type="button"
                @click="selectedVariantId = variant.id"
              >
                {{ variant.label }}
              </button>
            </div>
          </section>
          <section class="mt-4">
            <h3 class="m-0 text-[12px] font-bold text-[#617269]">数量</h3>
            <div
              class="mt-1.5 flex h-9 w-[108px] overflow-hidden rounded-[5px] border border-[#dce5de]"
            >
              <button
                class="w-9 border-0 border-r border-[#dce5de] bg-white text-lg text-[#547064] disabled:text-[#c2cdc5]"
                type="button"
                :disabled="quantity === 1"
                aria-label="Decrease quantity"
                @click="decreaseQuantity"
              >
                -
              </button>
              <span class="grid flex-1 place-items-center text-[13px] font-bold">
                {{ quantity }}
              </span>
              <button
                class="w-9 border-0 border-l border-[#dce5de] bg-white text-lg text-[#547064] disabled:text-[#c2cdc5]"
                type="button"
                :disabled="quantity === selectedVariant.stock_quantity"
                aria-label="Increase quantity"
                @click="increaseQuantity"
              >
                +
              </button>
            </div>
          </section>
          <div class="mt-5 grid gap-2">
            <button
              class="min-h-10 rounded-[5px] border-0 bg-[#237f4b] text-[14px] font-bold text-white disabled:bg-[#9cbca7]"
              type="button"
              :disabled="!canPurchase || addCartItemMutation.isPending.value"
              @click="addToCart"
            >
              カートに追加
            </button>
            <button
              class="min-h-10 rounded-[5px] border border-[#237f4b] bg-white text-[14px] font-bold text-[#237f4b]"
              type="button"
              :disabled="!canPurchase || addCartItemMutation.isPending.value"
              @click="buyNow"
            >
              今すぐ購入
            </button>
          </div>
        </main>
      </template>
      <div
        v-else
        class="grid min-h-[360px] place-items-center px-6 text-center text-[14px] text-[#63746a]"
      >
        <p v-if="catalogueQuery.isPending.value">商品を読み込んでいます。</p>
        <p v-else>商品を表示できません。</p>
      </div>
      <BuyerBottomNavigation />
    </section>
  </main>
</template>
