<script setup lang="ts">
import { useId, type HTMLAttributes } from 'vue'
import { RadioGroupIndicator, RadioGroupItem, RadioGroupRoot } from 'reka-ui'
import { cn } from '../lib/utils'

defineOptions({ inheritAttrs: false })
const props = defineProps<{ options: readonly { value: string; label: string }[]; class?: HTMLAttributes['class'] }>()
const modelValue = defineModel<string>()
const id = useId()
</script>

<template>
  <RadioGroupRoot v-model="modelValue" v-bind="$attrs" :class="cn('grid gap-1', props.class)">
    <label v-for="option in options" :key="option.value" :for="`${id}-${option.value}`" class="flex min-h-11 cursor-pointer items-center gap-3 text-sm text-[var(--color-text)]">
      <RadioGroupItem :id="`${id}-${option.value}`" :value="option.value" class="grid size-4 shrink-0 place-items-center rounded-full border border-[#8b9990] text-[var(--color-primary)] outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)] focus-visible:ring-offset-2 data-[state=checked]:border-[var(--color-primary)] data-[state=checked]:bg-[var(--color-primary)]">
        <RadioGroupIndicator class="block size-2 rounded-full bg-current" />
      </RadioGroupItem>
      {{ option.label }}
    </label>
  </RadioGroupRoot>
</template>
