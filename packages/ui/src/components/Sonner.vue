<script setup lang="ts">
import { Toaster, type ToasterProps } from 'vue-sonner'
import 'vue-sonner/style.css'

interface Props {
  position?: ToasterProps['position']
  richColors?: boolean
  mobile?: boolean
}

withDefaults(defineProps<Props>(), {
  position: 'bottom-right',
  richColors: false,
  mobile: false,
})
</script>

<template>
  <Toaster
    :position="position"
    :rich-colors="richColors"
    :class="mobile ? 'ui-toaster--mobile' : ''"
    :offset="mobile ? { top: '76px', left: '16px', right: '16px' } : undefined"
    :mobile-offset="mobile ? { top: '76px', left: '16px', right: '16px' } : undefined"
    close-button
    :duration="3500"
  />
</template>

<style>
[data-sonner-toaster].ui-toaster--mobile {
  --width: min(343px, calc(100vw - 32px));
  z-index: 60;
}

[data-sonner-toaster].ui-toaster--mobile [data-sonner-toast] {
  border-radius: 8px;
  box-shadow: 0 8px 20px rgb(35 58 43 / 16%);
  font-size: 13px;
  font-weight: 600;
}
</style>
