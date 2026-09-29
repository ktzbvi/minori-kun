<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '../lib/utils'

defineOptions({ inheritAttrs: false })

const props = defineProps<{ class?: HTMLAttributes['class'] }>()
const modelValue = defineModel<string | number>()
</script>

<template>
  <input
    v-model="modelValue"
    v-bind="$attrs"
    data-slot="input"
    :class="
      cn(
        'flex h-9 w-full min-w-0 rounded-md border border-[#cad8ce] bg-white px-3 py-1 text-base text-[var(--color-text)] shadow-xs outline-none transition-[color,box-shadow] placeholder:text-[#98a69d] disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
        'focus-visible:border-[var(--color-primary)] focus-visible:ring-3 focus-visible:ring-[#237f4b]/15',
        'aria-invalid:border-[#b33a2b] aria-invalid:ring-3 aria-invalid:ring-[#b33a2b]/10',
        props.class,
      )
    "
  />
</template>
