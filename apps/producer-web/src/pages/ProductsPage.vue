<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { ChevronDown, ChevronRight, Image as ImageIcon, Plus, Search } from 'lucide-vue-next'
import { UiButton } from '@minorikun/ui'
import { useProducerProductsQuery } from '@/services/products/product.query'
import type {
  ProducerProductListFilters,
  ProducerProductListItem,
  ProducerProductPublicationState,
  ProducerProductStockState,
} from '@/types/product'
import { getApiErrorMessage } from '@/lib/api-error'

const keyword = ref('')
const categoryFilter = ref('all')
const publicationFilter = ref<'all' | ProducerProductPublicationState>('all')
const stockFilter = ref<ProducerProductStockState>('all')
const failedProductImages = reactive(new Set<string>())

const productFilters = computed<ProducerProductListFilters>(() => ({
  keyword: keyword.value.trim(),
  category: categoryFilter.value,
  publication_state: publicationFilter.value,
  stock_state: stockFilter.value,
}))

const productsQuery = useProducerProductsQuery(productFilters)
const products = computed(() => productsQuery.data.value?.data ?? [])
const categories = computed(() => productsQuery.data.value?.meta.categories ?? [])

const publicationOptions = [
  { value: 'all', label: 'すべての公開状態' },
  { value: 'published', label: '公開中' },
  { value: 'unpublished', label: '非公開' },
  { value: 'draft', label: '下書き' },
] as const

function priceLabel(price: number | null, hasMultiplePrices: boolean) {
  if (price === null) return '未設定'
  return `¥${price.toLocaleString('ja-JP')}${hasMultiplePrices ? '〜' : ''}`
}

function discountLabel(discountBps: number) {
  if (discountBps <= 0) return ''
  return `${Math.round(discountBps / 100)}%OFF`
}

function publicationLabel(state: ProducerProductListItem['publication_state']) {
  if (state === 'published') return '公開中'
  if (state === 'unpublished') return '非公開'
  return '下書き'
}

function publicationClass(state: ProducerProductListItem['publication_state']) {
  if (state === 'published') return 'bg-[#ddf2e6] text-[#16804d]'
  if (state === 'unpublished') return 'bg-[#edf1ed] text-[#68766e]'
  return 'bg-[#fff0d7] text-[#9c5a13]'
}

function publicationDotClass(state: ProducerProductListItem['publication_state']) {
  if (state === 'published') return 'bg-[#16804d]'
  if (state === 'unpublished') return 'bg-[#97a59c]'
  return 'bg-[#c77a1a]'
}

function resetFilters() {
  keyword.value = ''
  categoryFilter.value = 'all'
  publicationFilter.value = 'all'
  stockFilter.value = 'all'
}

function markProductImageFailed(productId: string) {
  failedProductImages.add(productId)
}
</script>

