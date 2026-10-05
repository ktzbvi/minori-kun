<script setup lang="ts">
import { Leaf } from 'lucide-vue-next'
</script>

<template>
  <div class="flex items-center gap-2.5 min-[761px]:hidden">
    <span class="grid size-12 place-items-center rounded-full bg-[#e7f2eb] text-white" aria-hidden="true">
      <Leaf class="size-9 rounded-full bg-[#237f4b] p-2" :stroke-width="2.4" />
    </span>
    <span class="text-2xl font-extrabold tracking-[.04em] text-[#217848]">みのりくん</span>
  </div>
</template>
