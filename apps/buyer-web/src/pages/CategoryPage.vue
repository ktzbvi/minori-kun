<script setup lang="ts">
import {
  Apple,
  ChevronLeft,
  Grid2X2,
  Package,
  Search,
  ShoppingCart,
  Sprout,
  Wheat,
} from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { categories, type Category } from '@/lib/catalog'
import { useCart } from '@/lib/cart'

const router = useRouter()
const { cartItemCount } = useCart()

const categoryCards: {
  id: Category
  description: string
  icon: typeof Grid2X2
}[] = [
  { id: 'all', description: '\u304a\u3059\u3059\u3081\u5546\u54c1', icon: Grid2X2 },
  { id: 'vegetables', description: '\u65b0\u9bae\u306a\u91ce\u83dc', icon: Sprout },
  { id: 'fruit', description: '\u65ec\u306e\u679c\u7269', icon: Apple },
  {
    id: 'sets',
    description: '\u30ae\u30d5\u30c8\u30fb\u8a70\u3081\u5408\u308f\u305b',
    icon: Package,
  },
  { id: 'grains', description: '\u767d\u7c73\u30fb\u7384\u7c73', icon: Wheat },
]

function categoryLabel(categoryId: Category) {
  return categories.find((category) => category.id === categoryId)?.label ?? ''
}

function openCategory(categoryId: Category) {
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
            <component :is="category.icon" :size="23" class="text-[#237f4b]" stroke-width="1.9" />
            <strong class="mt-1 block text-[13px]">{{ categoryLabel(category.id) }}</strong>
            <span class="mt-0.5 block text-[10px] text-[#75847a]">{{ category.description }}</span>
          </button>
        </div>
      </section>

      <BuyerBottomNavigation active="category" />
    </section>
  </main>
</template>
