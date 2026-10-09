<script setup lang="ts">
import { ref } from 'vue'
import { Search, ShoppingCart } from 'lucide-vue-next'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import BuyerBrand from '@/components/BuyerBrand.vue'
import { useBuyerHome } from '@/composables/useBuyerHome'
const {
  pageSize,
  selectedCategory,
  visibleCount,
  productList,
  router,
  catalogueQuery,
  cartQuery,
  addCartItemMutation,
  cartItemCount,
  categories,
  allProducts,
  visibleProducts,
  displayedProducts,
  hasMoreProducts,
  addToCart,
  openCart,
  openProduct,
  openSearch,
  loadMoreProducts,
  fillProductList,
  handleProductListScroll,
  productPrice,
  discountRate,
  formatYen,
} = useBuyerHome()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="relative flex h-dvh w-full flex-col bg-[#f8faf6] sm:max-w-[375px] sm:shadow-sm">
      <header
        class="flex h-[74px] shrink-0 items-center justify-between border-b border-[#e3e9e3] bg-white px-4"
      >
        <BuyerBrand size="header" />
        <div class="flex items-center gap-3">
          <button
            class="grid size-9 place-items-center rounded-full border-0 bg-transparent text-[#627469]"
            type="button"
            aria-label="Search"
            @click="openSearch"
          >
            <Search :size="21" />
          </button>
          <button
            class="relative grid size-9 place-items-center rounded-full border-0 bg-transparent text-[#627469]"
            type="button"
            aria-label="Cart"
            @click="openCart"
          >
            <ShoppingCart :size="21" />
            <span
              v-if="cartItemCount"
              class="absolute top-0 right-0 grid size-4 place-items-center rounded-full bg-[#e25a3d] text-[9px] font-bold text-white"
            >
              {{ cartItemCount }}
            </span>
          </button>
        </div>
      </header>
      <div class="shrink-0 border-b border-[#e6ece6] bg-white px-4 py-2.5">
        <div class="flex gap-2 overflow-x-auto pb-0.5">
          <button
            v-for="category in categories"
            :key="category.id"
            class="min-h-7 shrink-0 rounded-full border px-3 text-[12px] font-bold"
            :class="
              selectedCategory === category.id
                ? 'border-[#237f4b] bg-[#237f4b] text-white'
                : 'border-[#dbe4dc] bg-white text-[#3f5046]'
            "
            type="button"
            :aria-pressed="selectedCategory === category.id"
            @click="selectedCategory = category.id"
          >
            {{ category.label }}
          </button>
        </div>
      </div>
      <section
        ref="productList"
        class="min-h-0 flex-1 overflow-y-auto px-4 pt-3 pb-[82px]"
        @scroll="handleProductListScroll"
      >
        <div class="grid grid-cols-2 content-start gap-3">
          <article
            v-for="product in displayedProducts"
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
                  v-if="discountRate(product)"
                  class="absolute top-0 right-0 bg-[#df483f] px-2 py-1 text-[11px] font-extrabold text-white"
                >
                  {{ discountRate(product) }}%
                </span>
              </div>
              <div class="px-2.5 pt-2">
                <h2 class="m-0 truncate text-[13px] font-bold text-[#29392f]">
                  {{ product.name }}
                </h2>
                <p class="mt-1 mb-0 min-h-[18px] text-[11px] leading-[1.35]">
                  <span v-if="discountRate(product)" class="mr-1 text-[#819086] line-through">
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
      </section>
      <BuyerBottomNavigation active="home" />
    </section>
  </main>
</template>
