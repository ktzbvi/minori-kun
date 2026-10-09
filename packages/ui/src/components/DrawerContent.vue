<script setup lang="ts">
// Adapted from the shadcn-vue DrawerContent source; uses Reka's actual Drawer primitives.
import type { DrawerContentEmits, DrawerContentProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import {
  DrawerContent,
  DrawerHandle,
  DrawerOverlay,
  DrawerPortal,
  useForwardPropsEmits,
} from 'reka-ui'
import { cn } from '../lib/utils'
defineOptions({ inheritAttrs: false })
const props = defineProps<DrawerContentProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<DrawerContentEmits>()
const forwarded = useForwardPropsEmits(props, emits)
</script>
<template>
  <DrawerPortal>
    <DrawerOverlay data-slot="drawer-overlay" class="fixed inset-0 z-50 bg-black/35" />
    <DrawerContent
      data-slot="drawer-content"
      v-bind="{ ...$attrs, ...forwarded }"
      :class="
        cn(
          'fixed inset-x-0 bottom-0 z-50 flex h-auto max-h-[82dvh] flex-col rounded-t-[20px] bg-white px-4 pt-2 pb-[max(1rem,env(safe-area-inset-bottom))] text-[var(--color-text)] shadow-xl outline-none transform-[translate3d(var(--drawer-swipe-movement-x,0px),var(--drawer-swipe-movement-y,0px),0)] transition-transform duration-300 ease-out data-swiping:duration-0 data-swiping:select-none',
          props.class,
        )
      "
    >
      <DrawerHandle
        data-slot="drawer-handle"
        class="mx-auto mb-3 h-1 w-11 shrink-0 rounded-full bg-[#d2d9d4]"
      />
      <slot />
    </DrawerContent>
  </DrawerPortal>
</template>
