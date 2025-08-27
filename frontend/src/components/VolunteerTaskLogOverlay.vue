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

const props = defineProps<{ open: boolean; campaignId?: number | null }>()
const emit = defineEmits<{ (e:'close'): void }>()

const logs = ref<VolunteerTaskLog[]>([])
const loading = ref(false)
const search = ref('')
const newTaskId = ref<string>('')
const starting = ref(false)
const startError = ref<string | null>(null)
const startMessage = ref<string | null>(null)

const tasks = ref<any[]>([])
const selectedTaskId = ref<string>('')
const tasksLoading = ref(false)
const tasksError = ref<string | null>(null)

async function fetchLogs() {
  loading.value = true
  try {
    const params: Record<string, any> = {}
    if (props.campaignId) params.campaign_id = props.campaignId
  const { data } = await api.get<VolunteerTaskLog[]>('/task-logs', { params })
  logs.value = data
  console.debug('[TaskLogOverlay] Loaded logs', logs.value.length)
  } catch (e) {
    console.error('Failed to fetch task logs', e)
  } finally {
    loading.value = false
  }
}

async function fetchTasks() {
  if (!props.campaignId) return
  tasksLoading.value = true
  tasksError.value = null
  try {
  const { data } = await api.get(`/campaigns/${props.campaignId}/tasks`)
  const arr = Array.isArray(data) ? data : (Array.isArray(data?.tasks) ? data.tasks : [])
  tasks.value = arr
  console.debug('[TaskLogOverlay] Loaded tasks from API', tasks.value.length, 'raw:', data)
    if (tasks.value.length === 0) {
      // retry once after slight delay in case of race with seeding
      setTimeout(async () => {
        try {
          const retry = await api.get(`/campaigns/${props.campaignId}/tasks`)
          if (Array.isArray(retry.data) && retry.data.length) {
            tasks.value = retry.data
            console.log('Tasks loaded on retry')
          }
        } catch {}
      }, 800)
    }
  } catch (e:any) {
    console.error('Failed to fetch campaign tasks', e)
    tasksError.value = e?.response?.data?.error || 'Failed to load tasks.'
  } finally {
    tasksLoading.value = false
  }
}

function buildTasksFromLogsFallback() {
  if (tasks.value.length) return
  if (!logs.value.length) return
  const mapped = logs.value.map(l => ({
    id: l.task_id,
    task_type: l.task?.task_type || 'unknown',
    status: l.status,
    urgency: '-',
    aid_type: '-',
    assigned_to_name: l.volunteer?.name || '—'
  }))
  // unique by id
  const uniq: Record<number, any> = {}
  mapped.forEach(t => { uniq[t.id] = t })
  tasks.value = Object.values(uniq)
  if (tasks.value.length) {
    console.debug('[TaskLogOverlay] Fallback built tasks from logs', tasks.value.length)
  }
}

function refresh() { fetchLogs(); fetchTasks() }

watch(() => props.open, async (val) => { if (val) { await fetchLogs(); await fetchTasks(); buildTasksFromLogsFallback() } })
watch(() => props.campaignId, (newId, oldId) => {
  if (props.open && newId && newId !== oldId) {
    fetchTasks();
    fetchLogs().then(buildTasksFromLogsFallback)
  }
})

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
  const taskIdVal = selectedTaskId.value || newTaskId.value
  if (!taskIdVal) return
  starting.value = true
  startError.value = null
  startMessage.value = null
  try {
    const taskIdNum = Number(taskIdVal)
    if (isNaN(taskIdNum)) return
    const { data } = await api.post('/task-log/checkin', { task_id: taskIdNum })
    if (data.log) {
      const idx = logs.value.findIndex(l => l.id === data.log.id)
      if (idx !== -1) logs.value[idx] = data.log
      else logs.value.unshift(data.log)
      newTaskId.value = ''
      selectedTaskId.value = ''
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
  <Modal :show="open" :title="'Task Logs' + (campaignId ? ' – Campaign #' + campaignId : '')" maxWidth="max-w-4xl" zIndex="z-60" @close="$emit('close')">
    <div class="space-y-6">
      <!-- Controls Panel -->
      <div class="rounded-lg border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
          <div class="grid flex-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="flex flex-col">
              <label class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Campaign Tasks</label>
              <select v-model="selectedTaskId" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 disabled:opacity-50" :disabled="tasksLoading || tasksError">
                <option value="">{{ tasksLoading ? 'Loading tasks...' : (tasksError ? 'Failed to load tasks' : (tasks.length ? 'Select a task...' : 'No tasks found')) }}</option>
                <option v-for="t in tasks" :key="t.id" :value="t.id">#{{ t.id }} · {{ t.task_type }} · {{ t.status }} · {{ t.urgency || '-' }} · {{ t.assigned_to_name || 'Unassigned' }}</option>
              </select>
              <p v-if="tasksError" class="mt-1 text-[11px] text-red-600 font-medium">{{ tasksError }}</p>
            </div>
            <div class="flex flex-col">
              <label class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Manual Task ID</label>
              <input v-model="newTaskId" placeholder="Task ID" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20" />
            </div>
            <div class="flex flex-col">
              <label class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Search Logs</label>
              <input v-model="search" placeholder="Search volunteer / type / id" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20" />
            </div>
          </div>
          <div class="flex gap-3">
            <button @click="startTask" :disabled="starting || (!newTaskId && !selectedTaskId)" class="inline-flex items-center gap-1 rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed">
              <span v-if="starting" class="animate-spin h-4 w-4 rounded-full border-2 border-white border-t-transparent"></span>
              <span>{{ starting ? 'Starting...' : 'Check In' }}</span>
            </button>
            <button @click="refresh" type="button" class="rounded-md bg-white px-5 py-2 text-sm font-semibold text-blue-600 shadow-sm ring-1 ring-blue-600/50 transition hover:bg-blue-50">Refresh</button>
          </div>
        </div>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
          <p v-if="!loading">Showing <span class="font-semibold text-slate-700">{{ filteredLogs.length }}</span> of {{ logs.length }} logs</p>
          <p v-else class="italic">Loading logs...</p>
        </div>
      </div>
      <div v-if="startError || startMessage" class="-mt-2">
        <p v-if="startError" class="text-sm text-red-600 font-medium">{{ startError }}</p>
        <p v-else-if="startMessage" class="text-sm text-green-600 font-medium">{{ startMessage }}</p>
      </div>

      <div class="rounded-xl border border-slate-200 shadow-sm overflow-hidden bg-white">
        <VolunteerTaskLogTable
          :logs="filteredLogs"
          :loading="loading"
          :search="search"
          @checkOut="checkOut"
        />
      </div>

      <div class="flex justify-between items-center gap-4 pt-2 border-t border-slate-200">
        <p class="text-xs text-slate-400">Updated {{ new Date().toLocaleTimeString() }}</p>
        <div class="flex gap-3">
          <button type="button" @click="refresh" class="rounded-md border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">Reload</button>
          <button type="button" @click="$emit('close')" class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white">Close</button>
        </div>
      </div>
    </div>
  </Modal>
</template>

