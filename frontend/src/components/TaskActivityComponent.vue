<template>
  <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-500">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900">Your Active Task</h3>
      <span 
        :class="getStatusColor(currentTask?.status)"
        class="px-3 py-1 text-xs font-medium rounded-full"
      >
        {{ currentTask?.status?.toUpperCase() || 'LOADING' }}
      </span>
    </div>

    <div v-if="loading" class="text-center text-gray-500 py-8">
      <div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-2"></div>
      Loading task details...
    </div>

    <div v-else-if="error" class="text-center text-red-500 py-8">
      <div class="mb-2">
        <svg class="w-12 h-12 mx-auto text-red-300" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
      </div>
      {{ error }}
    </div>

    <div v-else-if="currentTask" class="space-y-4">
      <!-- Task Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Disaster</label>
          <p class="text-gray-900 font-semibold">{{ currentTask.disaster || 'N/A' }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Aid Type</label>
          <p class="text-gray-900">{{ currentTask.aid_type || 'N/A' }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
          <p class="text-gray-900">{{ currentTask.location || 'N/A' }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Urgency</label>
          <span 
            :class="getUrgencyColor(currentTask.urgency)"
            class="px-2 py-1 text-xs font-medium rounded-full"
          >
            {{ currentTask.urgency?.toUpperCase() || 'N/A' }}
          </span>
        </div>
      </div>

      <!-- Time Info -->
      <div class="border-t pt-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
            <p class="text-gray-900">{{ formatDateTime(currentTask.start_time) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Task Status</label>
            <p class="text-gray-900">{{ currentTask.status || 'N/A' }}</p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="border-t pt-4 flex gap-3">
        <button
          v-if="currentTask.status === 'assigned'"
          @click="updateTaskStatus('accepted')"
          :disabled="updatingStatus"
          class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {{ updatingStatus ? 'Accepting...' : 'Accept Task' }}
        </button>
        <button
          v-if="currentTask.status === 'accepted'"
          @click="updateTaskStatus('ended')"
          :disabled="updatingStatus"
          class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {{ updatingStatus ? 'Completing...' : 'Complete Task' }}
        </button>
      </div>
    </div>

    <div v-else class="text-center text-gray-500 py-8">
      <div class="mb-2">
        <svg class="w-12 h-12 mx-auto text-gray-300" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      No active task assigned
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { api } from '../lib/api'
import { useAuth } from '../stores/auth'

interface Task {
  task_id: number
  disaster: string
  location: string
  aid_type: string
  urgency: string
  start_time: string
  status: string
}

const auth = useAuth()
const currentTask = ref<Task | null>(null)
const loading = ref(false)
const error = ref('')
const updatingStatus = ref(false)

const fetchCurrentTask = async () => {
  if (!auth.user?.id) return
  
  loading.value = true
  error.value = ''
  
  try {
    const response = await api.get('/volunteer/tasks')
    const tasks = response.data
    
    // Find the first assigned or accepted task
    const activeTask = tasks.find((task: Task) => 
      task.status === 'assigned' || task.status === 'accepted'
    )
    
    currentTask.value = activeTask || null
  } catch (err: any) {
    console.error('Failed to fetch current task:', err)
    error.value = 'Failed to load task details'
  } finally {
    loading.value = false
  }
}

const updateTaskStatus = async (newStatus: string) => {
  if (!currentTask.value) return
  
  updatingStatus.value = true
  
  try {
    await api.patch(`/volunteer/tasks/${currentTask.value.task_id}/status`, {
      status: newStatus
    })
    
    // Update local status
    currentTask.value.status = newStatus
    
    // If task is completed, remove it from view
    if (newStatus === 'ended') {
      setTimeout(() => {
        currentTask.value = null
      }, 2000) // Show success for 2 seconds
    }
  } catch (err: any) {
    console.error('Failed to update task status:', err)
    error.value = 'Failed to update task status'
  } finally {
    updatingStatus.value = false
  }
}

const getStatusColor = (status: string | undefined) => {
  switch (status?.toLowerCase()) {
    case 'assigned':
      return 'bg-yellow-100 text-yellow-800'
    case 'accepted':
      return 'bg-blue-100 text-blue-800'
    case 'ended':
      return 'bg-green-100 text-green-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getUrgencyColor = (urgency: string | undefined) => {
  switch (urgency?.toLowerCase()) {
    case 'high':
    case 'critical':
      return 'bg-red-100 text-red-800'
    case 'medium':
      return 'bg-yellow-100 text-yellow-800'
    case 'low':
      return 'bg-green-100 text-green-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatDateTime = (dateString: string | undefined) => {
  if (!dateString) return 'N/A'
  
  try {
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateString
  }
}

// Check if user should see this component
const shouldShowComponent = computed(() => {
  return auth.user?.volunteer && (currentTask.value || loading.value)
})

onMounted(() => {
  if (auth.user?.volunteer) {
    fetchCurrentTask()
  }
})

// Expose shouldShowComponent for parent component
defineExpose({
  shouldShowComponent
})
</script>
