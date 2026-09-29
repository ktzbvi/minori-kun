<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '../lib/utils'

const props = defineProps<{ class?: HTMLAttributes['class'] }>()
</script>

<template>
  <section
    data-slot="card"
    :class="
      cn(
        'rounded-[14px] border border-[var(--color-border)] bg-[var(--color-surface)] p-6',
        props.class,
      )
    "
  >
    <slot />
  </section>
</template>
