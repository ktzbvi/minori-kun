<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { ChevronLeft, PackageOpen, Search, ShoppingCart, Store, Trash2 } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import {
  useRemoveBuyerCartItemMutation,
  useUpdateBuyerCartItemMutation,
} from '@/services/cart/cart.mutation'
import { useBuyerCartQuery, type BuyerCartItem } from '@/services/cart/cart.query'
import { getBuyerAccountProfile } from '@/services/account/account.api'

const router = useRouter()
const cartQuery = useBuyerCartQuery()
const updateCartItemMutation = useUpdateBuyerCartItemMutation()
const removeCartItemMutation = useRemoveBuyerCartItemMutation()
const cartItems = computed(() => cartQuery.data.value?.items ?? [])
const deliveryPrefecture = ref('')

onMounted(async () => {
  try {
    deliveryPrefecture.value = (await getBuyerAccountProfile()).prefecture
  } catch {
    // The protected Cart route redirects to login before this can affect checkout.
  }
})

const producerGroups = computed(() => {
  const grouped = new Map<string, BuyerCartItem[]>()

  for (const item of cartItems.value) {
    const current = grouped.get(item.producer_id) ?? []
    current.push(item)
    grouped.set(item.producer_id, current)
  }

  return Array.from(grouped, ([producerId, items]) => ({
    producerId,
    shopName: items[0]?.shop_name ?? '\u30b7\u30e7\u30c3\u30d7',
    items,
    itemCount: items.reduce((total, item) => total + item.quantity, 0),
    subtotal: shopSubtotal(items),
  }))
})

function decreaseQuantity(item: BuyerCartItem) {
  if (item.quantity === 1) return

  updateCartItemMutation.mutate(
    { itemId: item.id, quantity: item.quantity - 1 },
    {
      onError: () =>
        toast.error(
          '\u6570\u91cf\u3092\u5909\u66f4\u3067\u304d\u307e\u305b\u3093\u3067\u3057\u305f\u3002',
        ),
    },
  )
}

function increaseQuantity(item: BuyerCartItem) {
  if (item.quantity >= item.stock_quantity) {
    toast.warning('\u5728\u5eab\u6570\u3092\u78ba\u8a8d\u3057\u3066\u304f\u3060\u3055\u3044')
    return
  }

  updateCartItemMutation.mutate(
    { itemId: item.id, quantity: item.quantity + 1 },
    {
      onError: () =>
        toast.error(
          '\u6570\u91cf\u3092\u5909\u66f4\u3067\u304d\u307e\u305b\u3093\u3067\u3057\u305f\u3002',
        ),
    },
  )
}

function removeLine(item: BuyerCartItem) {
  removeCartItemMutation.mutate(item.id, {
    onSuccess: () =>
      toast.error('\u30ab\u30fc\u30c8\u304b\u3089\u524a\u9664\u3057\u307e\u3057\u305f'),
    onError: () =>
      toast.error(
        '\u5546\u54c1\u3092\u524a\u9664\u3067\u304d\u307e\u305b\u3093\u3067\u3057\u305f\u3002',
      ),
  })
}

function openSearch() {
  void router.push({ name: 'search' })
}

function goHome() {
  void router.push({ name: 'home' })
}

function proceedToOrderConfirmation(producerId: string) {
  void router.push({ name: 'order-confirmation', query: { producer: producerId } })
}

function formatYen(amount: number) {
  return `\u7a0e\u8fbc ${amount.toLocaleString('ja-JP')}\u5186`
}
function unitPrice(item: BuyerCartItem) {
  return Math.round((item.unit_price_yen * (10_000 - item.discount_bps)) / 10_000)
}

function lineTotal(item: BuyerCartItem) {
  return unitPrice(item) * item.quantity
}

