<script setup lang="ts">
import { UiToaster } from '@minorikun/ui'
</script>

<template>
  <RouterView />
  <UiToaster position="top-center" rich-colors mobile />
</template>
