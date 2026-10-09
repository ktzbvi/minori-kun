<script setup lang="ts">
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'

withDefaults(
  defineProps<{
    active?: 'home' | 'category' | 'cart' | 'profile'
    fullWidth?: boolean
  }>(),
  { active: undefined, fullWidth: false },
)
</script>

<template>
  <main
    class="min-h-dvh bg-[#e4ebe6]"
    :class="fullWidth ? '' : 'sm:grid sm:place-items-center sm:p-6'"
  >
    <section
      :class="fullWidth ? 'max-w-none' : 'max-w-[375px] sm:h-[728px] sm:shadow-sm'"
      class="relative isolate flex h-dvh w-full flex-col overflow-hidden bg-[#f8faf6] text-[#26362c]"
    >
      <slot />
      <BuyerBottomNavigation :active="active" />
    </section>
  </main>
</template>
