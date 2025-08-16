<script setup lang="ts">
import { ref } from 'vue'
import VolunteerTaskLogOverlay from '@/components/VolunteerTaskLogOverlay.vue'
const open = ref(false)
</script>


<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Task Log Overlay Test</h1>
    <p class="text-gray-600 max-w-prose">Press the button below to load and display recent volunteer task logs from the backend in a modal overlay.</p>
    <button @click="open = true" class="bg-blue-600 text-white font-medium px-5 py-2 rounded-md hover:bg-blue-700">Show Task Logs</button>
  </div>
  <VolunteerTaskLogOverlay :open="open" @close="open=false" />
</template>


