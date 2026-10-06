<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
import { SelectIcon, SelectTrigger } from 'reka-ui'
import { cn } from '../lib/utils'

defineOptions({ inheritAttrs: false })
const props = defineProps<{ disabled?: boolean; class?: HTMLAttributes['class'] }>()
</script>

<template>
  <SelectTrigger
    v-bind="$attrs"
    :disabled="props.disabled"
    data-slot="select-trigger"
    :class="cn('flex h-11 w-full min-w-0 items-center justify-between gap-2 rounded-md border border-[#cad8ce] bg-white px-3 text-base text-[var(--color-text)] shadow-xs outline-none transition-[color,box-shadow] data-[placeholder]:text-[#98a69d] disabled:cursor-not-allowed disabled:opacity-50 md:text-sm focus-visible:border-[var(--color-primary)] focus-visible:ring-3 focus-visible:ring-[#237f4b]/15 aria-invalid:border-[#b33a2b] aria-invalid:ring-3 aria-invalid:ring-[#b33a2b]/10 [&>span]:truncate', props.class)"
  >
    <slot />
    <SelectIcon as-child><ChevronDown class="size-4 shrink-0 opacity-60" aria-hidden="true" /></SelectIcon>
  </SelectTrigger>
</template>
