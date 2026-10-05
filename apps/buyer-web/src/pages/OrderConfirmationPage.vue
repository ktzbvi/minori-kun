<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import { ChevronLeft, Pencil } from 'lucide-vue-next'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { checkoutDeliveryAddress, initializeCheckoutDeliveryAddress } from '@/lib/checkout'
import { getBuyerAccountProfile } from '@/services/account/account.api'
import { useBuyerCartQuery, type BuyerCartItem } from '@/services/cart/cart.query'

const router = useRouter()
const route = useRoute()
const cartQuery = useBuyerCartQuery()

onMounted(async () => {
  try {
    const profile = await getBuyerAccountProfile()

    initializeCheckoutDeliveryAddress({
      name: profile.name,
      phone: profile.phone,
      postalCode: profile.postal_code,
      prefecture: profile.prefecture,
      city: profile.city,
      addressLine: [profile.address_line1, profile.address_line2].filter(Boolean).join(' '),
    })
  } catch {
    await router.replace({ name: 'login', query: { redirect: route.fullPath } })
  }
})

const producerGroups = computed(() => {
  const grouped = new Map<string, BuyerCartItem[]>()

  for (const item of cartQuery.data.value?.items ?? []) {
    const items = grouped.get(item.producer_id) ?? []
    items.push(item)
    grouped.set(item.producer_id, items)
  }

  return Array.from(grouped, ([producerId, items]) => ({
    producerId,
    shopName: items[0]?.shop_name ?? '\u30b7\u30e7\u30c3\u30d7',
    items,
  }))
})

const selectedProducerGroup = computed(() => {
  const selectedProducerId =
    typeof route.query.producer === 'string' ? route.query.producer : undefined

  return (
    producerGroups.value.find((group) => group.producerId === selectedProducerId) ??
    producerGroups.value[0]
  )
})

watch(
  () => ({
    data: cartQuery.data.value,
    isFetching: cartQuery.isFetching.value,
    isSuccess: cartQuery.isSuccess.value,
  }),
  ({ data, isFetching, isSuccess }) => {
    if (!isSuccess || isFetching || !data || selectedProducerGroup.value) return

    void router.replace({ name: data.items.length ? 'cart' : 'order-history' })
  },
  { immediate: true },
)

const total = computed(() =>
  selectedProducerGroup.value ? shopTotal(selectedProducerGroup.value.items) : 0,
)
const canProceedToPayment = computed(() => Boolean(selectedProducerGroup.value && total.value > 0))

function changeAddress() {
  void router.push({
    name: 'checkout-address',
    query:
      typeof route.query.producer === 'string' ? { producer: route.query.producer } : undefined,
  })
}

function proceedToPayment() {
  if (!canProceedToPayment.value) {
    toast.error(
      '\u30ab\u30fc\u30c8\u306e\u5546\u54c1\u3092\u78ba\u8a8d\u3057\u3066\u304f\u3060\u3055\u3044\u3002',
    )
    void router.replace({ name: 'cart' })
    return
  }

  void router.push({
    name: 'buyer-payment',
    query: { producer: selectedProducerGroup.value?.producerId },
  })
}

function formatYen(amount: number) {
  return `${amount.toLocaleString('ja-JP')}\u5186`
}
function unitPrice(item: BuyerCartItem) {
  return Math.round((item.unit_price_yen * (10_000 - item.discount_bps)) / 10_000)
}

function lineTotal(item: BuyerCartItem) {
  return unitPrice(item) * item.quantity
}

function regularLineTotal(item: BuyerCartItem) {
  return item.unit_price_yen * item.quantity
}

function deliveryFee(item: BuyerCartItem) {
  if (checkoutDeliveryAddress.prefecture === '\u5317\u6d77\u9053') {
    return item.delivery_fee_hokkaido_yen
  }

  if (checkoutDeliveryAddress.prefecture === '\u6c96\u7e04\u770c') {
    return item.delivery_fee_okinawa_yen
  }

  return item.delivery_fee_honshu_yen
}

function deliveryFeeItem(items: BuyerCartItem[]) {
  return items.reduce<BuyerCartItem | undefined>(
    (selected, item) =>
      !selected || discountedFee(item) > discountedFee(selected) ? item : selected,
    undefined,
  )
}

function discountedDeliveryFee(items: BuyerCartItem[]) {
  const item = deliveryFeeItem(items)
  return item ? discountedFee(item) : 0
}

function shopTotal(items: BuyerCartItem[]) {
  return items.reduce((total, item) => total + lineTotal(item), 0) + discountedDeliveryFee(items)
}

function lineAmount(item: BuyerCartItem, items: BuyerCartItem[]) {
  return (
    lineTotal(item) + (deliveryFeeItem(items)?.id === item.id ? discountedDeliveryFee(items) : 0)
  )
}

function regularLineAmount(item: BuyerCartItem, items: BuyerCartItem[]) {
  return regularLineTotal(item) + (deliveryFeeItem(items)?.id === item.id ? deliveryFee(item) : 0)
}

function discountedFee(item: BuyerCartItem) {
  return Math.round((deliveryFee(item) * (10_000 - item.discount_bps)) / 10_000)
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
              <p class="mt-1 mb-0 text-[13px] font-bold">{{ checkoutDeliveryAddress.name }}</p>
              <p class="mt-0.5 mb-0 text-[10px] leading-[1.55] text-[#647468]">
                &#x3012;{{ checkoutDeliveryAddress.postalCode }}
                <br />
                {{ checkoutDeliveryAddress.prefecture }}{{ checkoutDeliveryAddress.city
                }}{{ checkoutDeliveryAddress.addressLine }}
                <br />
                {{ checkoutDeliveryAddress.phone }}
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
          <section v-if="selectedProducerGroup" class="mt-2">
            <p class="m-0 text-[10px] font-bold text-[#237f4b]">
              {{ selectedProducerGroup.shopName }}
            </p>
            <div
              v-for="item in selectedProducerGroup.items"
              :key="item.id"
              class="mt-1 flex items-end justify-between gap-3 text-[11px]"
            >
              <p class="m-0 min-w-0 text-[#526259]">
                {{ item.product_name }} {{ item.variant_label }} &#x00D7; {{ item.quantity }}
              </p>
              <p class="m-0 shrink-0 whitespace-nowrap text-right">
                <span v-if="item.discount_bps" class="mr-1 text-[#8b978f] line-through">
                  {{ formatYen(regularLineAmount(item, selectedProducerGroup.items)) }}
                </span>
                <strong class="text-[#d94339]">
                  {{ formatYen(lineAmount(item, selectedProducerGroup.items)) }}
                </strong>
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
          class="mt-3 min-h-10 w-full rounded-[5px] border-0 bg-[#237f4b] text-[13px] font-bold text-white disabled:bg-[#9cbca7]"
          type="button"
          :disabled="!canProceedToPayment"
          @click="proceedToPayment"
        >
          &#x6C7A;&#x6E08;&#x753B;&#x9762;&#x3078;&#x9032;&#x3080;
        </button>
      </section>

      <BuyerBottomNavigation active="cart" />
    </section>
  </main>
</template>
