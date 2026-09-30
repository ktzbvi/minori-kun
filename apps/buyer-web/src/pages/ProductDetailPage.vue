<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { ChevronLeft, Leaf, Search, ShoppingCart } from 'lucide-vue-next'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useCart } from '@/lib/cart'
import { products } from '@/lib/catalog'

const route = useRoute()
const router = useRouter()
const { addItem, cartItemCount } = useCart()
const product = computed(
  () => products.find((item) => item.id === route.params.productId) ?? products[0]!,
)
const selectedVariantId = ref(product.value.variants[0]!.id)
const quantity = ref(1)
const selectedVariant = computed(
  () =>
    product.value.variants.find((variant) => variant.id === selectedVariantId.value) ??
    product.value.variants[0]!,
)
const canPurchase = computed(() => selectedVariant.value.stock >= quantity.value)
watch(product, (next) => {
  selectedVariantId.value = next.variants[0]!.id
  quantity.value = 1
})
function decreaseQuantity() {
  quantity.value = Math.max(1, quantity.value - 1)
}
function increaseQuantity() {
  quantity.value = Math.min(selectedVariant.value.stock, quantity.value + 1)
}
function addToCart() {
  if (!canPurchase.value) return

  addItem(product.value.id, quantity.value, selectedVariant.value.id)
  toast.success('\u30ab\u30fc\u30c8\u306b\u8ffd\u52a0\u3057\u307e\u3057\u305f')
}
function openSearch() {
  void router.push({ name: 'search' })
}
function openCart() {
  void router.push({ name: 'cart' })
}
function showCheckoutUnavailable() {
  toast.warning('\u8cfc\u5165\u624b\u7d9a\u304d\u306f\u6e96\u5099\u4e2d\u3067\u3059')
}
function formatYen(amount: number) {
  return `\u7a0e\u8fbc ${amount.toLocaleString('ja-JP')}\u5186`
}
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
          <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">&#x5546;&#x54C1;&#x8A73;&#x7D30;</h1>
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
              class="absolute top-0 right-0 grid size-4 place-items-center rounded-full bg-[#e25a3d] text-[9px] font-bold text-white"
              v-if="cartItemCount"
            >
              {{ cartItemCount }}
            </span>
          </button>
        </div>
      </header>
      <div class="aspect-[1.52] overflow-hidden bg-[#e5eee7]">
        <img :src="product.imageUrl" :alt="product.name" class="size-full object-cover" />
      </div>
      <main class="px-4 pt-3 pb-6">
        <h2 class="m-0 text-[22px] leading-[1.35] font-extrabold">{{ product.name }}</h2>
        <p class="mt-1 mb-0 text-[13px]">
          <span v-if="product.regularPrice" class="mr-1 text-[#819086] line-through">
            {{ product.regularPrice.toLocaleString('ja-JP') }}&#x5186;
          </span>
          <strong class="text-[17px] text-[#d94339]">{{ formatYen(selectedVariant.price) }}</strong>
        </p>
        <p class="mt-2 mb-0 text-[12px] leading-[1.65] text-[#63746a]">{{ product.description }}</p>
        <p class="mt-1 mb-6 flex items-center gap-1 text-[12px] font-bold text-[#237f4b]">
          <Leaf :size="14" />
          {{ product.producerName }}
        </p>
        <section class="border-t border-[#e1e8e2] pt-4">
          <h3 class="m-0 text-[14px] font-bold">
            &#x5546;&#x54C1;&#x30AA;&#x30D7;&#x30B7;&#x30E7;&#x30F3;
          </h3>
          <p class="mt-1 mb-2 text-[12px] text-[#617269]">
            &#x30BB;&#x30C3;&#x30C8;&#x30BF;&#x30A4;&#x30D7;
          </p>
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
              {{ variant.name }}
            </button>
          </div>
        </section>
        <section class="mt-4">
          <h3 class="m-0 text-[12px] font-bold text-[#617269]">&#x6570;&#x91CF;</h3>
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
            <span class="grid flex-1 place-items-center text-[13px] font-bold">{{ quantity }}</span>
            <button
              class="w-9 border-0 border-l border-[#dce5de] bg-white text-lg text-[#547064] disabled:text-[#c2cdc5]"
              type="button"
              :disabled="quantity === selectedVariant.stock"
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
            :disabled="!canPurchase"
            @click="addToCart"
          >
            &#x30AB;&#x30FC;&#x30C8;&#x306B;&#x8FFD;&#x52A0;
          </button>
          <button
            class="min-h-10 rounded-[5px] border border-[#237f4b] bg-white text-[14px] font-bold text-[#237f4b]"
            type="button"
            @click="showCheckoutUnavailable"
          >
            &#x4ECA;&#x3059;&#x3050;&#x8CFC;&#x5165;
          </button>
        </div>
      </main>
      <BuyerBottomNavigation />
    </section>
  </main>
</template>
