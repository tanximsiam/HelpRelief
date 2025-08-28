<script lang="ts" setup>
import { ref, watch, onMounted, computed } from 'vue'
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
  task?: { aid_type?: string }
  volunteer?: { name?: string }
  placeholder?: boolean
}

const props = defineProps<{ open: boolean; campaignId?: number | null }>()
defineEmits<{ (e:'close'): void }>()

const logs = ref<VolunteerTaskLog[]>([])
const loading = ref(false)
const campaignName = ref<string>('')
const tasks = ref<any[]>([])

async function fetchLogs() {
  loading.value = true
  try {
    const params: Record<string, any> = {}
    if (props.campaignId) params.campaign_id = props.campaignId
    const { data } = await api.get<VolunteerTaskLog[]>('/task-logs', { params })
    logs.value = data
  } catch (e) {
    console.error('Failed to fetch task logs', e)
  } finally {
    loading.value = false
  }
}

async function fetchTasks() {
  if (!props.campaignId) { tasks.value = []; return }
  try {
    const { data } = await api.get(`/campaigns/${props.campaignId}/tasks`)
    const arr = Array.isArray(data) ? data : (Array.isArray(data?.tasks) ? data.tasks : [])
    tasks.value = arr
  } catch (e) {
    console.error('Failed to fetch tasks for placeholders', e)
    tasks.value = []
  }
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

async function checkIn(log: VolunteerTaskLog) {
  try {
    const { data } = await api.post('/task-log/checkin', { task_id: log.task_id })
    if (data.log) {
      const idx = logs.value.findIndex(l => l.id === data.log.id)
      if (idx !== -1) logs.value[idx] = data.log
      else logs.value.unshift(data.log)
    }
  } catch (e) { console.error('Check-in failed', e) }
}

async function fetchCampaignName() {
  if (!props.campaignId) { campaignName.value = ''; return }
  try {
    const { data } = await api.get(`/campaigns/${props.campaignId}`)
    campaignName.value = data?.disaster?.name || data?.disaster_name || data?.name || `Campaign #${props.campaignId}`
  } catch { campaignName.value = `Campaign #${props.campaignId}` }
}

watch(() => props.open, (val) => { if (val) { fetchLogs(); fetchTasks(); fetchCampaignName() } })
watch(() => props.campaignId, (newId, oldId) => { if (props.open && newId && newId !== oldId) { fetchLogs(); fetchTasks(); fetchCampaignName() } })
onMounted(() => { if (props.open) { fetchLogs(); fetchTasks(); fetchCampaignName() } })

// Merge real logs with placeholder rows for tasks that have no log yet.
const displayLogs = computed<VolunteerTaskLog[]>(() => {
  if (!tasks.value.length) return logs.value
  const withLogTaskIds = new Set(logs.value.map(l => l.task_id))
  const placeholders: VolunteerTaskLog[] = tasks.value
    .filter(t => !withLogTaskIds.has(t.id))
    .map(t => ({
      id: -t.id, // negative sentinel
      task_id: t.id,
      status: 'unstarted',
      report: '-',
  task: { aid_type: t.aid_type || t.type || 'task' },
  volunteer: t.assigned_to_name ? { name: t.assigned_to_name } : undefined,
      placeholder: true
    }))
  return [...logs.value, ...placeholders].sort((a,b) => {
    // Sort: placeholders last, newest (by check_in) first
    if (a.placeholder && !b.placeholder) return 1
    if (!a.placeholder && b.placeholder) return -1
    const at = a.check_in || ''
    const bt = b.check_in || ''
    return at === bt ? 0 : (at > bt ? -1 : 1)
  })
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
/* inherit fade transition from Modal */
</style>

<template>
  <Modal :show="open" :title="'Task Logs' + (campaignId ? ' – ' + (campaignName || ('Campaign #' + campaignId)) : '')" maxWidth="max-w-4xl" zIndex="z-60" @close="$emit('close')">
    <div class="space-y-6">
      <div class="rounded-lg border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4 shadow-sm flex items-center justify-between">
        <p class="text-xs text-slate-600">
          <span v-if="!loading">Showing <span class="font-semibold text-slate-800">{{ logs.length }}</span> logs</span>
          <span v-else class="italic">Loading logs...</span>
        </p>
      </div>
      <div class="rounded-xl border border-slate-200 shadow-sm overflow-hidden bg-white">
        <VolunteerTaskLogTable
          :logs="displayLogs"
          :loading="loading"
          search=""
          @checkOut="checkOut"
          @checkIn="checkIn"
        />
      </div>
      <div class="flex justify-between items-center gap-4 pt-2 border-t border-slate-200">
        <p class="text-xs text-slate-400">Updated {{ new Date().toLocaleTimeString() }}</p>
        <button type="button" @click="$emit('close')" class="rounded-md bg-blue-600 px-6 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white">Close</button>
      </div>
    </div>
  </Modal>
</template>

