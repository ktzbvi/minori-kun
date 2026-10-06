<script setup lang="ts" generic="T extends string">
import { SelectRoot } from 'reka-ui'

defineProps<{ disabled?: boolean; name?: string; required?: boolean }>()
const modelValue = defineModel<T>()
</script>

<template>
  <SelectRoot :model-value="modelValue" :disabled="disabled" :name="name" :required="required" @update:model-value="modelValue = $event as T">
    <slot />
  </SelectRoot>
</template>
