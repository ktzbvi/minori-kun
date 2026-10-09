<script setup lang="ts">
import { ShoppingCart } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'
import { UiBadge, UiButton } from '@minorikun/ui'
import { useBuyerCartCount } from '@/composables/useBuyerCartCount'

withDefaults(defineProps<{ appearance?: 'icon' | 'navigation'; active?: boolean }>(), {
  appearance: 'icon',
  active: false,
})

const { cartItemCount } = useBuyerCartCount()
</script>

<template>
  <UiButton
    as-child
    variant="ghost"
    :class="
      appearance === 'icon'
        ? 'relative grid size-9 min-h-9 place-items-center rounded-full border-0 bg-transparent p-0 text-[#627469]'
        : [
            'min-h-12 gap-2.5 rounded-xl px-4 text-base',
            active
              ? 'bg-[#edf4ef] font-bold text-[#237f4b] hover:bg-[#e3efe6]'
              : 'font-medium text-[#52675a] hover:bg-[#edf4ef] hover:text-[#237f4b]',
          ]
    "
  >
    <RouterLink
      :to="{ name: 'cart' }"
      :aria-current="active ? 'page' : undefined"
      :aria-label="cartItemCount ? `カート（${cartItemCount}点）` : 'カート'"
    >
      <ShoppingCart :size="appearance === 'icon' ? 21 : 20" aria-hidden="true" />
      <span v-if="appearance === 'navigation'">カート</span>
      <UiBadge
        v-if="cartItemCount"
        class="h-4 min-w-4 border-0 bg-[#e25a3d] px-0.5 py-0 text-xs font-bold text-white"
        :class="appearance === 'icon' ? 'absolute top-0 right-0' : ''"
      >
        {{ cartItemCount }}
      </UiBadge>
    </RouterLink>
  </UiButton>
</template>
