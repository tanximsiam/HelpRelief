<template>
  <section class="w-full p-4 bg-yellow-50 border border-yellow-300 rounded mb-6">
    <h2 class="text-lg font-semibold mb-4">⚠️ Pending Disaster Alerts</h2>

    <div v-for="alert in alerts" :key="alert.id" class="mb-4 p-4 bg-white rounded shadow">
      <p><strong>{{ alert.title }}</strong> ({{ alert.disaster_type }})</p>
      <p class="text-sm">
        Divisions: {{ (Array.isArray(alert.divisions) ? alert.divisions : JSON.parse(alert.divisions || '[]')).join(', ') }}
      </p>

      <p class="text-sm">Reported at: {{ formatDate(alert.reported_at) }}</p>
      <a :href="alert.description" class="text-blue-600 underline text-sm" target="_blank">🔗 View Source</a>

      <div class="mt-3 flex gap-2">
        <button @click="rejectAlert(alert.id)" class="bg-red-100 text-red-700 px-3 py-1 rounded">
          Reject this Alert
        </button>
        <button @click="openModal(alert)" class="bg-blue-100 text-blue-700 px-3 py-1 rounded">
          Report Disaster
        </button>
      </div>
    </div>

    <ReportDisasterModal
      v-if="selectedAlert"
      :show="true"
      :alert="selectedAlert"
      @close="selectedAlert = null"
      @created="handleReported"
    />
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { api } from '@/lib/api'
import ReportDisasterModal from './ReportDisasterModal.vue'

interface Alert {
  id: number
  title: string
  disaster_type: string
  status: string
  description: string
  divisions: string[]
  reported_at: string
  confirmed: 'pending' | 'confirmed' | 'rejected'
}

defineProps<{ alerts: Alert[] }>()
const emit = defineEmits(['refresh'])


const selectedAlert = ref<Alert | null>(null)

function openModal(alert: Alert) {
  selectedAlert.value = alert
}

async function rejectAlert(id: number) {
  try {
    await api.post(`/alerts/${id}/reject`)
    emit('refresh')
  } catch (e) {
    console.warn('Failed to reject alert', e)
  }
}

async function handleReported() {
  if (selectedAlert.value) {
    await api.post(`/alerts/${selectedAlert.value.id}/confirm`)
    selectedAlert.value = null
    emit('refresh')
  }
}

function formatDate(datetime: string): string {
  return new Date(datetime).toLocaleString()
}
</script>
