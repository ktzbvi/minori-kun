<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '../lib/utils'

const badgeVariants = cva('inline-flex items-center justify-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors', {
  variants: {
    variant: {
      default: 'border-transparent bg-primary text-primary-foreground',
      secondary: 'border-transparent bg-secondary text-secondary-foreground',
      destructive: 'border-transparent bg-destructive text-destructive-foreground',
      outline: 'text-foreground',
    },
  },
  defaultVariants: { variant: 'default' },
})
const props = defineProps<{ variant?: VariantProps<typeof badgeVariants>['variant']; class?: HTMLAttributes['class'] }>()
</script>

<template>
  <span data-slot="badge" :class="cn(badgeVariants({ variant: props.variant }), props.class)"><slot /></span>
</template>