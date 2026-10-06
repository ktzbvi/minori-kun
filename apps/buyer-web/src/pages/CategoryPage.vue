<script setup lang="ts">
import { ChevronLeft, Grid2X2, Search, ShoppingCart } from 'lucide-vue-next'
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useBuyerCartQuery } from '@/services/cart/cart.query'
import { useBuyerCatalogueQuery } from '@/services/catalog/catalog.query'

const router = useRouter()
const catalogueQuery = useBuyerCatalogueQuery()
const cartQuery = useBuyerCartQuery()
const cartItemCount = computed(
  () => cartQuery.data.value?.items.reduce((total, item) => total + item.quantity, 0) ?? 0,
)
const categoryCards = computed(() => [
  { id: 'all', label: 'すべて', count: catalogueQuery.data.value?.length ?? 0 },
  ...Array.from(
    new Set((catalogueQuery.data.value ?? []).map((product) => product.category).filter(Boolean)),
  ).map((category) => ({
    id: category!,
    label: category!,
    count:
      catalogueQuery.data.value?.filter((product) => product.category === category).length ?? 0,
  })),
])

function openCategory(categoryId: string) {
  void router.push({ name: 'category-products', params: { categoryId } })
}

function openSearch() {
  void router.push({ name: 'search' })
}
function openCart() {
  void router.push({ name: 'cart' })
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
            aria-label="Home"
            @click="router.push({ name: 'home' })"
          >
            <ChevronLeft :size="22" stroke-width="2.5" />
          </button>
          <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">&#x30AB;&#x30C6;&#x30B4;&#x30EA;</h1>
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
        <h2 class="m-0 text-[13px] font-bold text-[#237f4b]">
          &#x30AB;&#x30C6;&#x30B4;&#x30EA;&#x30FC;&#x304B;&#x3089;&#x63A2;&#x3059;
        </h2>
        <p class="mt-1 mb-3 text-[11px] text-[#718075]">
          &#x76EE;&#x7684;&#x306E;&#x5546;&#x54C1;&#x3092;&#x63A2;&#x3057;&#x3066;&#x304F;&#x3060;&#x3055;&#x3044;
        </p>
        <div class="grid grid-cols-2 gap-3">
          <button
            v-for="category in categoryCards"
            :key="category.id"
            class="min-h-[72px] rounded-[8px] border border-[#dce5dc] bg-white p-3 text-left text-[#26362c]"
            type="button"
            @click="openCategory(category.id)"
          >
            <Grid2X2 :size="23" class="text-[#237f4b]" stroke-width="1.9" />
            <strong class="mt-1 block text-[13px]">{{ category.label }}</strong>
            <span class="mt-0.5 block text-[10px] text-[#75847a]">
              {{ category.count }}件の商品
            </span>
          </button>
        </div>
        <p
          v-if="catalogueQuery.isPending.value"
          class="mt-6 text-center text-[12px] text-[#718075]"
        >
          商品を読み込んでいます。
        </p>
        <p
          v-else-if="catalogueQuery.isError.value"
          class="mt-6 text-center text-[12px] text-[#718075]"
        >
          商品を読み込めませんでした。
        </p>
      </section>

      <BuyerBottomNavigation active="category" />
    </section>
  </main>
</template>
