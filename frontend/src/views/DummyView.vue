<script setup lang="ts">
import { ref } from 'vue'
import AidRequestForm from '../components/AidRequestForm.vue'

const showAidRequestForm = ref(false)

interface AidRequestData {
  disasterContext: string
  aidType: string
  urgency: string
  description: string
}

const handleFormSubmit = (data: AidRequestData) => {
  console.log('Aid request submitted:', data)
  // Here you can send the data to your backend API
  // Example API call:
  // await fetch('/api/aid-requests', {
  //   method: 'POST',
  //   headers: { 'Content-Type': 'application/json' },
  //   body: JSON.stringify(data)
  // })
}
</script>

<template>
  <div class="dummy">
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-8">This is a dummy page</h1>

      <!-- Request Aid Button -->
      <div class="flex justify-center">
        <button
          @click="showAidRequestForm = true"
          class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors shadow-lg"
        >
          Request Aid
        </button>
      </div>
    </div>

    <!-- Aid Request Form Overlay -->
    <AidRequestForm
      :show="showAidRequestForm"
      @close="showAidRequestForm = false"
      @submit="handleFormSubmit"
    />
  </div>
</template>

<style scoped>
.dummy {
  min-height: 100vh;
  background-color: #f8fafc;
}

@media (min-width: 1024px) {
  .about {
    min-height: 100vh;
    display: flex;
    align-items: center;
  }
}
</style>
