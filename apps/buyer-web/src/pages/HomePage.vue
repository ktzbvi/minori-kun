<script setup lang="ts">
import { ImageOff, LoaderCircle, Search } from 'lucide-vue-next'
import { UiButton, UiCard, UiSkeleton } from '@minorikun/ui'
import BuyerLayout from '@/components/layout/BuyerLayout.vue'
import BuyerCartButton from '@/components/layout/BuyerCartButton.vue'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import BuyerBrand from '@/components/BuyerBrand.vue'
import { useBuyerHome } from '@/composables/useBuyerHome'
const {
  productList,
  loadMoreTrigger,
  selectedCategory,
  catalogueQuery,
  addCartItemMutation,
  categories,
  productTotal,
  displayedProducts,
  hasMoreProducts,
  retryProducts,
  addToCart,
  openProduct,
  openSearch,
  productPrice,
  discountRate,
  formatYen,
} = useBuyerHome()
</script>

<template>
  <BuyerLayout>
    <div
      class="bg-[#e4ebe6] sm:grid sm:place-items-center sm:p-6 lg:block lg:bg-transparent lg:p-0"
    >
      <section
        class="relative flex h-dvh w-full flex-col bg-[#f8faf6] sm:max-w-[375px] sm:shadow-sm lg:h-auto lg:max-w-none lg:shadow-none"
      >
        <header
          class="flex h-[74px] shrink-0 items-center justify-between border-b border-[#e3e9e3] bg-white px-4 lg:hidden"
        >
          <BuyerBrand size="header" />
          <div class="flex items-center gap-3">
            <UiButton
              variant="ghost"
              class="grid size-9 min-h-9 place-items-center rounded-full border-0 bg-transparent text-[#627469]"
              type="button"
              aria-label="検索"
              @click="openSearch"
            >
              <Search :size="21" aria-hidden="true" />
            </UiButton>
            <BuyerCartButton />
          </div>
        </header>
        <div
          class="shrink-0 border-b border-[#e6ece6] bg-white px-4 py-2.5 lg:mx-auto lg:w-full lg:max-w-6xl lg:border-0 lg:bg-transparent lg:px-8 lg:pt-6 lg:pb-4"
        >
          <div class="flex gap-2 overflow-x-auto pb-0.5 lg:flex-wrap">
            <UiButton
              v-for="category in categories"
              :key="category.id"
              variant="outline"
              class="min-h-7 shrink-0 rounded-full border px-3 text-xs font-bold lg:min-h-10 lg:px-5 lg:text-sm"
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
            </UiButton>
          </div>
        </div>
        <section
          ref="productList"
          class="min-h-0 flex-1 overflow-y-auto px-4 pt-3 pb-[82px] lg:mx-auto lg:w-full lg:max-w-6xl lg:overflow-visible lg:px-8 lg:pt-0 lg:pb-12"
        >
          <div class="mb-5 hidden items-center justify-between lg:flex">
            <h1 class="text-2xl font-bold">商品一覧</h1>
            <p class="text-sm text-[#627469]" aria-live="polite">{{ productTotal }}件の商品</p>
          </div>
          <div
            v-if="catalogueQuery.isPending.value"
            class="grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-5"
            role="status"
            aria-label="商品を読み込み中"
          >
            <UiCard v-for="item in 8" :key="item" class="overflow-hidden p-0 shadow-none">
              <UiSkeleton class="aspect-[1.35] w-full lg:aspect-square" />
              <div class="grid gap-3 p-3 lg:p-4">
                <UiSkeleton class="h-5 w-full" />
                <UiSkeleton class="h-4 w-2/3" />
                <UiSkeleton class="h-9 w-full" />
              </div>
            </UiCard>
          </div>
          <UiCard
            v-else-if="catalogueQuery.isError.value && !displayedProducts.length"
            class="p-6 text-center shadow-none lg:py-16"
            role="alert"
          >
            <p class="text-sm">商品を読み込めませんでした。もう一度お試しください。</p>
            <UiButton
              variant="outline"
              class="mt-4"
              :disabled="catalogueQuery.isFetching.value"
              @click="retryProducts"
            >
              再読み込み
            </UiButton>
          </UiCard>
          <UiCard
            v-else-if="!displayedProducts.length"
            class="p-6 text-center shadow-none lg:py-16"
            role="status"
          >
            <p class="text-sm">表示できる商品がありません。</p>
            <UiButton
              v-if="selectedCategory !== 'all'"
              variant="outline"
              class="mt-4"
              @click="selectedCategory = 'all'"
            >
              すべての商品を見る
            </UiButton>
          </UiCard>
          <div v-else class="grid grid-cols-2 content-start gap-3 lg:grid-cols-4 lg:gap-5">
            <UiCard
              v-for="product in displayedProducts"
              :key="product.id"
              class="flex flex-col overflow-hidden rounded-lg border border-[#dce5dc] bg-white p-0 shadow-none lg:rounded-xl"
            >
              <UiButton
                variant="ghost"
                class="block min-h-0 w-full whitespace-normal rounded-none border-0 bg-transparent p-0 text-left hover:bg-transparent"
                type="button"
                @click="openProduct(product.id)"
              >
                <div class="relative aspect-[1.35] lg:aspect-square overflow-hidden bg-[#e7eee8]">
                  <img
                    v-if="product.image_url"
                    :src="product.image_url"
                    :alt="product.name"
                    class="size-full object-cover"
                  />
                  <span v-else class="flex size-full items-center justify-center text-[#718075]">
                    <ImageOff class="size-8" aria-hidden="true" />
                    <span class="sr-only">商品画像なし</span>
                  </span>
                  <span
                    v-if="discountRate(product)"
                    class="absolute top-0 right-0 bg-[#df483f] px-2 py-1 text-xs font-extrabold text-white"
                  >
                    {{ discountRate(product) }}%OFF
                  </span>
                </div>
                <div class="px-2.5 pt-2 lg:px-4 lg:pt-4">
                  <h2
                    class="m-0 truncate text-sm font-bold text-[#29392f] lg:line-clamp-2 lg:min-h-12 lg:whitespace-normal lg:text-base lg:leading-6"
                  >
                    {{ product.name }}
                  </h2>
                  <p class="mt-1 mb-0 min-h-[18px] text-xs leading-snug lg:mt-2 lg:text-sm">
                    <span v-if="discountRate(product)" class="mr-1 text-[#819086] line-through">
                      {{ product.variants[0]?.price_yen.toLocaleString('ja-JP') }}円
                    </span>
                    <strong class="text-[#d94339]">{{ formatYen(productPrice(product)) }}</strong>
                  </p>
                </div>
              </UiButton>
              <div class="mt-auto px-2.5 pb-2 lg:px-4 lg:pb-4">
                <UiButton
                  class="mt-2 min-h-7 w-full rounded-[5px] border-0 bg-[#237f4b] px-1 text-xs font-bold text-white lg:mt-4 lg:min-h-11 lg:text-sm"
                  type="button"
                  :disabled="
                    !product.variants[0] ||
                    product.variants[0].stock_quantity < 1 ||
                    addCartItemMutation.isPending.value
                  "
                  @click="addToCart(product)"
                >
                  {{
                    !product.variants[0]
                      ? '購入できません'
                      : product.variants[0].stock_quantity < 1
                        ? '売り切れ'
                        : 'カートに追加'
                  }}
                </UiButton>
              </div>
            </UiCard>
          </div>
          <UiCard
            v-if="catalogueQuery.isError.value && displayedProducts.length"
            class="mt-6 p-4 text-center shadow-none"
            role="alert"
          >
            <p class="text-sm">追加の商品を読み込めませんでした。もう一度お試しください。</p>
            <UiButton
              variant="outline"
              class="mt-3"
              :disabled="catalogueQuery.isFetching.value"
              @click="retryProducts"
            >
              再読み込み
            </UiButton>
          </UiCard>
          <div
            v-if="
              hasMoreProducts && !catalogueQuery.isPending.value && !catalogueQuery.isError.value
            "
            ref="loadMoreTrigger"
            class="mt-6 flex items-center justify-center gap-2 py-3 text-sm text-[#627469]"
            role="status"
          >
            <LoaderCircle class="size-4 animate-spin" aria-hidden="true" />
            <span>商品を読み込み中</span>
          </div>
        </section>
        <div class="lg:hidden"><BuyerBottomNavigation active="home" /></div>
      </section>
    </div>
  </BuyerLayout>
</template>
