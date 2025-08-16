<script lang="ts" setup>
import { ref, watch, computed } from 'vue'
import { api } from '@/lib/api'
import Modal from '@/components/Modal.vue'
import VolunteerTaskLogTable from '@/components/VolunteerTaskLogTable.vue'

interface VolunteerTaskLog {
  id: number
  task_id: number
  status: string
  report: string
  check_in?: string
  check_out?: string
  task?: { task_type?: string }
  volunteer?: { name?: string }
}

const props = defineProps<{ open: boolean }>()
const emit = defineEmits<{ (e:'close'): void }>()

const logs = ref<VolunteerTaskLog[]>([])
const loading = ref(false)
const search = ref('')
const newTaskId = ref<string>('')
const starting = ref(false)
const startError = ref<string | null>(null)
const startMessage = ref<string | null>(null)

async function fetchLogs() {
  loading.value = true
  try {
    const { data } = await api.get<VolunteerTaskLog[]>('/task-logs')
    logs.value = data
  } catch (e) {
    console.error('Failed to fetch task logs', e)
  } finally {
    loading.value = false
  }
}

function refresh() { fetchLogs() }

watch(() => props.open, (val) => { if (val) fetchLogs() })

const filteredLogs = computed(() => {
  if (!search.value) return logs.value
  const q = search.value.toLowerCase()
  return logs.value.filter(l =>
    (l.volunteer?.name || '').toLowerCase().includes(q) ||
    (l.task?.task_type || '').toLowerCase().includes(q) ||
    String(l.task_id).includes(q)
  )
})

function formatDate(dt?: string) {
  if (!dt) return '-'
  const d = new Date(dt)
  return d.toLocaleString()
}

async function startTask() {
  if (!newTaskId.value) return
  starting.value = true
  startError.value = null
  startMessage.value = null
  try {
    const taskIdNum = Number(newTaskId.value)
    if (isNaN(taskIdNum)) return
    const { data } = await api.post('/task-log/checkin', { task_id: taskIdNum })
    if (data.log) {
      const idx = logs.value.findIndex(l => l.id === data.log.id)
      if (idx !== -1) logs.value[idx] = data.log
      else logs.value.unshift(data.log)
      newTaskId.value = ''
      startMessage.value = data.message || 'Task started.'
    }
  } catch (e: any) {
    console.error('Failed to start task', e)
    startError.value = e?.response?.data?.message || 'Failed to start task'
  }
  finally { starting.value = false }
}

async function checkOut(log: VolunteerTaskLog) {
  try {
    const { data } = await api.post('/task-log/checkout', { task_id: log.task_id })
    if (data.log) {
      const idx = logs.value.findIndex(l => l.id === data.log.id)
      if (idx !== -1) logs.value[idx] = data.log
    }
  } catch (e) { console.error('Check-out failed', e) }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
/* inherit fade transition from Modal */
</style>

<template>
  <Modal :show="open" title="Volunteer Task Logs" maxWidth="max-w-6xl" @close="$emit('close')">
    <div class="space-y-6">
      <!-- Controls -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-wrap gap-3 items-center">
          <form @submit.prevent="startTask" class="flex items-stretch gap-2">
            <input v-model="newTaskId" placeholder="Task ID" class="w-32 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20" />
            <button type="submit" :disabled="starting || !newTaskId" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed">{{ starting ? 'Starting...' : 'Start Task' }}</button>
          </form>
          <input v-model="search" placeholder="Search task type / volunteer / id" class="w-72 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20" />
          <button @click="refresh" type="button" class="rounded-md border border-blue-600 px-4 py-2 text-sm font-semibold text-blue-600 transition hover:bg-blue-600 hover:text-white">Refresh</button>
        </div>
        <p class="text-sm text-slate-500" v-if="!loading">Showing {{ filteredLogs.length }} of {{ logs.length }} logs</p>
      </div>
      <div v-if="startError || startMessage" class="-mt-2">
        <p v-if="startError" class="text-sm text-red-600 font-medium">{{ startError }}</p>
        <p v-else-if="startMessage" class="text-sm text-green-600 font-medium">{{ startMessage }}</p>
      </div>

      <VolunteerTaskLogTable
        :logs="filteredLogs"
        :loading="loading"
        :search="search"
        @checkOut="checkOut"
      />

      <div class="flex justify-end">
        <button type="button" @click="$emit('close')" class="rounded-md bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white">Close</button>
      </div>
    </div>
  </Modal>
</template>

