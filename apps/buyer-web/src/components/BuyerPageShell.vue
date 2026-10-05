<script setup lang="ts">
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'

withDefaults(
  defineProps<{
    active?: 'home' | 'category' | 'cart' | 'profile'
  }>(),
  { active: undefined },
)
</script>

<template>
  <main class="min-h-dvh bg-[#e4ebe6] sm:grid sm:place-items-center sm:p-6">
    <section
      class="relative isolate flex h-dvh w-full max-w-[375px] flex-col overflow-hidden bg-[#f8faf6] text-[#26362c] sm:h-[728px] sm:shadow-sm"
    >
      <slot />
      <BuyerBottomNavigation :active="active" />
    </section>
  </main>
</template>
