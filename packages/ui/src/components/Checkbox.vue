<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { Check } from 'lucide-vue-next'
import { CheckboxIndicator, CheckboxRoot } from 'reka-ui'
import { cn } from '../lib/utils'

const props = defineProps<{ class?: HTMLAttributes['class']; disabled?: boolean }>()
const modelValue = defineModel<boolean>({ default: false })
</script>

<template>
  <CheckboxRoot
    v-model="modelValue"
    data-slot="checkbox"
    :disabled="disabled"
    :class="cn(
      'peer grid size-5 shrink-0 place-items-center rounded border border-[#2d8654] bg-white text-white outline-none transition-colors focus-visible:ring-3 focus-visible:ring-[#237f4b]/20 disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-[#237f4b]',
      props.class,
    )"
  >
    <CheckboxIndicator data-slot="checkbox-indicator">
      <Check class="size-3.5" :stroke-width="3" aria-hidden="true" />
    </CheckboxIndicator>
  </CheckboxRoot>
</template>
