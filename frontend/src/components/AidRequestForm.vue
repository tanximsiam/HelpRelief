<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  show: boolean
}>()

interface AidRequestData {
  disasterContext: string
  aidType: string
  urgency: string
  description: string
}

const emit = defineEmits<{
  close: []
  submit: [data: AidRequestData]
}>()

const formData = ref<AidRequestData>({
  disasterContext: '',
  aidType: '',
  urgency: '',
  description: ''
})

const handleSubmit = () => {
  if (!formData.value.aidType || !formData.value.urgency) {
    alert('Please select both aid type and urgency level')
    return
  }

  if (!formData.value.description.trim()) {
    alert('Please provide a description for your aid request')
    return
  }

  emit('submit', formData.value)
  handleClose()
}

const handleClose = () => {
  // Reset form
  formData.value = {
    disasterContext: '',
    aidType: '',
    urgency: '',
    description: ''
  }
  emit('close')
}
</script>

<template>
  <!-- Overlay -->
  <div
    v-if="show"
    class="fixed inset-0 bg-white/30 backdrop-blur-sm flex items-center justify-center z-50"
    @click.self="handleClose"
  >
    <!-- Form Modal -->
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Aid Request</h2>
        <button
          @click="handleClose"
          class="text-gray-500 hover:text-gray-700 text-xl font-bold"
        >
          ×
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Disaster Context -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Disaster Context
          </label>
          <textarea
            v-model="formData.disasterContext"
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
            rows="3"
            placeholder="Describe the disaster context..."
          />
        </div>

        <!-- Type of Aid and Urgency Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Type of Aid -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">
              Type of Aid
            </label>
            <div class="space-y-3">
              <label class="flex items-center">
                <input
                  v-model="formData.aidType"
                  type="radio"
                  value="Financial"
                  class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                />
                <span class="ml-2 text-gray-700">Financial</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="formData.aidType"
                  type="radio"
                  value="Physical"
                  class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                />
                <span class="ml-2 text-gray-700">Physical</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="formData.aidType"
                  type="radio"
                  value="Medical"
                  class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                />
                <span class="ml-2 text-gray-700">Medical</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="formData.aidType"
                  type="radio"
                  value="Food"
                  class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                />
                <span class="ml-2 text-gray-700">Food</span>
              </label>
            </div>
          </div>

          <!-- Urgency -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">
              Urgency
            </label>
            <div class="space-y-3">
              <label class="flex items-center">
                <input
                  v-model="formData.urgency"
                  type="radio"
                  value="Low"
                  class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                />
                <span class="ml-2 text-gray-700">Low</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="formData.urgency"
                  type="radio"
                  value="Medium"
                  class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                />
                <span class="ml-2 text-gray-700">Medium</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="formData.urgency"
                  type="radio"
                  value="High"
                  class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                />
                <span class="ml-2 text-gray-700">High</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Description
          </label>
          <textarea
            v-model="formData.description"
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
            rows="4"
            placeholder="Provide request details"
          />
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
          <button
            type="submit"
            class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
          >
            Submit
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
/* Additional styles if needed */
</style>
