<script setup lang="ts">
import { Leaf, Grid2X2, Search, UserRound } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { UiButton } from '@minorikun/ui'
import BuyerCartButton from './BuyerCartButton.vue'
defineProps<{ active?: 'category' | 'search' | 'cart' | 'profile' }>()
</script>
<template>
  <header class="hidden shrink-0 border-b border-[#e3e9e3] bg-white lg:block">
    <div class="mx-auto flex h-20 w-full max-w-6xl items-center justify-between gap-6 px-8">
      <RouterLink
        to="/"
        class="flex shrink-0 items-center gap-3 rounded-lg text-[#237f4b] focus-visible:outline-2 focus-visible:outline-offset-4"
      >
        <span class="grid size-11 place-items-center rounded-full bg-[#edf4ef]">
          <Leaf class="size-6" aria-hidden="true" />
        </span>
        <span class="text-2xl font-extrabold tracking-widest">みのりくん</span>
      </RouterLink>
      <nav class="flex items-center gap-1 xl:gap-3" aria-label="メインナビゲーション">
        <UiButton
          as-child
          variant="ghost"
          class="min-h-12 gap-2.5 rounded-xl px-4 text-base"
          :class="
            active === 'category'
              ? 'bg-[#edf4ef] font-bold text-[#237f4b] hover:bg-[#e3efe6]'
              : 'font-medium text-[#52675a] hover:bg-[#edf4ef] hover:text-[#237f4b]'
          "
        >
          <RouterLink
            :to="{ name: 'categories' }"
            :aria-current="active === 'category' ? 'page' : undefined"
          >
            <Grid2X2 class="size-5" aria-hidden="true" />
            カテゴリ
          </RouterLink>
        </UiButton>
        <UiButton
          as-child
          variant="ghost"
          class="min-h-12 gap-2.5 rounded-xl px-4 text-base"
          :class="
            active === 'search'
              ? 'bg-[#edf4ef] font-bold text-[#237f4b] hover:bg-[#e3efe6]'
              : 'font-medium text-[#52675a] hover:bg-[#edf4ef] hover:text-[#237f4b]'
          "
        >
          <RouterLink
            :to="{ name: 'search' }"
            :aria-current="active === 'search' ? 'page' : undefined"
          >
            <Search class="size-5" aria-hidden="true" />
            検索
          </RouterLink>
        </UiButton>
        <BuyerCartButton appearance="navigation" :active="active === 'cart'" />
        <span class="mx-1 h-6 w-px bg-[#dce5dc] xl:mx-2" aria-hidden="true"></span>
        <UiButton
          as-child
          variant="ghost"
          class="min-h-12 gap-2.5 rounded-xl px-4 text-base"
          :class="
            active === 'profile'
              ? 'bg-[#edf4ef] font-bold text-[#237f4b] hover:bg-[#e3efe6]'
              : 'font-medium text-[#52675a] hover:bg-[#edf4ef] hover:text-[#237f4b]'
          "
        >
          <RouterLink
            :to="{ name: 'my-page' }"
            :aria-current="active === 'profile' ? 'page' : undefined"
          >
            <UserRound class="size-5" aria-hidden="true" />
            マイページ
          </RouterLink>
        </UiButton>
      </nav>
    </div>
  </header>
</template>
