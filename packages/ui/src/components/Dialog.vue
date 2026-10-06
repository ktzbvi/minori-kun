<script setup lang="ts">
import { X } from 'lucide-vue-next'
import { useId } from 'vue'
import { cn } from '../lib/utils'
import { DialogClose, DialogContent, DialogDescription, DialogOverlay, DialogPortal, DialogRoot, DialogTitle } from 'reka-ui'

defineProps<{ open: boolean; title: string; description?: string; presentation?: 'dialog' | 'sheet' }>()
const emit = defineEmits<{ 'update:open': [value: boolean]; closeAutoFocus: [event: globalThis.Event] }>()
const descriptionId = useId()
</script>

<template>
  <DialogRoot :open="open" @update:open="emit('update:open', $event)">
    <DialogPortal>
      <DialogOverlay class="fixed inset-0 z-50 bg-black/40" />
      <DialogContent
        :class="cn('fixed z-50 flex max-h-[85dvh] flex-col gap-4 bg-white text-foreground shadow-lg', presentation === 'sheet'
          ? 'inset-x-0 bottom-0 h-3/4 rounded-t-3xl p-5 pb-[max(1.25rem,env(safe-area-inset-bottom))]'
          : 'top-1/2 left-1/2 w-[calc(100%_-_2rem)] max-w-xl -translate-x-1/2 -translate-y-1/2 rounded-xl border p-6')"
        :aria-describedby="description ? descriptionId : undefined"
        @close-auto-focus="emit('closeAutoFocus', $event)"
      >
        <div v-if="presentation === 'sheet'" class="mx-auto h-1 w-12 shrink-0 rounded-full bg-[#cfd6d2]" aria-hidden="true" />
        <DialogTitle class="pr-8 text-xl font-semibold">{{ title }}</DialogTitle>
        <DialogDescription v-if="description" :id="descriptionId" class="text-sm text-muted-foreground">{{ description }}</DialogDescription>
        <div :class="cn('min-h-0 overflow-y-auto', presentation === 'sheet' && 'flex-1')"><slot /></div>
        <div v-if="$slots.footer" class="flex justify-end gap-2"><slot name="footer" /></div>
        <DialogClose :class="cn('absolute right-4 rounded-sm opacity-70 hover:opacity-100 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none', presentation === 'sheet' ? 'top-7 grid size-11 place-items-center p-2' : 'top-4 p-1')" aria-label="閉じる">
          <X class="size-5" aria-hidden="true" />
        </DialogClose>
      </DialogContent>
    </DialogPortal>
  </DialogRoot>
</template>
