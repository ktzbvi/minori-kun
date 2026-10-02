<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { Leaf, Search, ShoppingCart } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useAddBuyerCartItemMutation } from '@/services/cart/cart.mutation'
import { useBuyerCartQuery } from '@/services/cart/cart.query'
import {
  useBuyerCatalogueQuery,
  type BuyerCatalogueProduct,
} from '@/services/catalog/catalog.query'

const pageSize = 4
const selectedCategory = ref('all')
const visibleCount = ref(pageSize)
const productList = ref<HTMLElement>()
const router = useRouter()
const catalogueQuery = useBuyerCatalogueQuery()
const cartQuery = useBuyerCartQuery()
const addCartItemMutation = useAddBuyerCartItemMutation()
const cartItemCount = computed(
  () => cartQuery.data.value?.items.reduce((total, item) => total + item.quantity, 0) ?? 0,
)
const categories = computed(() => [
  { id: 'all', label: 'すべて' },
  ...Array.from(
    new Set((catalogueQuery.data.value ?? []).map((product) => product.category).filter(Boolean)),
  ).map((category) => ({ id: category!, label: category! })),
])
const allProducts = computed(() => catalogueQuery.data.value ?? [])
const visibleProducts = computed(() =>
  selectedCategory.value === 'all'
    ? allProducts.value
    : allProducts.value.filter((product) => product.category === selectedCategory.value),
)
const displayedProducts = computed(() => visibleProducts.value.slice(0, visibleCount.value))
const hasMoreProducts = computed(
  () => displayedProducts.value.length < visibleProducts.value.length,
)

watch(selectedCategory, () => {
  visibleCount.value = pageSize
  void fillProductList()
})
function addToCart(product: BuyerCatalogueProduct) {
  const variant = product.variants[0]
  if (!variant || variant.stock_quantity < 1) return

  addCartItemMutation.mutate(
    { variantId: variant.id, quantity: 1 },
    {
      onSuccess: () => toast.success('カートに追加しました'),
      onError: () => toast.error('カートに追加できませんでした。ログイン状態を確認してください。'),
    },
  )
}
function openCart() {
  void router.push({ name: 'cart' })
}
function openProduct(productId: string) {
  void router.push({ name: 'product-detail', params: { productId } })
}
function openSearch() {
  void router.push({ name: 'search' })
}
function loadMoreProducts() {
  if (!hasMoreProducts.value) return

  visibleCount.value += pageSize
}
async function fillProductList() {
  await nextTick()

  while (
    hasMoreProducts.value &&
    productList.value &&
    productList.value.scrollHeight <= productList.value.clientHeight
  ) {
    loadMoreProducts()
    await nextTick()
  }
}
function handleProductListScroll(event: Event) {
  const element = event.currentTarget as HTMLElement
  const isNearBottom = element.scrollTop + element.clientHeight >= element.scrollHeight - 80

  if (isNearBottom) loadMoreProducts()
}

onMounted(() => {
  void fillProductList()
})

function productPrice(product: BuyerCatalogueProduct) {
  const variant = product.variants[0]
  if (!variant) return 0

  return Math.round((variant.price_yen * (10_000 - variant.discount_bps)) / 10_000)
}

function discountRate(product: BuyerCatalogueProduct) {
  return (product.variants[0]?.discount_bps ?? 0) / 100
}

function formatYen(amount: number) {
  return `\u7a0e\u8fbc ${amount.toLocaleString('ja-JP')}\u5186`
}
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="relative flex h-dvh w-full flex-col bg-[#f8faf6] sm:max-w-[375px] sm:shadow-sm">
      <header
        class="flex h-[74px] shrink-0 items-center justify-between border-b border-[#e3e9e3] bg-white px-4"
      >
        <div class="flex items-center gap-2">
          <span
            class="grid size-7 place-items-center rounded-full bg-[#e4f3e9] text-[#237d4a]"
            aria-hidden="true"
          >
            <Leaf :size="17" stroke-width="2.5" />
          </span>
          <strong class="text-[17px] tracking-[0.05em] text-[#237d4a]">
            &#x307F;&#x306E;&#x308A;&#x304F;&#x3093;
          </strong>
        </div>
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
                    {{ product.variants[0]?.price_yen.toLocaleString('ja-JP') }}&#x5186;
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
                &#x30AB;&#x30FC;&#x30C8;&#x306B;&#x8FFD;&#x52A0;
              </button>
            </div>
          </article>
        </div>
      </section>
      <BuyerBottomNavigation active="home" />
    </section>
  </main>
</template>
