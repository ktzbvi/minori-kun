<script setup lang="ts">
withDefaults(
  defineProps<{
    size?: 'header' | 'form' | 'compact' | 'large'
  }>(),
  { size: 'form' },
)
</script>

<template>
  <div
    class="flex items-center justify-center"
  >
    <img
      src="/images/minorikun-logo.png"
      alt="みのりくん"
      class="h-auto"
      :class="{
        'w-[87px]': size === 'header' || size === 'compact',
        'w-[109px]': size === 'form',
        'w-40': size === 'large',
      }"
    />
  </div>
</template>
