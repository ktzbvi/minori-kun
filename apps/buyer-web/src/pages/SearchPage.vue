<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { ChevronLeft, Search, ShoppingCart } from 'lucide-vue-next'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { products } from '@/lib/catalog'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useCart } from '@/lib/cart'

const route = useRoute()
const router = useRouter()
const { addItem, cartItemCount } = useCart()
const searchInput = ref(readQuery())
const query = ref(searchInput.value.trim())

const results = computed(() => {
  const normalizedQuery = query.value.toLocaleLowerCase()

  if (!normalizedQuery) return []

  return products.filter((product) =>
    [product.name, ...(product.searchTerms ?? [])].some((term) =>
      term.toLocaleLowerCase().startsWith(normalizedQuery),
    ),
  )
})

watch(
  () => route.query.q,
  () => {
    searchInput.value = readQuery()
    query.value = searchInput.value.trim()
  },
)

function readQuery() {
  return typeof route.query.q === 'string' ? route.query.q : ''
}

function searchProducts() {
  const normalizedQuery = searchInput.value.trim()

  query.value = normalizedQuery
  void router.replace({
    name: 'search',
    query: normalizedQuery ? { q: normalizedQuery } : {},
  })
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
          <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">&#x691C;&#x7D22;&#x7D50;&#x679C;</h1>
        </div>
        <div class="flex gap-2">
          <button
            class="grid size-9 place-items-center rounded-full border-0 bg-transparent text-[#627469]"
            type="button"
            aria-label="Search"
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
            :placeholder="'\u30ad\u30fc\u30ef\u30fc\u30c9\u3092\u5165\u529b'"
            @blur="searchProducts"
          />
        </label>
      </form>

      <section class="flex-1 overflow-y-auto px-3 pt-2 pb-[82px]">
        <p class="m-0 text-[11px] text-[#718075]">
          <template v-if="query">
            「{{ query }}」&#x306E;&#x691C;&#x7D22;&#x7D50;&#x679C; {{ results.length }}&#x4EF6;
          </template>
          <template v-else>
            &#x30AD;&#x30FC;&#x30EF;&#x30FC;&#x30C9;&#x3092;&#x5165;&#x529B;&#x3057;&#x3066;&#x304F;&#x3060;&#x3055;&#x3044;
          </template>
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
                <img :src="product.imageUrl" :alt="product.name" class="size-full object-cover" />
                <span
                  v-if="product.discountRate"
                  class="absolute top-0 right-0 bg-[#df483f] px-2 py-1 text-[11px] font-extrabold text-white"
                >
                  {{ product.discountRate }}%
                </span>
              </div>
              <div class="px-2.5 pt-2">
                <h2 class="m-0 truncate text-[13px] font-bold text-[#29392f]">
                  {{ product.name }}
                </h2>
                <p class="mt-1 mb-0 min-h-[18px] text-[11px] leading-[1.35]">
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
            <h2 class="m-0 text-[15px] font-bold text-[#37483d]">
              &#x8A72;&#x5F53;&#x3059;&#x308B;&#x5546;&#x54C1;&#x304C;&#x3042;&#x308A;&#x307E;&#x305B;&#x3093;
            </h2>
            <p class="mt-1 mb-0 text-[11px]">
              &#x30AD;&#x30FC;&#x30EF;&#x30FC;&#x30C9;&#x3092;&#x5909;&#x66F4;&#x3057;&#x3066;&#x518D;&#x691C;&#x7D22;&#x3057;&#x3066;&#x304F;&#x3060;&#x3055;&#x3044;
            </p>
          </div>
        </div>
      </section>

      <BuyerBottomNavigation />
    </section>
  </main>
</template>
