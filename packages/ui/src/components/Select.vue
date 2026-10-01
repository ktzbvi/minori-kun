<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '../lib/utils'

defineOptions({ inheritAttrs: false })

const props = defineProps<{ class?: HTMLAttributes['class'] }>()
const modelValue = defineModel<string>()
</script>

<template>
  <select
    v-model="modelValue"
    v-bind="$attrs"
    data-slot="select"
    :class="cn(
      'flex h-11 w-full rounded-md border border-[#cad8ce] bg-white px-3 text-base text-[var(--color-text)] shadow-xs outline-none transition-[color,box-shadow] disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
      'focus-visible:border-[var(--color-primary)] focus-visible:ring-3 focus-visible:ring-[#237f4b]/15',
      'aria-invalid:border-[#b33a2b] aria-invalid:ring-3 aria-invalid:ring-[#b33a2b]/10',
      props.class,
    )"
  >
    <slot />
  </select>
</template>
