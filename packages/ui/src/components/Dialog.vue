<script setup lang="ts">
import { X } from 'lucide-vue-next'
import { useId } from 'vue'
import { DialogClose, DialogContent, DialogDescription, DialogOverlay, DialogPortal, DialogRoot, DialogTitle } from 'reka-ui'

defineProps<{ open: boolean; title: string; description?: string }>()
const emit = defineEmits<{ 'update:open': [value: boolean] }>()
const descriptionId = useId()
</script>

<template>
  <DialogRoot :open="open" @update:open="emit('update:open', $event)">
    <DialogPortal>
      <DialogOverlay class="fixed inset-0 z-50 bg-black/40" />
      <DialogContent
        class="fixed top-1/2 left-1/2 z-50 flex max-h-[85dvh] w-[calc(100%_-_2rem)] max-w-xl -translate-x-1/2 -translate-y-1/2 flex-col gap-4 rounded-xl border bg-white p-6 text-foreground shadow-lg"
        :aria-describedby="description ? descriptionId : undefined"
      >
        <DialogTitle class="pr-8 text-xl font-semibold">{{ title }}</DialogTitle>
        <DialogDescription v-if="description" :id="descriptionId" class="text-sm text-muted-foreground">{{ description }}</DialogDescription>
        <div class="min-h-0 overflow-y-auto"><slot /></div>
        <div v-if="$slots.footer" class="flex justify-end gap-2"><slot name="footer" /></div>
        <DialogClose class="absolute top-4 right-4 rounded-sm p-1 opacity-70 hover:opacity-100 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none" aria-label="閉じる">
          <X class="size-5" aria-hidden="true" />
        </DialogClose>
      </DialogContent>
    </DialogPortal>
  </DialogRoot>
</template>