function deliveryFee(item: BuyerCartItem) {
  if (deliveryPrefecture.value === '\u5317\u6d77\u9053') {
    return item.delivery_fee_hokkaido_yen
  }

  if (deliveryPrefecture.value === '\u6c96\u7e04\u770c') {
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

function shopSubtotal(items: BuyerCartItem[]) {
  return items.reduce((total, item) => total + lineTotal(item), 0) + discountedDeliveryFee(items)
}

function lineAmount(item: BuyerCartItem, items: BuyerCartItem[]) {
  return (
    lineTotal(item) + (deliveryFeeItem(items)?.id === item.id ? discountedDeliveryFee(items) : 0)
  )
}

function regularLineAmount(item: BuyerCartItem, items: BuyerCartItem[]) {
  return (
    item.unit_price_yen * item.quantity +
    (deliveryFeeItem(items)?.id === item.id ? deliveryFee(item) : 0)
  )
}

function discountedFee(item: BuyerCartItem) {
  return Math.round((deliveryFee(item) * (10_000 - item.discount_bps)) / 10_000)
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
          <span class="grid size-9 place-items-center text-[#627469]" aria-label="Cart">
            <ShoppingCart :size="21" />
          </span>
        </div>
      </header>

      <template v-if="cartItems.length">
        <section class="min-h-0 flex-1 overflow-y-auto px-3 pt-3 pb-[82px]">
          <p class="mb-2 text-[10px] text-[#708076]">
            &#x30B7;&#x30E7;&#x30C3;&#x30D7;&#x3054;&#x3068;&#x306B;&#x304A;&#x652F;&#x6255;&#x3044;&#x624B;&#x7D9A;&#x304D;&#x3092;&#x884C;&#x3044;&#x307E;&#x3059;&#x3002;
          </p>
          <section
            v-for="group in producerGroups"
            :key="group.shopName"
            class="mb-2.5 rounded-[7px] border border-[#dce5dc] bg-white p-2"
            :aria-label="group.shopName"
          >
            <h2 class="mb-1.5 flex items-center gap-1 text-[12px] font-bold text-[#237f4b]">
              <Store :size="13" stroke-width="2.25" />
              {{ group.shopName }}
            </h2>
            <article
              v-for="(item, index) in group.items"
              :key="item.id"
              class="flex gap-2 py-1.5"
              :class="{ 'border-b border-[#e6ece6]': index < group.items.length - 1 }"
            >
              <img
                v-if="item.image_url"
                :src="item.image_url"
                :alt="item.product_name"
                class="size-[52px] shrink-0 rounded-[4px] object-cover"
              />
              <span v-else class="size-[52px] shrink-0 rounded-[4px] bg-[#e5eee7]" />
              <div class="min-w-0 flex-1">
                <h2 class="m-0 truncate text-[12px] font-bold">{{ item.product_name }}</h2>
                <p class="mt-0.5 mb-0 text-[10px] text-[#66776c]">{{ item.variant_label }}</p>
                <p class="mt-1 mb-0 text-[12px]">
                  <span v-if="item.discount_bps" class="mr-1 text-[#89968e] line-through">
                    {{ formatYen(regularLineAmount(item, group.items)) }}
                  </span>
                  <strong class="text-[#d94339]">
                    {{ formatYen(lineAmount(item, group.items)) }}
                  </strong>
                </p>
                <div class="mt-1 flex items-center gap-2">
                  <div class="flex h-6 overflow-hidden rounded-[3px] border border-[#dce5de]">
                    <button
                      class="grid w-6 place-items-center border-0 border-r border-[#dce5de] bg-white text-[#547064] disabled:text-[#c2cdc5]"
                      type="button"
                      :disabled="item.quantity === 1 || updateCartItemMutation.isPending.value"
                      aria-label="Decrease quantity"
                      @click="decreaseQuantity(item)"
                    >
                      -
                    </button>
                    <span class="grid min-w-6 place-items-center text-[12px] font-bold">
                      {{ item.quantity }}
                    </span>
                    <button
                      class="grid w-6 place-items-center border-0 border-l border-[#dce5de] bg-white text-[#547064] disabled:text-[#c2cdc5]"
                      type="button"
                      :disabled="
                        item.quantity === item.stock_quantity ||
                        updateCartItemMutation.isPending.value
                      "
                      aria-label="Increase quantity"
                      @click="increaseQuantity(item)"
                    >
                      +
                    </button>
                  </div>
                </div>
              </div>
              <button
                class="grid size-8 shrink-0 place-items-center rounded-full border-0 bg-transparent text-[#708076]"
                type="button"
                :disabled="removeCartItemMutation.isPending.value"
                :aria-label="item.product_name + '\u3092\u524a\u9664'"
                @click="removeLine(item)"
              >
                <Trash2 :size="17" />
              </button>
            </article>
            <div class="mt-1 flex items-center justify-between text-[11px] text-[#53645a]">
              <span>
                &#x30B7;&#x30E7;&#x30C3;&#x30D7;&#x5C0F;&#x8A08;&#xFF08;{{
                  group.itemCount
                }}&#x70B9;&#x30FB;&#x7A0E;&#x8FBC;&#xFF09;
              </span>
              <strong class="text-[#33443a]">{{ formatYen(group.subtotal) }}</strong>
            </div>
            <button
              class="mt-2 min-h-8 w-full rounded-[4px] border-0 bg-[#237f4b] text-[11px] font-bold text-white"
              type="button"
              @click="proceedToOrderConfirmation(group.producerId)"
            >
              &#x3053;&#x306E;&#x30B7;&#x30E7;&#x30C3;&#x30D7;&#x306E;&#x5546;&#x54C1;&#x3092;&#x8CFC;&#x5165;&#x3059;&#x308B;
            </button>
          </section>
        </section>
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
