<script setup lang="ts">
import { UiFormControl, UiFormItem, UiFormLabel, UiFormMessage, UiInput } from '@minorikun/ui'

defineProps<{ period: string; idPrefix: string; errors: Record<string, string | undefined> }>()
const year = defineModel<string>('year', { required: true })
const from = defineModel<string>('from', { required: true })
const to = defineModel<string>('to', { required: true })
</script>

<template>
  <UiFormItem v-if="period === 'year'" class="grid gap-2">
    <UiFormLabel :for="`${idPrefix}-year`">暦年</UiFormLabel>
    <UiFormControl><UiInput :id="`${idPrefix}-year`" v-model="year" type="number" min="2000" max="2100" class="h-11 rounded-xl" :aria-invalid="Boolean(errors.year)" :aria-describedby="errors.year ? `${idPrefix}-year-error` : undefined" /></UiFormControl>
    <UiFormMessage v-if="errors.year" :id="`${idPrefix}-year-error`" role="alert">{{ errors.year }}</UiFormMessage>
  </UiFormItem>
  <template v-if="period === 'custom'">
    <UiFormItem class="grid gap-2">
      <UiFormLabel :for="`${idPrefix}-from`">開始日</UiFormLabel>
      <UiFormControl><UiInput :id="`${idPrefix}-from`" v-model="from" type="date" class="h-11 rounded-xl" :aria-invalid="Boolean(errors.from)" :aria-describedby="errors.from ? `${idPrefix}-from-error` : undefined" /></UiFormControl>
      <UiFormMessage v-if="errors.from" :id="`${idPrefix}-from-error`" role="alert">{{ errors.from }}</UiFormMessage>
    </UiFormItem>
    <UiFormItem class="grid gap-2">
      <UiFormLabel :for="`${idPrefix}-to`">終了日</UiFormLabel>
      <UiFormControl><UiInput :id="`${idPrefix}-to`" v-model="to" type="date" class="h-11 rounded-xl" :min="from || undefined" :aria-invalid="Boolean(errors.to)" :aria-describedby="errors.to ? `${idPrefix}-to-error` : undefined" /></UiFormControl>
      <UiFormMessage v-if="errors.to" :id="`${idPrefix}-to-error`" role="alert">{{ errors.to }}</UiFormMessage>
    </UiFormItem>
  </template>
</template>
