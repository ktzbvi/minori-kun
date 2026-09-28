<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeft, Pencil } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useCart, type CartLine } from '@/lib/cart'
import { products, type CatalogueProduct, type ProductVariant } from '@/lib/catalog'

type OrderEntry = {
  line: CartLine
  product: CatalogueProduct
  variant: ProductVariant
  lineTotal: number
  regularPrice?: number
}

const router = useRouter()
const { cartLines } = useCart()

const orderEntries = computed<OrderEntry[]>(() =>
  cartLines.value.flatMap((line) => {
    const product = products.find((item) => item.id === line.productId)
    const variant = line.variantId
      ? product?.variants.find((item) => item.id === line.variantId)
      : product?.variants[0]

    if (!product || !variant) return []

    return [
      {
        line,
        product,
        variant,
        lineTotal: variant.price * line.quantity,
        regularPrice: variant.price === product.price ? product.regularPrice : undefined,
      },
    ]
  }),
)

const producerGroups = computed(() => {
  const grouped = new Map<string, OrderEntry[]>()

  for (const entry of orderEntries.value) {
    const entries = grouped.get(entry.product.producerName) ?? []
    entries.push(entry)
    grouped.set(entry.product.producerName, entries)
  }

  return Array.from(grouped, ([producerName, entries]) => ({ producerName, entries }))
})

const total = computed(() =>
  orderEntries.value.reduce((amount, entry) => amount + entry.lineTotal, 0),
)

function changeAddress() {
  toast.warning(
    '\u304a\u5c4a\u3051\u5148\u306e\u5909\u66f4\u306f\u73fe\u5728\u958b\u767a\u4e2d\u3067\u3059',
  )
}

function proceedToPayment() {
  toast.warning('\u6c7a\u6e08\u753b\u9762\u306f\u73fe\u5728\u958b\u767a\u4e2d\u3067\u3059')
}

function formatYen(amount: number) {
  return `${amount.toLocaleString('ja-JP')}\u5186`
}
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section class="relative flex h-dvh w-full flex-col bg-[#f8faf6] sm:max-w-[375px] sm:shadow-sm">
      <header class="flex h-[65px] shrink-0 items-center border-b border-[#e3e9e3] bg-white px-4">
        <button
          class="grid size-9 place-items-center rounded-full border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="Back"
          @click="router.back()"
        >
          <ChevronLeft :size="22" stroke-width="2.5" />
        </button>
        <h1 class="m-0 ml-1 text-[16px] font-bold text-[#237d4a]">
          &#x6CE8;&#x6587;&#x5185;&#x5BB9;&#x306E;&#x78BA;&#x8A8D;
        </h1>
      </header>

      <section class="min-h-0 flex-1 overflow-y-auto px-3 pt-3 pb-[82px]">
        <section
          class="rounded-[7px] border border-[#dce5dc] bg-white p-3"
          aria-label="Delivery address"
        >
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="m-0 text-[11px] font-bold">&#x304A;&#x5C4A;&#x3051;&#x5148;</p>
              <p class="mt-1 mb-0 text-[13px] font-bold">&#x7530;&#x4E2D; &#x592A;&#x90CE;</p>
              <p class="mt-0.5 mb-0 text-[10px] leading-[1.55] text-[#647468]">
                &#x3012;150-0002<br />
                &#x6771;&#x4EAC;&#x90FD;&#x6E0B;&#x8C37;&#x533A;&#x6E0B;&#x8C37;2-1-3<br />
                090-1234-5678
              </p>
            </div>
            <button
              class="flex min-h-8 items-center gap-1 rounded-[4px] border-0 bg-transparent px-1 text-[11px] font-bold text-[#237f4b]"
              type="button"
              @click="changeAddress"
            >
              <Pencil :size="14" />
              &#x5909;&#x66F4;
            </button>
          </div>
        </section>

        <section
          class="mt-3 rounded-[7px] border border-[#dce5dc] bg-white p-3"
          aria-label="Order summary"
        >
          <h2 class="m-0 text-[12px] font-bold">&#x3054;&#x6CE8;&#x6587;&#x5185;&#x5BB9;</h2>
          <section v-for="group in producerGroups" :key="group.producerName" class="mt-2">
            <p class="m-0 text-[10px] font-bold text-[#237f4b]">{{ group.producerName }}</p>
            <div
              v-for="entry in group.entries"
              :key="`${entry.line.productId}-${entry.line.variantId ?? 'default'}`"
              class="mt-1 flex items-end justify-between gap-3 text-[11px]"
            >
              <p class="m-0 min-w-0 text-[#526259]">
                {{ entry.product.name }} {{ entry.variant.name }} &#x00D7; {{ entry.line.quantity }}
              </p>
              <p class="m-0 shrink-0 whitespace-nowrap text-right">
                <span v-if="entry.regularPrice" class="mr-1 text-[#8b978f] line-through">
                  {{ formatYen(entry.regularPrice * entry.line.quantity) }}
                </span>
                <strong class="text-[#d94339]">{{ formatYen(entry.lineTotal) }}</strong>
              </p>
            </div>
          </section>
          <div class="mt-3 border-t border-[#e6ece6] pt-2">
            <div class="flex justify-between text-[11px]">
              <span>&#x5546;&#x54C1;&#x5408;&#x8A08;</span>
              <strong>{{ formatYen(total) }}</strong>
            </div>
            <div class="mt-2 flex justify-between text-[14px] font-bold">
              <span>&#x304A;&#x652F;&#x6255;&#x3044;&#x5408;&#x8A08;</span>
              <strong class="text-[#d94339]">{{ formatYen(total) }}</strong>
            </div>
          </div>
        </section>

        <button
          class="mt-3 min-h-10 w-full rounded-[5px] border-0 bg-[#237f4b] text-[13px] font-bold text-white"
          type="button"
          @click="proceedToPayment"
        >
          &#x6C7A;&#x6E08;&#x753B;&#x9762;&#x3078;&#x9032;&#x3080;
        </button>
      </section>

      <BuyerBottomNavigation active="cart" />
    </section>
  </main>
</template>