<template>
  <div>
    <section class="flex flex-col gap-4 lg:gap-6 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <h1 class="text-2xl leading-tight font-extrabold text-[#17241d] sm:text-3xl lg:text-4xl">商品一覧</h1>
        <p class="mt-2 text-base font-medium text-[#66766e] sm:text-base lg:text-lg">
          登録した商品を確認・管理できます。
        </p>
      </div>
      <RouterLink
        to="/products/new"
        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#24884f] px-4 text-base font-extrabold text-white shadow-sm transition-colors hover:bg-[#176b3e] focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b] min-[761px]:w-auto min-[761px]:text-base lg:min-h-[52px] lg:min-w-0 lg:px-6 lg:text-lg"
      >
        <Plus class="size-5" :stroke-width="3" aria-hidden="true" />
        商品を登録する
      </RouterLink>
    </section>

    <section class="mt-6 lg:mt-8 min-[1280px]:rounded-[20px] min-[1280px]:border min-[1280px]:border-[#d6e2da] min-[1280px]:bg-white min-[1280px]:p-6 min-[1280px]:shadow-sm" aria-label="商品絞り込み">
      <div class="grid gap-3 min-[560px]:grid-cols-3 min-[1280px]:grid-cols-[minmax(220px,1.3fr)_repeat(3,minmax(140px,1fr))] min-[1280px]:gap-4">
        <label class="relative block min-[560px]:col-span-3 min-[1280px]:col-span-1">
          <span class="sr-only">商品名・商品IDで検索</span>
          <Search class="absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-[#68766e]" :stroke-width="2.5" aria-hidden="true" />
          <input
            v-model="keyword"
            type="search"
            class="h-11 w-full rounded-xl border border-[#cfded5] bg-white pr-4 pl-11 text-base font-medium text-[#1d2b24] outline-none transition-colors placeholder:text-[#8a988f] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15 min-[761px]:h-12 min-[761px]:text-base lg:h-14 lg:text-base"
            placeholder="商品名・商品IDで検索"
          />
        </label>

        <label class="relative block">
          <span class="sr-only">カテゴリ</span>
          <select v-model="categoryFilter" class="h-11 w-full appearance-none rounded-xl border border-[#cfded5] bg-white px-3.5 pr-10 text-base font-medium text-[#68766e] outline-none transition-colors focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15 min-[761px]:h-12 min-[761px]:text-base lg:h-14 lg:text-base">
            <option value="all">カテゴリ</option>
            <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
          </select>
          <ChevronDown class="pointer-events-none absolute top-1/2 right-3.5 size-5 -translate-y-1/2 text-[#68766e]" :stroke-width="3" aria-hidden="true" />
        </label>

        <label class="relative block">
          <span class="sr-only">公開状態</span>
          <select v-model="publicationFilter" class="h-11 w-full appearance-none rounded-xl border border-[#cfded5] bg-white px-3.5 pr-10 text-base font-medium text-[#68766e] outline-none transition-colors focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15 min-[761px]:h-12 min-[761px]:text-base lg:h-14 lg:text-base">
            <option v-for="option in publicationOptions" :key="option.value" :value="option.value">{{ option.value === 'all' ? '公開状態' : option.label }}</option>
          </select>
          <ChevronDown class="pointer-events-none absolute top-1/2 right-3.5 size-5 -translate-y-1/2 text-[#68766e]" :stroke-width="3" aria-hidden="true" />
        </label>

        <label class="relative block">
          <span class="sr-only">在庫状態</span>
          <select v-model="stockFilter" class="h-11 w-full appearance-none rounded-xl border border-[#cfded5] bg-white px-3.5 pr-10 text-base font-medium text-[#68766e] outline-none transition-colors focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15 min-[761px]:h-12 min-[761px]:text-base lg:h-14 lg:text-base">
            <option value="all">在庫状態</option>
            <option value="in_stock">在庫あり</option>
            <option value="low_stock">在庫少なめ</option>
            <option value="out_of_stock">在庫なし</option>
          </select>
          <ChevronDown class="pointer-events-none absolute top-1/2 right-3.5 size-5 -translate-y-1/2 text-[#68766e]" :stroke-width="3" aria-hidden="true" />
        </label>
      </div>
    </section>

    <section class="mt-6 lg:mt-10 min-[1280px]:mt-8 min-[1280px]:rounded-[20px] min-[1280px]:border min-[1280px]:border-[#d6e2da] min-[1280px]:bg-white min-[1280px]:p-6 min-[1280px]:shadow-sm" aria-labelledby="products-title">
      <header class="mb-4 lg:mb-6 flex items-center justify-between">
        <h2 id="products-title" class="text-xl font-extrabold text-[#17241d] lg:text-[30px]">商品</h2>
        <p class="text-sm font-bold text-[#68766e] lg:text-xl">{{ productsQuery.isPending.value ? '確認中' : `${products.length}件` }}</p>
      </header>

      <div v-if="productsQuery.isPending.value" class="rounded-[14px] bg-[#f4f7f4] p-4 text-center lg:p-8" role="status">
        <p class="text-lg font-bold text-[#68766e]">商品を確認しています...</p>
      </div>

      <div v-else-if="productsQuery.isError.value" class="rounded-[14px] border border-[#e4c9c3] bg-white p-4 text-center lg:p-8">
        <p class="text-base font-bold text-[#7b3329]" role="alert">{{ getApiErrorMessage(productsQuery.error.value) || '商品を取得できませんでした。' }}</p>
        <UiButton class="mt-5" variant="outline" :disabled="productsQuery.isFetching.value" @click="productsQuery.refetch()">
          {{ productsQuery.isFetching.value ? '確認中...' : '再試行' }}
        </UiButton>
      </div>

      <div v-else-if="products.length === 0" class="rounded-[14px] bg-[#f4f7f4] p-4 text-center lg:p-8">
        <p class="text-lg font-bold text-[#1d2b24]">条件に一致する商品がありません。</p>
        <button type="button" class="mt-4 text-base font-bold text-[#16804d] underline underline-offset-4" @click="resetFilters">
          絞り込みを解除する
        </button>
      </div>

      <div v-else class="hidden min-[1280px]:block">
        <div class="grid min-h-14 grid-cols-[90px_minmax(220px,1.5fr)_minmax(120px,.7fr)_minmax(120px,.7fr)_80px_120px_28px] items-center gap-3 rounded-[14px] bg-[#f2f5f3] px-4 text-base font-bold text-[#7a8880]">
          <span>商品ID</span>
          <span>商品</span>
          <span>カテゴリ</span>
          <span>価格（税込）</span>
          <span>在庫</span>
          <span>公開状態</span>
          <span class="sr-only">詳細</span>
        </div>
        <ul class="divide-y divide-[#d9e3dc]">
          <li v-for="product in products" :key="product.id">
            <RouterLink
              :to="`/products/${product.id}`"
              class="grid min-h-[88px] grid-cols-[90px_minmax(220px,1.5fr)_minmax(120px,.7fr)_minmax(120px,.7fr)_80px_120px_28px] items-center gap-3 px-4 py-4 focus-visible:outline-3 focus-visible:outline-offset-[-3px] focus-visible:outline-[#237f4b]"
            >
              <span class="text-base font-medium text-[#68766e]">{{ product.display_id }}</span>
              <span class="flex min-w-0 items-center gap-4">
                <img
                  v-if="product.image_url && !failedProductImages.has(product.id)"
                  :src="product.image_url"
                  :alt="`${product.name}の商品画像`"
                  class="size-14 shrink-0 rounded-[10px] object-cover"
                  @error="markProductImageFailed(product.id)"
                />
                <span v-else class="grid size-14 shrink-0 place-items-center rounded-[10px] bg-[#edf3ef] text-[#7a8880]" aria-hidden="true">
                  <ImageIcon class="size-8" :stroke-width="1.8" />
                </span>
                <span class="min-w-0">
                  <span class="block truncate text-lg font-extrabold text-[#1d2b24]">{{ product.name }}</span>
                  <span v-if="discountLabel(product.discount_bps)" class="mt-1 inline-flex rounded-full bg-[#fde3df] px-3 py-1 text-sm font-extrabold text-[#d14a38]">
                    {{ discountLabel(product.discount_bps) }}
                  </span>
                </span>
              </span>
              <span class="truncate text-base font-medium text-[#1d2b24]">{{ product.category || '未設定' }}</span>
              <span class="text-lg font-extrabold text-[#1d2b24]">{{ priceLabel(product.price_yen, product.has_multiple_prices) }}</span>
              <span class="text-lg font-medium text-[#1d2b24]">{{ product.stock_quantity }}</span>
              <span class="inline-flex w-fit min-w-[104px] items-center justify-center gap-2 rounded-full px-3 py-2 text-base font-extrabold" :class="publicationClass(product.publication_state)">
                <span class="size-3 rounded-full" :class="publicationDotClass(product.publication_state)" aria-hidden="true" />
                {{ publicationLabel(product.publication_state) }}
              </span>
              <ChevronRight class="size-6 text-[#16804d]" :stroke-width="3" aria-hidden="true" />
            </RouterLink>
          </li>
        </ul>
      </div>

      <ul v-if="!productsQuery.isPending.value && !productsQuery.isError.value && products.length > 0" class="grid gap-4 min-[600px]:gap-[18px] min-[1280px]:hidden" aria-label="商品一覧">
        <li v-for="product in products" :key="product.id">
          <RouterLink
            :to="`/products/${product.id}`"
            class="relative grid min-h-[132px] grid-cols-[64px_minmax(0,1fr)] gap-3 rounded-[16px] border border-[#d9e3dc] bg-white p-3 pr-9 focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-[#237f4b] min-[600px]:min-h-[144px] min-[600px]:grid-cols-[80px_minmax(0,1fr)_112px] min-[600px]:gap-4 min-[600px]:rounded-[18px] min-[600px]:p-4 min-[600px]:pr-10 lg:min-h-[164px] lg:grid-cols-[104px_minmax(0,1fr)_132px] lg:gap-5 lg:p-5 lg:pr-12"
          >
            <img
              v-if="product.image_url && !failedProductImages.has(product.id)"
              :src="product.image_url"
              :alt="`${product.name}の商品画像`"
              class="row-span-2 size-16 self-center rounded-[12px] object-cover min-[600px]:row-span-1 min-[600px]:size-20 min-[600px]:rounded-[14px] lg:size-[104px]"
              @error="markProductImageFailed(product.id)"
            />
            <span v-else class="row-span-2 grid size-16 self-center place-items-center rounded-[12px] bg-[#edf3ef] text-[#7a8880] min-[600px]:row-span-1 min-[600px]:size-20 min-[600px]:rounded-[14px] lg:size-[104px]" aria-hidden="true">
              <ImageIcon class="size-9" :stroke-width="1.8" />
            </span>
            <span class="flex min-w-0 flex-col justify-center">
              <span class="block truncate text-base font-extrabold text-[#1d2b24] min-[600px]:text-lg lg:text-xl">{{ product.name }}</span>
              <span class="mt-1 block truncate text-xs font-medium text-[#68766e] min-[600px]:text-sm lg:text-base">
                {{ product.display_id }} · {{ product.category || '未設定' }}
              </span>
              <span class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm font-extrabold text-[#1d2b24] min-[600px]:mt-3 min-[600px]:text-base lg:mt-4 lg:text-lg">
                <span>{{ priceLabel(product.price_yen, product.has_multiple_prices) }}</span>
                <span>在庫 {{ product.stock_quantity }}</span>
              </span>
            </span>
            <span class="col-start-2 flex flex-wrap items-center gap-2 min-[600px]:col-start-3 min-[600px]:row-start-1 min-[600px]:flex-col min-[600px]:items-end min-[600px]:justify-center min-[600px]:gap-3">
              <span class="inline-flex min-w-0 items-center justify-center gap-2 rounded-full px-2.5 py-1 text-xs font-extrabold min-[600px]:min-w-0 min-[600px]:text-sm lg:min-w-[124px] lg:text-base" :class="publicationClass(product.publication_state)">
                <span class="size-2 rounded-full min-[600px]:size-2.5" :class="publicationDotClass(product.publication_state)" aria-hidden="true" />
                {{ publicationLabel(product.publication_state) }}
              </span>
              <span v-if="discountLabel(product.discount_bps)" class="inline-flex px-1 text-sm font-extrabold text-[#cf302d] min-[600px]:text-sm lg:text-base">
                {{ discountLabel(product.discount_bps) }}
              </span>
            </span>
            <ChevronRight class="absolute top-1/2 right-2 size-5 -translate-y-1/2 text-[#68766e] min-[600px]:right-3" :stroke-width="3" aria-hidden="true" />
          </RouterLink>
        </li>
      </ul>
    </section>
  </div>
</template>
