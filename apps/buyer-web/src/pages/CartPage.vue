<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeft, PackageOpen, Search, ShoppingBag, Trash2 } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useCart, type CartLine } from '@/lib/cart'
import { products, type CatalogueProduct, type ProductVariant } from '@/lib/catalog'

type CartEntry = {
  line: CartLine
  product: CatalogueProduct
  variant: ProductVariant
  unitPrice: number
  lineTotal: number
  regularPrice?: number
}

const router = useRouter()
const { cartItemCount, cartLines, removeItem, setItemQuantity } = useCart()

const cartEntries = computed<CartEntry[]>(() =>
  cartLines.value.flatMap((line) => {
    const product = products.find((item) => item.id === line.productId)
    const variant = line.variantId
      ? product?.variants.find((item) => item.id === line.variantId)
      : product?.variants[0]

    if (!product || !variant) return []

    const unitPrice = variant.price

    return [
      {
        line,
        product,
        variant,
        unitPrice,
        lineTotal: unitPrice * line.quantity,
        regularPrice: unitPrice === product.price ? product.regularPrice : undefined,
      },
    ]
  }),
)

const producerGroups = computed(() => {
  const grouped = new Map<string, CartEntry[]>()

  for (const entry of cartEntries.value) {
    const current = grouped.get(entry.product.producerName) ?? []
    current.push(entry)
    grouped.set(entry.product.producerName, current)
  }

  return Array.from(grouped, ([producerName, entries]) => ({ producerName, entries }))
})

const subtotal = computed(() =>
  cartEntries.value.reduce((total, entry) => total + entry.lineTotal, 0),
)

function decreaseQuantity(entry: CartEntry) {
  if (entry.line.quantity === 1) return

  setItemQuantity(entry.line.productId, entry.line.quantity - 1, entry.line.variantId)
}

function increaseQuantity(entry: CartEntry) {
  if (entry.line.quantity >= entry.variant.stock) {
    toast.warning('\u5728\u5eab\u6570\u3092\u78ba\u8a8d\u3057\u3066\u304f\u3060\u3055\u3044')
    return
  }

  setItemQuantity(entry.line.productId, entry.line.quantity + 1, entry.line.variantId)
}

function removeLine(entry: CartEntry) {
  removeItem(entry.line.productId, entry.line.variantId)
  toast.error('\u30ab\u30fc\u30c8\u304b\u3089\u524a\u9664\u3057\u307e\u3057\u305f')
}

function openSearch() {
  void router.push({ name: 'search' })
}

function goHome() {
  void router.push({ name: 'home' })
}

