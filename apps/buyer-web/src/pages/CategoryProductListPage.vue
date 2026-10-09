<script setup lang="ts">
import { ChevronLeft, Search } from 'lucide-vue-next'
import BuyerCartButton from '@/components/layout/BuyerCartButton.vue'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useBuyerCategoryProductList } from '@/composables/useBuyerCategoryProductList'
const {
  catalogueQuery,
  addCartItemMutation,
  selectedCategory,
  categoryProducts,
  goBack,
  openSearch,
  openProduct,
  addToCart,
  productPrice,
  formatYen,
} = useBuyerCategoryProductList()
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
            @click="goBack"
          >
            <ChevronLeft :size="22" stroke-width="2.5" />
          </button>
          <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">カテゴリ商品一覧</h1>
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

      <section class="min-h-0 flex-1 overflow-y-auto px-4 pt-4 pb-[82px]">
        <h2 class="m-0 text-[16px] font-bold text-[#26362c]">{{ selectedCategory }}</h2>
        <p class="mt-1 mb-3 text-[11px] text-[#718075]">{{ categoryProducts.length }}件の商品</p>
        <div class="grid grid-cols-2 content-start gap-3">
          <article
            v-for="product in categoryProducts"
            :key="product.id"
            class="overflow-hidden rounded-[10px] border border-[#dce5dc] bg-white"
          >
            <button
              class="block w-full border-0 bg-transparent p-0 text-left"
              type="button"
              @click="openProduct(product.id)"
            >
              <div class="relative aspect-[1.35] overflow-hidden bg-[#e7eee8]">
                <img
                  v-if="product.image_url"
                  :src="product.image_url"
                  :alt="product.name"
                  class="size-full object-cover"
                />
                <span
                  v-if="product.variants[0]?.discount_bps"
                  class="absolute top-0 right-0 bg-[#df483f] px-2 py-1 text-[11px] font-extrabold text-white"
                >
                  {{ product.variants[0].discount_bps / 100 }}%
                </span>
              </div>
              <div class="px-2.5 pt-2">
                <h3 class="m-0 truncate text-[13px] font-bold text-[#29392f]">
                  {{ product.name }}
                </h3>
                <p class="mt-1 mb-0 min-h-[18px] text-[11px] leading-[1.35]">
                  <span
                    v-if="product.variants[0]?.discount_bps"
                    class="mr-1 text-[#819086] line-through"
                  >
                    {{ product.variants[0]?.price_yen.toLocaleString('ja-JP') }}円
                  </span>
                  <strong class="text-[#d94339]">{{ formatYen(productPrice(product)) }}</strong>
                </p>
              </div>
            </button>
            <div class="px-2.5 pb-2">
              <button
                class="mt-2 min-h-7 w-full rounded-[5px] border-0 bg-[#237f4b] px-1 text-[12px] font-bold text-white"
                type="button"
                :disabled="
                  !product.variants[0] ||
                  product.variants[0].stock_quantity < 1 ||
                  addCartItemMutation.isPending.value
                "
                @click="addToCart(product)"
              >
                カートに追加
              </button>
            </div>
          </article>
        </div>

        <p
          v-if="catalogueQuery.isPending.value"
          class="mt-10 text-center text-[12px] text-[#718075]"
        >
          商品を読み込んでいます。
        </p>
        <p
          v-else-if="catalogueQuery.isError.value"
          class="mt-10 text-center text-[12px] text-[#718075]"
        >
          商品を読み込めませんでした。
        </p>
        <p
          v-else-if="!categoryProducts.length"
          class="mt-10 text-center text-[12px] text-[#718075]"
        >
          このカテゴリに商品はありません
        </p>
      </section>

      <BuyerBottomNavigation active="category" />
    </section>
  </main>
</template>
