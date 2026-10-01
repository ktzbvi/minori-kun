<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { ChevronDown, ChevronRight, Image as ImageIcon, Plus, Search } from 'lucide-vue-next'
import { useQuery } from '@tanstack/vue-query'
import { UiButton } from '@minorikun/ui'
import { producerProductsQuery } from '@/services/products/product.query'
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

const productFilters = computed<ProducerProductListFilters>(() => ({
  keyword: keyword.value.trim(),
  category: categoryFilter.value,
  publication_state: publicationFilter.value,
  stock_state: stockFilter.value,
}))

const productsQuery = useQuery(computed(() => producerProductsQuery(productFilters.value)))
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
</script>

<template>
  <div>
    <section class="flex flex-col gap-6 min-[980px]:flex-row min-[980px]:items-start min-[980px]:justify-between">
      <div>
        <h1 class="text-[40px] leading-tight font-extrabold text-[#17241d] min-[761px]:text-[50px] lg:text-[48px]">商品一覧</h1>
        <p class="mt-2 text-xl font-medium text-[#66766e] min-[761px]:text-[26px] lg:text-2xl">
          登録した商品を確認・管理できます。
        </p>
      </div>
      <RouterLink
        to="/products/new"
        class="inline-flex min-h-[58px] w-full items-center justify-center gap-3 rounded-[14px] bg-[#24884f] px-7 text-lg font-extrabold text-white shadow-sm transition-colors hover:bg-[#176b3e] focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b] min-[761px]:w-auto min-[761px]:min-w-[316px] min-[761px]:text-2xl"
      >
        <Plus class="size-7" :stroke-width="3" aria-hidden="true" />
        商品を登録する
      </RouterLink>
    </section>

    <section class="mt-8 rounded-[20px] border border-[#d6e2da] bg-white p-5 shadow-sm min-[761px]:p-9" aria-label="商品絞り込み">
      <div class="grid gap-4 min-[980px]:grid-cols-[minmax(280px,1.3fr)_repeat(3,minmax(210px,1fr))] min-[980px]:gap-5">
        <label class="relative block">
          <span class="sr-only">商品名・商品IDで検索</span>
          <Search class="absolute top-1/2 left-5 size-7 -translate-y-1/2 text-[#68766e]" :stroke-width="2.5" aria-hidden="true" />
          <input
            v-model="keyword"
            type="search"
            class="h-[58px] w-full rounded-[14px] border border-[#cfded5] bg-white pr-4 pl-16 text-lg font-medium text-[#1d2b24] outline-none transition-colors placeholder:text-[#8a988f] focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15 min-[761px]:h-[76px] min-[761px]:text-2xl"
            placeholder="商品名・商品IDで検索"
          />
        </label>

        <label class="relative block">
          <span class="sr-only">カテゴリ</span>
          <select v-model="categoryFilter" class="h-[58px] w-full appearance-none rounded-[14px] border border-[#cfded5] bg-white px-5 pr-14 text-lg font-medium text-[#68766e] outline-none transition-colors focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15 min-[761px]:h-[76px] min-[761px]:text-2xl">
            <option value="all">すべてのカテゴリ</option>
            <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
          </select>
          <ChevronDown class="pointer-events-none absolute top-1/2 right-5 size-7 -translate-y-1/2 text-[#68766e]" :stroke-width="3" aria-hidden="true" />
        </label>

        <label class="relative block">
          <span class="sr-only">公開状態</span>
          <select v-model="publicationFilter" class="h-[58px] w-full appearance-none rounded-[14px] border border-[#cfded5] bg-white px-5 pr-14 text-lg font-medium text-[#68766e] outline-none transition-colors focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15 min-[761px]:h-[76px] min-[761px]:text-2xl">
            <option v-for="option in publicationOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
          </select>
          <ChevronDown class="pointer-events-none absolute top-1/2 right-5 size-7 -translate-y-1/2 text-[#68766e]" :stroke-width="3" aria-hidden="true" />
        </label>

        <label class="relative block">
          <span class="sr-only">在庫状態</span>
          <select v-model="stockFilter" class="h-[58px] w-full appearance-none rounded-[14px] border border-[#cfded5] bg-white px-5 pr-14 text-lg font-medium text-[#68766e] outline-none transition-colors focus:border-[#237f4b] focus:ring-3 focus:ring-[#237f4b]/15 min-[761px]:h-[76px] min-[761px]:text-2xl">
            <option value="all">すべての在庫状態</option>
            <option value="in_stock">在庫あり</option>
            <option value="low_stock">在庫少なめ</option>
            <option value="out_of_stock">在庫なし</option>
          </select>
          <ChevronDown class="pointer-events-none absolute top-1/2 right-5 size-7 -translate-y-1/2 text-[#68766e]" :stroke-width="3" aria-hidden="true" />
        </label>
      </div>
    </section>

    <section class="mt-8 rounded-[20px] border border-[#d6e2da] bg-white p-5 shadow-sm min-[761px]:p-9" aria-labelledby="products-title">
      <header class="mb-6 flex items-center justify-between">
        <h2 id="products-title" class="text-[30px] font-extrabold text-[#17241d]">商品</h2>
        <p class="text-xl font-bold text-[#68766e]">{{ productsQuery.isPending.value ? '確認中' : `${products.length}件` }}</p>
      </header>

      <div v-if="productsQuery.isPending.value" class="rounded-[14px] bg-[#f4f7f4] p-8 text-center" role="status">
        <p class="text-lg font-bold text-[#68766e]">商品を確認しています...</p>
      </div>

      <div v-else-if="productsQuery.isError.value" class="rounded-[14px] border border-[#e4c9c3] bg-white p-8 text-center">
        <p class="text-base font-bold text-[#7b3329]" role="alert">{{ getApiErrorMessage(productsQuery.error.value) || '商品を取得できませんでした。' }}</p>
        <UiButton class="mt-5" variant="outline" :disabled="productsQuery.isFetching.value" @click="productsQuery.refetch()">
          {{ productsQuery.isFetching.value ? '確認中...' : '再試行' }}
        </UiButton>
      </div>

      <div v-else-if="products.length === 0" class="rounded-[14px] bg-[#f4f7f4] p-8 text-center">
        <p class="text-lg font-bold text-[#1d2b24]">条件に一致する商品がありません。</p>
        <button type="button" class="mt-4 text-base font-bold text-[#16804d] underline underline-offset-4" @click="resetFilters">
          絞り込みを解除する
        </button>
      </div>

      <div v-else class="hidden min-[980px]:block">
        <div class="grid min-h-[70px] grid-cols-[120px_minmax(280px,1.7fr)_190px_180px_130px_200px_44px] items-center gap-4 rounded-[14px] bg-[#f2f5f3] px-6 text-xl font-bold text-[#7a8880]">
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
              class="grid min-h-[112px] grid-cols-[120px_minmax(280px,1.7fr)_190px_180px_130px_200px_44px] items-center gap-4 px-6 py-5 focus-visible:outline-3 focus-visible:outline-offset-[-3px] focus-visible:outline-[#237f4b]"
            >
              <span class="text-xl font-medium text-[#68766e]">{{ product.display_id }}</span>
              <span class="flex min-w-0 items-center gap-6">
                <img v-if="product.image_url" :src="product.image_url" :alt="`${product.name}の商品画像`" class="size-[72px] shrink-0 rounded-[10px] object-cover" />
                <span v-else class="grid size-[72px] shrink-0 place-items-center rounded-[10px] bg-[#edf3ef] text-[#7a8880]" aria-hidden="true">
                  <ImageIcon class="size-8" :stroke-width="1.8" />
                </span>
                <span class="min-w-0">
                  <span class="block truncate text-2xl font-extrabold text-[#1d2b24]">{{ product.name }}</span>
                  <span v-if="discountLabel(product.discount_bps)" class="mt-2 inline-flex rounded-full bg-[#fde3df] px-5 py-1.5 text-lg font-extrabold text-[#d14a38]">
                    {{ discountLabel(product.discount_bps) }}
                  </span>
                </span>
              </span>
              <span class="text-2xl font-medium text-[#1d2b24]">{{ product.category || '未設定' }}</span>
              <span class="text-2xl font-extrabold text-[#1d2b24]">{{ priceLabel(product.price_yen, product.has_multiple_prices) }}</span>
              <span class="text-2xl font-medium text-[#1d2b24]">{{ product.stock_quantity }}</span>
              <span class="inline-flex w-fit min-w-[136px] items-center justify-center gap-3 rounded-full px-5 py-2 text-xl font-extrabold" :class="publicationClass(product.publication_state)">
                <span class="size-4 rounded-full" :class="publicationDotClass(product.publication_state)" aria-hidden="true" />
                {{ publicationLabel(product.publication_state) }}
              </span>
              <ChevronRight class="size-8 text-[#16804d]" :stroke-width="3" aria-hidden="true" />
            </RouterLink>
          </li>
        </ul>
      </div>

      <ul v-if="!productsQuery.isPending.value && !productsQuery.isError.value && products.length > 0" class="grid gap-4 min-[980px]:hidden" aria-label="商品一覧">
        <li v-for="product in products" :key="product.id">
          <RouterLink
            :to="`/products/${product.id}`"
            class="grid grid-cols-[88px_minmax(0,1fr)_28px] gap-4 rounded-[16px] border border-[#d9e3dc] bg-white p-4 focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-[#237f4b]"
          >
            <img v-if="product.image_url" :src="product.image_url" :alt="`${product.name}の商品画像`" class="size-[88px] rounded-[12px] object-cover" />
            <span v-else class="grid size-[88px] place-items-center rounded-[12px] bg-[#edf3ef] text-[#7a8880]" aria-hidden="true">
              <ImageIcon class="size-9" :stroke-width="1.8" />
            </span>
            <span class="min-w-0">
              <span class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-[#68766e]">{{ product.display_id }}</span>
                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold" :class="publicationClass(product.publication_state)">
                  <span class="size-2.5 rounded-full" :class="publicationDotClass(product.publication_state)" aria-hidden="true" />
                  {{ publicationLabel(product.publication_state) }}
                </span>
              </span>
              <span class="mt-2 block truncate text-xl font-extrabold text-[#1d2b24]">{{ product.name }}</span>
              <span class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm font-bold text-[#68766e]">
                <span>{{ product.category || '未設定' }}</span>
                <span>{{ priceLabel(product.price_yen, product.has_multiple_prices) }}</span>
                <span>在庫 {{ product.stock_quantity }}</span>
              </span>
              <span v-if="discountLabel(product.discount_bps)" class="mt-3 inline-flex rounded-full bg-[#fde3df] px-3 py-1 text-sm font-extrabold text-[#d14a38]">
                {{ discountLabel(product.discount_bps) }}
              </span>
            </span>
            <ChevronRight class="mt-8 size-7 text-[#16804d]" :stroke-width="3" aria-hidden="true" />
          </RouterLink>
        </li>
      </ul>
    </section>
  </div>
</template>
