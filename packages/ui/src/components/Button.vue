<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { Primitive, type PrimitiveProps } from 'reka-ui'
import { cva } from 'class-variance-authority'
import { cn } from '../lib/utils'

const props = withDefaults(
  defineProps<
    PrimitiveProps & {
      class?: HTMLAttributes['class']
      variant?: 'primary' | 'outline' | 'ghost'
      type?: 'button' | 'submit' | 'reset'
    }
  >(),
  { as: 'button', variant: 'primary', type: 'button' },
)

const buttonVariants = cva(
  'inline-flex min-h-11 items-center justify-center gap-2 whitespace-nowrap rounded-lg border px-4.5 text-sm font-bold transition-colors outline-none focus-visible:ring-3 focus-visible:ring-[var(--color-primary)]/30 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        primary:
          'border-[var(--color-primary)] bg-[var(--color-primary)] text-white hover:bg-[var(--color-primary-dark)]',
        outline:
          'border-[var(--color-primary)] bg-white text-[var(--color-primary)] hover:bg-[var(--color-background)]',
        ghost:
          'border-transparent bg-transparent text-[var(--color-primary)] hover:bg-[var(--color-background)]',
      },
    },
    defaultVariants: { variant: 'primary' },
  },
)
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    :type="type"
    data-slot="button"
    :data-variant="variant"
    :class="cn(buttonVariants({ variant }), props.class)"
  >
    <slot />
  </Primitive>
</template>
