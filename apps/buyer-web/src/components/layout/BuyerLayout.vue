<script setup lang="ts">
import BuyerDesktopNavigation from './BuyerDesktopNavigation.vue'
defineProps<{ active?: 'category' | 'search' | 'cart' | 'profile' }>()
</script>
<template>
  <main class="min-h-svh bg-[#f8faf6] text-[#26362c] lg:bg-[#f3f6f3]">
    <div class="relative flex min-h-svh w-full flex-col bg-[#f8faf6]">
      <BuyerDesktopNavigation :active="active" />
      <div v-if="$slots['mobile-header']" class="lg:hidden"><slot name="mobile-header" /></div>
      <slot />
      <div v-if="$slots['mobile-navigation']" class="lg:hidden">
        <slot name="mobile-navigation" />
      </div>
    </div>
  </main>
</template>
