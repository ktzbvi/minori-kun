<script setup lang="ts">
import { Check, ChevronRight } from 'lucide-vue-next'

defineProps<{ currentStep: 1 | 2 | 3 }>()

const steps = [
  { number: 1, label: 'メール入力' },
  { number: 2, label: 'メール確認' },
  { number: 3, label: '情報入力' },
] as const
</script>

<template>
  <nav aria-label="登録の進行状況">
    <ol class="flex items-center gap-1 text-xs font-medium min-[761px]:gap-3 min-[761px]:text-sm">
      <template v-for="(step, index) in steps" :key="step.number">
        <li
          class="flex items-center gap-0.5 whitespace-nowrap min-[761px]:gap-1"
          :class="step.number === currentStep ? 'font-bold text-[#25332b]' : step.number < currentStep ? 'text-[#258451]' : 'text-[#89988f]'"
          :aria-current="step.number === currentStep ? 'step' : undefined"
        >
          <Check v-if="step.number < currentStep" class="size-4" :stroke-width="2.5" aria-hidden="true" />
          <span v-else aria-hidden="true">{{ step.number === 1 ? '①' : step.number === 2 ? '②' : '③' }}</span>
          {{ step.label }}
        </li>
        <li v-if="index < steps.length - 1" aria-hidden="true">
          <ChevronRight class="size-3.5 text-[#8d9b92]" :stroke-width="2" />
        </li>
      </template>
    </ol>
  </nav>
</template>
