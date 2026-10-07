<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { DropdownMenuItem } from 'reka-ui'
import { cn } from '../lib/utils'
const props = defineProps<{ class?: HTMLAttributes['class']; disabled?: boolean }>()
</script>
<template><DropdownMenuItem :disabled="props.disabled" data-slot="dropdown-menu-item" :class="cn('relative flex cursor-default select-none items-center gap-2 rounded-lg px-3 py-2 text-sm outline-none focus:bg-[#e8f5ed] data-[disabled]:pointer-events-none data-[disabled]:opacity-50', props.class)"><slot /></DropdownMenuItem></template>