function proceedToOrderConfirmation() {
  void router.push({ name: 'order-confirmation' })
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
            @click="router.back()"
          >
            <ChevronLeft :size="22" stroke-width="2.5" />
          </button>
          <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">&#x30AB;&#x30FC;&#x30C8;</h1>
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
          <span class="relative grid size-9 place-items-center text-[#627469]" aria-label="Cart">
            <ShoppingBag :size="21" />
            <span
              v-if="cartItemCount"
              class="absolute top-0 right-0 grid size-4 place-items-center rounded-full bg-[#e25a3d] text-[9px] font-bold text-white"
            >
              {{ cartItemCount }}
            </span>
          </span>
        </div>
      </header>

      <template v-if="cartEntries.length">
        <section class="min-h-0 flex-1 overflow-y-auto px-3 pt-3 pb-[176px]">
          <section
            v-for="group in producerGroups"
            :key="group.producerName"
            class="mb-3"
            :aria-label="group.producerName"
          >
            <p class="mb-1 ml-1 text-[11px] font-bold text-[#237f4b]">{{ group.producerName }}</p>
            <article
              v-for="entry in group.entries"
              :key="`${entry.line.productId}-${entry.line.variantId ?? 'default'}`"
              class="mb-2 flex gap-2 rounded-[8px] border border-[#dce5dc] bg-white p-2"
            >
              <img
                :src="entry.product.imageUrl"
                :alt="entry.product.name"
                class="size-[62px] shrink-0 rounded-[5px] object-cover"
              />
              <div class="min-w-0 flex-1">
                <h2 class="m-0 truncate text-[12px] font-bold">{{ entry.product.name }}</h2>
                <p class="mt-0.5 mb-0 text-[10px] text-[#66776c]">{{ entry.variant.name }}</p>
                <p class="mt-1 mb-0 text-[12px]">
                  <span v-if="entry.regularPrice" class="mr-1 text-[#89968e] line-through">
                    {{ entry.regularPrice.toLocaleString('ja-JP') }}&#x5186;
                  </span>
                  <strong class="text-[#d94339]">{{ formatYen(entry.unitPrice) }}</strong>
                </p>
                <div class="mt-1.5 flex items-center gap-2">
                  <div class="flex h-7 overflow-hidden rounded-[4px] border border-[#dce5de]">
                    <button
                      class="grid w-7 place-items-center border-0 border-r border-[#dce5de] bg-white text-[#547064] disabled:text-[#c2cdc5]"
                      type="button"
                      :disabled="entry.line.quantity === 1"
                      aria-label="Decrease quantity"
                      @click="decreaseQuantity(entry)"
                    >
                      -
                    </button>
                    <span class="grid min-w-7 place-items-center text-[12px] font-bold">
                      {{ entry.line.quantity }}
                    </span>
                    <button
                      class="grid w-7 place-items-center border-0 border-l border-[#dce5de] bg-white text-[#547064] disabled:text-[#c2cdc5]"
                      type="button"
                      :disabled="entry.line.quantity === entry.variant.stock"
                      aria-label="Increase quantity"
                      @click="increaseQuantity(entry)"
                    >
                      +
                    </button>
                  </div>
                  <strong class="text-[12px] text-[#33443a]">{{
                    formatYen(entry.lineTotal)
                  }}</strong>
                </div>
              </div>
              <button
                class="grid size-8 shrink-0 place-items-center rounded-full border-0 bg-transparent text-[#708076]"
                type="button"
                :aria-label="`${entry.product.name}を削除`"
                @click="removeLine(entry)"
              >
                <Trash2 :size="17" />
              </button>
            </article>
          </section>
        </section>

        <aside
          class="absolute right-0 bottom-[64px] left-0 border-t border-[#dce5dc] bg-white px-3 py-2.5"
        >
          <div class="rounded-[6px] border border-[#dce5dc] px-3 py-2 text-[12px]">
            <div class="flex justify-between">
              <span>&#x5546;&#x54C1;&#x5408;&#x8A08;</span>
              <strong>{{ formatYen(subtotal) }}</strong>
            </div>
            <div class="mt-2 flex justify-between border-t border-[#e6ece6] pt-2 text-[14px]">
              <strong>&#x5408;&#x8A08;</strong>
              <strong class="text-[#d94339]">{{ formatYen(subtotal) }}</strong>
            </div>
          </div>
          <button
            class="mt-2 min-h-10 w-full rounded-[5px] border-0 bg-[#237f4b] text-[13px] font-bold text-white"
            type="button"
            @click="proceedToOrderConfirmation"
          >
            &#x6CE8;&#x6587;&#x5185;&#x5BB9;&#x3092;&#x78BA;&#x8A8D;&#x3059;&#x308B;
          </button>
        </aside>
      </template>

      <section v-else class="grid min-h-0 flex-1 place-items-center px-6 pb-[82px] text-center">
        <div>
          <span
            class="mx-auto mb-5 grid size-24 place-items-center rounded-[12px] border border-[#dce5dc] bg-white text-[#a9b8ad]"
          >
            <PackageOpen :size="52" stroke-width="1.25" />
          </span>
          <h2 class="m-0 text-[15px] font-bold text-[#37483d]">
            &#x30AB;&#x30FC;&#x30C8;&#x306B;&#x5546;&#x54C1;&#x304C;&#x3042;&#x308A;&#x307E;&#x305B;&#x3093;
          </h2>
          <p class="mt-2 mb-4 text-[11px] text-[#718075]">
            &#x5546;&#x54C1;&#x4E00;&#x89A7;&#x304B;&#x3089;&#x5546;&#x54C1;&#x3092;&#x9078;&#x3093;&#x3067;&#x304F;&#x3060;&#x3055;&#x3044;
          </p>
          <button
            class="min-h-10 w-[205px] rounded-[5px] border-0 bg-[#237f4b] text-[13px] font-bold text-white"
            type="button"
            @click="goHome"
          >
            &#x5546;&#x54C1;&#x4E00;&#x89A7;&#x3078;&#x623B;&#x308B;
          </button>
        </div>
      </section>

      <BuyerBottomNavigation active="cart" />
    </section>
  </main>
</template>
