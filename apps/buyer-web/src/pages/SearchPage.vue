<script setup lang="ts">
import { ChevronLeft, Search, ShoppingCart } from 'lucide-vue-next'
import BuyerCartButton from '@/components/layout/BuyerCartButton.vue'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useBuyerSearch } from '@/composables/useBuyerSearch'
const {
  router,
  addCartItemMutation,
  searchInput,
  query,
  results,
  searchProducts,
  openProduct,
  addToCart,
  formatYen,
  productPrice,
} = useBuyerSearch()
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section
      class="relative flex min-h-screen w-full flex-col bg-[#f8faf6] sm:min-h-[728px] sm:max-w-[375px] sm:shadow-sm"
    >
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
          <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">検索結果</h1>
        </div>
        <div class="flex gap-2">
          <button
            class="grid size-9 place-items-center rounded-full border-0 bg-transparent text-[#627469]"
            type="button"
            aria-label="Search"
          >
            <Search :size="21" />
          </button>
          <BuyerCartButton />
        </div>
      </header>

      <form
        class="shrink-0 border-b border-[#e6ece6] bg-white px-4 py-2"
        @submit.prevent="searchProducts"
      >
        <label
          class="flex h-8 items-center gap-2 rounded-[7px] border border-[#dbe4dc] bg-white px-2.5 text-[#68786e]"
        >
          <Search :size="16" />
          <input
            v-model="searchInput"
            class="min-w-0 flex-1 border-0 bg-transparent text-[13px] text-[#26362c] outline-none placeholder:text-[#9aa99f]"
            type="search"
            autocomplete="off"
            :placeholder="'キーワードを入力'"
            @blur="searchProducts"
          />
        </label>
      </form>

      <section class="flex-1 overflow-y-auto px-3 pt-2 pb-[82px]">
        <p class="m-0 text-[11px] text-[#718075]">
          <template v-if="query">「{{ query }}」の検索結果 {{ results.length }}件</template>
          <template v-else>キーワードを入力してください</template>
        </p>

        <div v-if="results.length" class="mt-2 grid grid-cols-2 gap-3">
          <article
            v-for="product in results"
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
                <h2 class="m-0 truncate text-[13px] font-bold text-[#29392f]">
                  {{ product.name }}
                </h2>
                <p class="mt-1 mb-0 min-h-[18px] text-[11px] leading-[1.35]">
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

        <div
          v-else-if="query"
          class="grid min-h-[430px] place-items-center text-center text-[#68786e]"
        >
          <div>
            <span
              class="mx-auto mb-4 grid size-24 place-items-center rounded-[12px] border border-[#dce5dc] bg-white text-[#b4c0b6]"
            >
              <ShoppingCart :size="54" stroke-width="1.25" />
            </span>
            <h2 class="m-0 text-[15px] font-bold text-[#37483d]">該当する商品がありません</h2>
            <p class="mt-1 mb-0 text-[11px]">キーワードを変更して再検索してください</p>
          </div>
        </div>
      </section>

      <BuyerBottomNavigation />
    </section>
  </main>
</template>
