<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { DropdownMenuContent, DropdownMenuPortal } from 'reka-ui'
import { cn } from '../lib/utils'
const props = defineProps<{ class?: HTMLAttributes['class'] }>()
</script>
<template>
  <DropdownMenuPortal><DropdownMenuContent align="end" :side-offset="8" data-slot="dropdown-menu-content" :class="cn('z-50 min-w-48 rounded-xl border border-[var(--color-border)] bg-white p-1 text-[var(--color-text)] shadow-lg', props.class)"><slot /></DropdownMenuContent></DropdownMenuPortal>
</template>