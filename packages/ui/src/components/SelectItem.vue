<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { Check } from 'lucide-vue-next'
import { SelectItem, SelectItemIndicator, SelectItemText } from 'reka-ui'
import { cn } from '../lib/utils'

const props = defineProps<{ value: string; disabled?: boolean; class?: HTMLAttributes['class'] }>()
</script>

<template>
  <SelectItem :value="value" :disabled="disabled" data-slot="select-item" :class="cn('relative flex min-h-10 w-full cursor-default select-none items-center rounded-lg py-2 pr-9 pl-3 text-sm outline-none data-[highlighted]:bg-[var(--color-background)] data-[highlighted]:text-[var(--color-primary)] data-[disabled]:pointer-events-none data-[disabled]:opacity-50', props.class)">
    <SelectItemText><slot /></SelectItemText>
    <SelectItemIndicator class="absolute right-3 flex size-4 items-center justify-center"><Check class="size-4" aria-hidden="true" /></SelectItemIndicator>
  </SelectItem>
</template>
