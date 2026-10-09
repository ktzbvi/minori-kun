<script setup lang="ts">
import {
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  Search,
  ShoppingCart,
  PackageOpen,
  SlidersHorizontal,
  LoaderCircle,
  X,
} from 'lucide-vue-next'
import {
  UiButton,
  UiCard,
  UiBadge,
  UiDialog,
  UiDrawer,
  UiDrawerContent,
  UiDrawerTrigger,
  UiDrawerClose,
  UiDrawerTitle,
  UiDrawerDescription,
  UiRadioGroup,
  UiInput,
  UiFormLabel,
  UiFormMessage,
  UiSkeleton,
} from '@minorikun/ui'
import BuyerPageShell from '@/components/BuyerPageShell.vue'
import BuyerLayout from '@/components/layout/BuyerLayout.vue'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import {
  orderStatusLabel,
  paymentStatusLabel,
  refundStatusLabel,
} from '@/services/orders/orders.status'
import { useBuyerOrderHistory } from '@/composables/useBuyerOrderHistory'
const {
  router,
  filterOpen,
  desktopFilters,
  filters,
  orders,
  periodOptions,
  appliedPeriodLabel,
  applyFilters,
  openFilters,
  closeFilters,
  resetFilters,
  openSearch,
  openCart,
  goBack,
  openOrder,
  retryOrders,
  yen,
  date,
} = useBuyerOrderHistory()
</script>
<template>
  <div class="hidden lg:block">
    <BuyerLayout active="profile">
      <template #mobile-header>
        <header
          class="flex h-[60px] items-center justify-between border-b border-[#e3e9e3] bg-white px-3"
        >
          <div class="flex items-center gap-2">
            <UiButton
              variant="ghost"
              class="size-9 min-h-9 p-0 text-[#237d4a]"
              aria-label="マイページに戻る"
              @click="goBack"
            >
              <ChevronLeft class="size-5" aria-hidden="true" />
            </UiButton>
            <h1 class="text-base font-bold text-[#237d4a]">注文履歴</h1>
          </div>
          <div class="flex gap-2">
            <UiButton
              variant="ghost"
              class="size-9 min-h-9 p-0 text-[#627469]"
              aria-label="検索"
              @click="openSearch"
            >
              <Search class="size-5" aria-hidden="true" />
            </UiButton>
            <UiButton
              variant="ghost"
              class="size-9 min-h-9 p-0 text-[#627469]"
              aria-label="カート"
              @click="openCart"
            >
              <ShoppingCart class="size-5" aria-hidden="true" />
            </UiButton>
          </div>
        </header>
      </template>
      <section
        class="mx-auto w-full flex-1 space-y-3 px-3 pt-3 pb-[calc(88px+env(safe-area-inset-bottom))] lg:max-w-4xl lg:space-y-5 lg:px-8 lg:pt-8 lg:pb-12"
      >
        <div class="hidden lg:block">
          <UiButton
            variant="ghost"
            class="mb-3 min-h-9 gap-1 px-0 text-sm text-[#687a70]"
            @click="goBack"
          >
            <ChevronLeft class="size-4" aria-hidden="true" />
            マイページに戻る
          </UiButton>
          <h1 class="text-2xl font-bold">注文履歴</h1>
          <p class="mt-2 text-sm leading-6 text-[#687a70]">
            ご注文の内容やお支払いの状況をご確認いただけます。
          </p>
        </div>
        <div
          class="lg:flex lg:items-center lg:justify-between lg:rounded-xl lg:border lg:border-[#dce5dc] lg:bg-white lg:px-5 lg:py-4"
        >
          <div class="hidden lg:block">
            <p class="text-sm font-semibold">注文期間</p>
            <p class="mt-1 text-sm text-[#687a70]">{{ appliedPeriodLabel }}</p>
          </div>
          <UiButton
            variant="outline"
            class="min-h-9 w-full justify-between gap-3 rounded-lg border-[#dce5dc] bg-white px-3 text-xs font-medium text-[#526258] lg:min-h-11 lg:w-auto lg:min-w-52 lg:text-sm"
            aria-haspopup="dialog"
            :aria-expanded="filterOpen"
            @click="openFilters"
          >
            <SlidersHorizontal class="hidden size-4 lg:block" aria-hidden="true" />
            <span>絞り込み</span>
            <ChevronDown class="size-4" aria-hidden="true" />
          </UiButton>
        </div>
        <div
          v-if="orders.isLoading.value"
          class="space-y-3"
          role="status"
          aria-label="注文履歴を読み込み中"
        >
          <UiCard v-for="n in 3" :key="n" class="grid gap-4 border-[#dce5dc] p-4 lg:p-6">
            <UiSkeleton class="h-5 w-1/3" />
            <UiSkeleton class="h-14 w-full" />
            <UiSkeleton class="h-4 w-1/2" />
          </UiCard>
          <p class="sr-only">注文履歴を読み込んでいます。</p>
        </div>
        <UiCard v-else-if="orders.isError.value" class="border-[#dce5dc] py-8 text-center lg:py-12">
          <p class="text-sm leading-6 text-[#b33a2b]" role="alert">
            注文履歴を読み込めませんでした。
          </p>
          <UiButton
            variant="outline"
            class="mt-4"
            :disabled="orders.isFetching.value"
            @click="retryOrders"
          >
            <LoaderCircle
              v-if="orders.isFetching.value"
              class="size-4 animate-spin"
              aria-hidden="true"
            />
            もう一度試す
          </UiButton>
        </UiCard>
        <UiCard
          v-else-if="!orders.data.value?.length"
          class="grid justify-items-center gap-3 border-[#dce5dc] py-10 text-center lg:py-16"
        >
          <PackageOpen class="size-9 text-[#87a08e]" aria-hidden="true" />
          <p class="text-base font-semibold">該当する注文はありません。</p>
          <p class="text-sm leading-6 text-[#687a70]">注文期間の条件を変更してご確認ください。</p>
          <UiButton variant="outline" class="mt-2" @click="openFilters">
            絞り込み条件を変更
          </UiButton>
        </UiCard>
        <template v-else>
          <UiCard
            v-for="order in orders.data.value"
            :key="order.id"
            class="overflow-hidden rounded-lg border-[#dce5dc] bg-white p-0 shadow-none lg:rounded-xl"
          >
            <UiButton
              variant="ghost"
              class="flex w-full flex-col items-stretch justify-start gap-0 whitespace-normal rounded-none border-0 p-3 text-left font-normal text-[#26362c] hover:bg-[#f6faf7] lg:p-6"
              @click="openOrder(order.id)"
            >
              <span class="flex items-start justify-between gap-3">
                <span class="min-w-0">
                  <strong class="block break-words text-sm lg:text-lg">
                    {{ order.shop_name }}
                  </strong>
                  <span class="mt-1 block text-xs text-[#68786e] lg:mt-2 lg:text-sm">
                    注文番号 {{ order.order_number }}
                  </span>
                  <span class="mt-1 block text-xs text-[#849188]">{{ date(order.placed_at) }}</span>
                </span>
                <span class="flex shrink-0 items-center gap-2 text-[#237f4b]">
                  <span class="hidden text-sm font-semibold lg:inline">注文詳細</span>
                  <ChevronRight class="size-5" aria-hidden="true" />
                </span>
              </span>
              <span
                v-for="item in order.items.slice(0, 2)"
                :key="item.product_name"
                class="mt-3 flex items-center gap-3 border-t border-[#e5ebe5] pt-3 lg:mt-4 lg:gap-4 lg:pt-4"
              >
                <img
                  v-if="item.image_url"
                  :src="item.image_url"
                  class="size-10 shrink-0 rounded-md object-cover lg:size-16"
                  alt=""
                />
                <span
                  v-else
                  class="grid size-10 shrink-0 place-items-center rounded-md bg-[#edf2ed] lg:size-16"
                >
                  <PackageOpen class="size-5 text-[#87a08e]" aria-hidden="true" />
                </span>
                <span class="min-w-0 flex-1 break-words text-xs lg:text-base">
                  {{ item.product_name }}
                  <span class="ml-2 text-[#68786e]">× {{ item.quantity }}</span>
                </span>
              </span>
              <span
                v-if="order.items.length > 2"
                class="mt-2 block text-right text-xs text-[#68786e]"
              >
                ほか{{ order.items.length - 2 }}点
              </span>
              <span
                class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-[#e5ebe5] pt-3 lg:mt-5 lg:pt-4"
              >
                <strong class="text-sm lg:text-lg">合計 {{ yen(order.total_yen) }}</strong>
                <span class="flex flex-wrap gap-2">
                  <UiBadge class="bg-[#edf3f8] text-[#33443a]">
                    注文：{{ orderStatusLabel(order.order_state) }}
                  </UiBadge>
                  <UiBadge class="bg-[#e9f5ee] text-[#33443a]">
                    支払：{{ paymentStatusLabel(order.payment_state) }}
                  </UiBadge>
                  <UiBadge
                    v-if="refundStatusLabel(order.refund_state)"
                    class="bg-[#fff4e5] text-[#33443a]"
                  >
                    返金：{{ refundStatusLabel(order.refund_state) }}
                  </UiBadge>
                </span>
              </span>
            </UiButton>
          </UiCard>
        </template>
      </section>
      <template #mobile-navigation>
        <div
          class="fixed inset-x-0 bottom-0 z-40 h-[calc(64px+env(safe-area-inset-bottom))] bg-white"
        >
          <div class="relative h-16"><BuyerBottomNavigation active="profile" /></div>
        </div>
      </template>
      <UiDialog
        :open="filterOpen && desktopFilters"
        title="絞り込み"
        :description="desktopFilters ? '注文期間を選択してください。' : undefined"
        :presentation="desktopFilters ? 'dialog' : 'sheet'"
        :content-class="
          desktopFilters ? 'max-w-md' : 'h-auto max-h-[82dvh] max-w-none rounded-t-[20px] px-4 pt-2'
        "
        :title-class="desktopFilters ? undefined : 'text-base'"
        :footer-class="desktopFilters ? undefined : 'grid grid-cols-2 gap-3'"
        @update:open="!$event && closeFilters()"
      >
        <form id="buyer-order-filters" class="grid gap-4" @submit.prevent="applyFilters">
          <fieldset>
            <legend class="mb-2 text-sm font-bold">注文期間</legend>
            <UiRadioGroup v-model="filters.period" :options="periodOptions" aria-label="注文期間" />
          </fieldset>
          <div v-if="filters.period === 'year'" class="grid gap-2">
            <UiFormLabel for="order-year">年</UiFormLabel>
            <UiInput
              id="order-year"
              v-model="filters.year"
              type="number"
              min="2000"
              max="2100"
              required
              class="h-11"
            />
          </div>
          <div v-if="filters.period === 'custom'" class="grid grid-cols-2 gap-3">
            <div class="grid gap-2">
              <UiFormLabel for="order-from">開始日</UiFormLabel>
              <UiInput
                id="order-from"
                v-model="filters.from"
                type="date"
                class="h-11"
                required
                :aria-invalid="Boolean(filters.from && filters.to && filters.from > filters.to)"
                :aria-describedby="
                  filters.from && filters.to && filters.from > filters.to
                    ? 'order-range-error'
                    : undefined
                "
              />
            </div>
            <div class="grid gap-2">
              <UiFormLabel for="order-to">終了日</UiFormLabel>
              <UiInput id="order-to" v-model="filters.to" type="date" class="h-11" required />
            </div>
            <UiFormMessage
              v-if="filters.from && filters.to && filters.from > filters.to"
              id="order-range-error"
              class="col-span-2"
              role="alert"
            >
              開始日は終了日より前の日付を選択してください。
            </UiFormMessage>
          </div>
        </form>
        <template #footer>
          <UiButton variant="outline" @click="resetFilters">リセット</UiButton>
          <UiButton
            type="submit"
            form="buyer-order-filters"
            :disabled="
              filters.period === 'custom' &&
              (!filters.from || !filters.to || filters.from > filters.to)
            "
          >
            適用する
          </UiButton>
        </template>
      </UiDialog>
    </BuyerLayout>
  </div>
  <div class="lg:hidden">
    <UiDrawer
      :open="filterOpen && !desktopFilters"
      swipe-direction="down"
      @update:open="!$event && closeFilters()"
    >
      <BuyerPageShell active="profile" full-width>
        <header
          class="flex h-[60px] shrink-0 items-center justify-between border-b border-[#e3e9e3] bg-white px-3"
        >
          <div class="flex items-center gap-2">
            <button
              class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
              type="button"
              aria-label="戻る"
              @click="router.push({ name: 'my-page' })"
            >
              <ChevronLeft :size="22" />
            </button>
            <h1 class="m-0 text-base font-bold text-[#237d4a]">注文履歴</h1>
          </div>
          <div class="flex gap-2">
            <button
              class="grid size-9 place-items-center border-0 bg-transparent text-[#627469]"
              type="button"
              aria-label="検索"
              @click="openSearch"
            >
              <Search :size="21" />
            </button>
            <button
              class="grid size-9 place-items-center border-0 bg-transparent text-[#627469]"
              type="button"
              aria-label="カート"
              @click="openCart"
            >
              <ShoppingCart :size="21" />
            </button>
          </div>
        </header>
        <section class="min-h-0 flex-1 space-y-3 overflow-y-auto px-3 py-3 pb-[78px]">
          <UiDrawerTrigger as-child>
            <button
              class="flex min-h-9 w-full items-center justify-between rounded-lg border border-[#dce5dc] bg-white px-3 text-left text-xs text-[#526258]"
              type="button"
              aria-haspopup="dialog"
              :aria-expanded="filterOpen"
              @click="openFilters"
            >
              <span>絞り込み</span>
              <ChevronDown :size="15" />
            </button>
          </UiDrawerTrigger>
          <p v-if="orders.isLoading.value" class="py-8 text-center text-xs text-[#68786e]">
            注文履歴を読み込んでいます...
          </p>
          <p v-else-if="orders.isError.value" class="py-8 text-center text-xs text-[#b33a2b]">
            注文履歴を読み込めませんでした。
          </p>
          <p v-else-if="!orders.data.value?.length" class="py-8 text-center text-xs text-[#68786e]">
            該当する注文はありません。
          </p>
          <button
            v-for="order in orders.data.value"
            :key="order.id"
            class="block w-full rounded-md border border-[#dce5dc] bg-white p-3 text-left"
            type="button"
            @click="router.push({ name: 'order-detail', params: { orderId: order.id } })"
          >
            <span class="flex items-start justify-between gap-2">
              <span>
                <strong class="block text-xs">{{ order.shop_name }}</strong>
                <span class="mt-1 block text-[10px] text-[#68786e]">
                  注文番号 {{ order.order_number }}
                </span>
                <span class="block text-[10px] text-[#849188]">{{ date(order.placed_at) }}</span>
              </span>
              <ChevronRight :size="18" class="shrink-0" />
            </span>
            <span
              v-for="item in order.items.slice(0, 2)"
              :key="item.product_name"
              class="mt-2 flex items-center gap-2 border-t border-[#e5ebe5] pt-2"
            >
              <img
                v-if="item.image_url"
                :src="item.image_url"
                class="size-9 rounded object-cover"
                alt=""
              />
              <span v-else class="size-9 rounded bg-[#edf2ed]" />
              <span class="min-w-0 flex-1 truncate text-[10px]">
                {{ item.product_name }} × {{ item.quantity }}
              </span>
            </span>
            <span
              v-if="order.items.length > 2"
              class="mt-1 block text-right text-[10px] text-[#68786e]"
            >
              ほか{{ order.items.length - 2 }}点
            </span>
            <span
              class="mt-2 flex flex-wrap items-center justify-between gap-2 border-t border-[#e5ebe5] pt-2 text-xs"
            >
              <strong>合計 {{ yen(order.total_yen) }}</strong>
              <span class="flex flex-wrap justify-end gap-1">
                <span class="rounded bg-[#edf3f8] px-2 py-1 text-[10px]">
                  注文：{{ orderStatusLabel(order.order_state) }}
                </span>
                <span class="rounded bg-[#e9f5ee] px-2 py-1 text-[10px]">
                  支払：{{ paymentStatusLabel(order.payment_state) }}
                </span>
                <span
                  v-if="refundStatusLabel(order.refund_state)"
                  class="rounded bg-[#fff4e5] px-2 py-1 text-[10px]"
                >
                  返金：{{ refundStatusLabel(order.refund_state) }}
                </span>
              </span>
            </span>
          </button>
        </section>
        <UiDrawerContent>
          <div class="mb-4 flex items-center justify-between">
            <UiDrawerTitle class="text-base font-bold">絞り込み</UiDrawerTitle>
            <UiDrawerClose as-child>
              <button
                class="grid size-8 place-items-center border-0 bg-transparent text-[#66766d]"
                type="button"
                aria-label="閉じる"
              >
                <X :size="21" aria-hidden="true" />
              </button>
            </UiDrawerClose>
          </div>
          <UiDrawerDescription class="sr-only">
            注文期間を選択して、適用してください。
          </UiDrawerDescription>
          <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="applyFilters">
            <fieldset class="m-0 min-h-0 flex-1 overflow-y-auto border-0 p-0">
              <legend class="mb-2 text-xs font-bold">注文期間</legend>
              <label
                v-for="option in [
                  { value: 'all', label: 'すべての期間' },
                  { value: '30d', label: '過去30日' },
                  { value: '90d', label: '過去90日' },
                  { value: '12m', label: '過去12か月' },
                  { value: 'year', label: '年を選択' },
                  { value: 'custom', label: '期間を指定' },
                ]"
                :key="option.value"
                class="flex min-h-8 cursor-pointer items-center gap-2.5 text-[11px]"
              >
                <input
                  v-model="filters.period"
                  class="size-3.5 accent-[#237f4b]"
                  type="radio"
                  name="order-period"
                  :value="option.value"
                />
                {{ option.label }}
              </label>
              <div v-if="filters.period === 'year'" class="mt-2 pl-6">
                <label for="order-year" class="mb-1 block text-[10px] text-[#68786e]">年</label>
                <input
                  id="order-year"
                  v-model="filters.year"
                  class="min-h-9 w-full rounded-md border border-[#dce5dc] px-2 text-xs"
                  type="number"
                  min="2000"
                  max="2100"
                />
              </div>
              <div v-if="filters.period === 'custom'" class="mt-2 grid grid-cols-2 gap-3">
                <label class="text-[10px]">
                  開始日
                  <input
                    v-model="filters.from"
                    class="mt-1 min-h-9 w-full rounded-lg border border-[#dce5dc] px-2 text-xs"
                    type="date"
                  />
                </label>
                <label class="text-[10px]">
                  終了日
                  <input
                    v-model="filters.to"
                    class="mt-1 min-h-9 w-full rounded-lg border border-[#dce5dc] px-2 text-xs"
                    type="date"
                  />
                </label>
                <p
                  v-if="filters.from && filters.to && filters.from > filters.to"
                  class="col-span-2 m-0 text-[10px] text-[#c8322a]"
                >
                  開始日は終了日より前の日付を選択してください。
                </p>
              </div>
            </fieldset>
            <div class="grid grid-cols-2 gap-3 pt-3">
              <button
                class="min-h-[38px] rounded-lg border border-[#237f4b] bg-white text-xs font-bold text-[#237f4b]"
                type="button"
                @click="resetFilters"
              >
                リセット
              </button>
              <button
                class="min-h-[38px] rounded-lg border-0 bg-[#237f4b] text-xs font-bold text-white disabled:opacity-50"
                type="submit"
                :disabled="
                  filters.period === 'custom' &&
                  (!filters.from || !filters.to || filters.from > filters.to)
                "
              >
                適用する
              </button>
            </div>
          </form>
        </UiDrawerContent>
      </BuyerPageShell>
    </UiDrawer>
  </div>
</template>
