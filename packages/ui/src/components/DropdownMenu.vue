<script setup lang="ts">
import { DropdownMenuRoot, useForwardPropsEmits, type DropdownMenuRootProps, type DropdownMenuRootEmits } from 'reka-ui'
const props = defineProps<DropdownMenuRootProps>()
const emit = defineEmits<DropdownMenuRootEmits>()
const forwarded = useForwardPropsEmits(props, emit)
</script>
<template><DropdownMenuRoot v-bind="forwarded"><slot /></DropdownMenuRoot></template>