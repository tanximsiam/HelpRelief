<template>
  <RouterLink
    :to="to"
    class="text-2xl font-medium hover:underline underline-offset-4"
  >
    {{ label }}
  </RouterLink>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
defineProps<{
  label: string
  to: string
}>()
</script>
