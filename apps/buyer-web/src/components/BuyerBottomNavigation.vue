<script setup lang="ts">
import { Grid2X2, House, ShoppingCart, UserRound } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'

type BuyerNavigationItem = 'home' | 'category' | 'cart' | 'profile'

withDefaults(
  defineProps<{
    active?: BuyerNavigationItem
  }>(),
  {
    active: undefined,
  },
)

const router = useRouter()

function navigate(item: BuyerNavigationItem) {
  if (item === 'home') {
    void router.push({ name: 'home' })
    return
  }
  if (item === 'category') {
    void router.push({ name: 'categories' })
    return
  }
  if (item === 'cart') {
    void router.push({ name: 'cart' })
    return
  }

  void router.push({ name: 'my-page' })
}
</script>

<template>
  <nav
    class="absolute right-0 bottom-0 left-0 grid h-[64px] grid-cols-4 border-t border-[#e0e7e1] bg-white"
    aria-label="Buyer navigation"
  >
    <button
      class="grid place-items-center gap-0.5 border-0 bg-transparent"
      :class="active === 'home' ? 'text-[#237f4b]' : 'text-[#68786e]'"
      type="button"
      :aria-current="active === 'home' ? 'page' : undefined"
      @click="navigate('home')"
    >
      <House :size="19" />
      <span class="text-[10px]" :class="{ 'font-bold': active === 'home' }">
        ホーム
      </span>
    </button>
    <button
      class="grid place-items-center gap-0.5 border-0 bg-transparent"
      :class="active === 'category' ? 'text-[#237f4b]' : 'text-[#68786e]'"
      type="button"
      :aria-current="active === 'category' ? 'page' : undefined"
      @click="navigate('category')"
    >
      <Grid2X2 :size="18" />
      <span class="text-[10px]" :class="{ 'font-bold': active === 'category' }">
        カテゴリ
      </span>
    </button>
    <button
      class="grid place-items-center gap-0.5 border-0 bg-transparent"
      :class="active === 'cart' ? 'text-[#237f4b]' : 'text-[#68786e]'"
      type="button"
      :aria-current="active === 'cart' ? 'page' : undefined"
      @click="navigate('cart')"
    >
      <ShoppingCart :size="18" />
      <span class="text-[10px]" :class="{ 'font-bold': active === 'cart' }">
        カート
      </span>
    </button>
    <button
      class="grid place-items-center gap-0.5 border-0 bg-transparent"
      :class="active === 'profile' ? 'text-[#237f4b]' : 'text-[#68786e]'"
      type="button"
      :aria-current="active === 'profile' ? 'page' : undefined"
      @click="navigate('profile')"
    >
      <UserRound :size="18" />
      <span class="text-[10px]" :class="{ 'font-bold': active === 'profile' }">
        マイページ
      </span>
    </button>
  </nav>
</template>
