<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeft, Search, ShoppingCart } from 'lucide-vue-next'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useCart } from '@/lib/cart'
import { categories, products, type Category } from '@/lib/catalog'

const route = useRoute()
const router = useRouter()
const { addItem, cartItemCount } = useCart()

const selectedCategoryId = computed<Category>(() => {
  const categoryId = route.params.categoryId

  return categories.some((category) => category.id === categoryId)
    ? (categoryId as Category)
    : 'all'
})
const selectedCategory = computed(
  () => categories.find((category) => category.id === selectedCategoryId.value) ?? categories[0]!,
)
const categoryProducts = computed(() =>
  selectedCategoryId.value === 'all'
    ? products
    : products.filter((product) => product.category === selectedCategoryId.value),
)

function goBack() {
  void router.push({ name: 'categories' })
}

function openSearch() {
  void router.push({ name: 'search' })
}

function openProduct(productId: string) {
  void router.push({ name: 'product-detail', params: { productId } })
}

function addToCart(productId: string) {
  addItem(productId)
  toast.success('\u30ab\u30fc\u30c8\u306b\u8ffd\u52a0\u3057\u307e\u3057\u305f')
}

function openCart() {
  void router.push({ name: 'cart' })
}

function formatYen(amount: number) {
  return `\u7a0e\u8fbc ${amount.toLocaleString('ja-JP')}\u5186`
}
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
          <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">
            &#x30AB;&#x30C6;&#x30B4;&#x30EA;&#x5546;&#x54C1;&#x4E00;&#x89A7;
          </h1>
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

      <section class="min-h-0 flex-1 overflow-y-auto px-4 pt-4 pb-[82px]">
        <h2 class="m-0 text-[16px] font-bold text-[#26362c]">{{ selectedCategory.label }}</h2>
        <p class="mt-1 mb-3 text-[11px] text-[#718075]">
          {{ categoryProducts.length }}&#x4EF6;&#x306E;&#x5546;&#x54C1;
        </p>
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
                <img :src="product.imageUrl" :alt="product.name" class="size-full object-cover" />
                <span
                  v-if="product.discountRate"
                  class="absolute top-0 right-0 bg-[#df483f] px-2 py-1 text-[11px] font-extrabold text-white"
                >
                  {{ product.discountRate }}%
                </span>
              </div>
              <div class="px-2.5 pt-2">
                <h3 class="m-0 truncate text-[13px] font-bold text-[#29392f]">
                  {{ product.name }}
                </h3>
                <p class="mt-1 mb-0 min-h-[18px] text-[11px] leading-[1.35]">
                  <span v-if="product.regularPrice" class="mr-1 text-[#819086] line-through">
                    {{ product.regularPrice.toLocaleString('ja-JP') }}&#x5186;
                  </span>
                  <strong class="text-[#d94339]">{{ formatYen(product.price) }}</strong>
                </p>
              </div>
            </button>
            <div class="px-2.5 pb-2">
              <button
                class="mt-2 min-h-7 w-full rounded-[5px] border-0 bg-[#237f4b] px-1 text-[12px] font-bold text-white"
                type="button"
                @click="addToCart(product.id)"
              >
                &#x30AB;&#x30FC;&#x30C8;&#x306B;&#x8FFD;&#x52A0;
              </button>
            </div>
          </article>
        </div>

        <p v-if="!categoryProducts.length" class="mt-10 text-center text-[12px] text-[#718075]">
          &#x3053;&#x306E;&#x30AB;&#x30C6;&#x30B4;&#x30EA;&#x306B;&#x5546;&#x54C1;&#x306F;&#x3042;&#x308A;&#x307E;&#x305B;&#x3093;
        </p>
      </section>

      <BuyerBottomNavigation active="category" />
    </section>
  </main>
</template>
