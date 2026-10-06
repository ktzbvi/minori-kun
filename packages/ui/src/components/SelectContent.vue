<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { ChevronDown, ChevronUp } from 'lucide-vue-next'
import { SelectContent, SelectPortal, SelectScrollDownButton, SelectScrollUpButton, SelectViewport } from 'reka-ui'
import { cn } from '../lib/utils'

const props = defineProps<{ class?: HTMLAttributes['class'] }>()
</script>

<template>
  <SelectPortal>
    <SelectContent position="popper" :side-offset="4" data-slot="select-content" :class="cn('z-50 max-h-[var(--reka-select-content-available-height)] min-w-[var(--reka-select-trigger-width)] overflow-hidden rounded-xl border border-[var(--color-border)] bg-white text-[var(--color-text)] shadow-lg', props.class)">
      <SelectScrollUpButton class="flex items-center justify-center py-1"><ChevronUp class="size-4" aria-hidden="true" /></SelectScrollUpButton>
      <SelectViewport class="p-1"><slot /></SelectViewport>
      <SelectScrollDownButton class="flex items-center justify-center py-1"><ChevronDown class="size-4" aria-hidden="true" /></SelectScrollDownButton>
    </SelectContent>
  </SelectPortal>
</template>
