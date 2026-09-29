<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '../lib/utils'

const props = defineProps<{ class?: HTMLAttributes['class'] }>()
</script>

<template>
  <p data-slot="form-message" :class="cn('text-sm font-medium text-[#b33a2b]', props.class)">
    <slot />
  </p>
</template>
