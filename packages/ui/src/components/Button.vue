<script setup lang="ts">
withDefaults(
  defineProps<{ variant?: 'primary' | 'outline' | 'ghost'; type?: 'button' | 'submit' }>(),
  { variant: 'primary', type: 'button' },
)
</script>

<template>
  <button :type="type" class="ui-button" :data-variant="variant"><slot /></button>
</template>
