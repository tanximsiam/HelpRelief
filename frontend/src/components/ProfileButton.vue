<template>
  <span
    class="rounded-full px-3 py-1 text-2xl font-medium flex items-center gap-3"
    title="Account"
  >
  {{ userName }}
  <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor">
    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/>
  </svg>
  </span>
</template>

<script setup lang="ts">
defineProps<{
  userName: string
  variant?: 'primary' | 'secondary'
}>()
</script>
