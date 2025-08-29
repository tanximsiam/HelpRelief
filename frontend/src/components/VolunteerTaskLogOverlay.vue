<script lang="ts" setup>
import { ref, watch, onMounted, computed } from 'vue'
import { api } from '@/lib/api'
import Modal from '@/components/Modal.vue'
import VolunteerTaskLogTable from '@/components/VolunteerTaskLogTable.vue'

// Shape returned from /task-logs (one per started/ended task)
interface VolunteerTaskLog { id: number; task_id: number; status: string; report: string; check_in?: string; check_out?: string; volunteer?: { name?: string }; task?: { aid_type?: string } }
// Shape returned from /campaigns/:id/tasks (source of truth for task listing)
interface CampaignTask { id: number; status: string; urgency: string; aid_type: string; location: string; start_time: string; end_time?: string; assigned_to?: number; assigned_to_name?: string }

const props = defineProps<{ open: boolean; campaignId?: number | null }>()
defineEmits<{ (e:'close'): void }>()

const logs = ref<VolunteerTaskLog[]>([])
// Unified loading counter to avoid empty-state flicker
const loadingCounter = ref(0)
// Incremented each time we start a full refresh; results from older cycles are ignored
const fetchCycleId = ref(0)
const loading = computed(() => loadingCounter.value > 0)
const campaignName = ref<string>('')
const tasks = ref<CampaignTask[]>([])

async function fetchLogs(cycle?: number) {
  loadingCounter.value++
  try {
    const params: Record<string, any> = {}
    if (props.campaignId) params.campaign_id = props.campaignId
    const { data } = await api.get<VolunteerTaskLog[]>('/task-logs', { params })
    if (cycle === undefined || cycle === fetchCycleId.value) {
      logs.value = data
    }
  } catch (e) {
    console.error('Failed to fetch task logs', e)
  } finally {
    loadingCounter.value--
  }
}

async function fetchTasks(cycle?: number) {
  if (!props.campaignId) { tasks.value = []; return }
  loadingCounter.value++
  try {
    const { data } = await api.get(`/campaigns/${props.campaignId}/tasks`)
    if (cycle === undefined || cycle === fetchCycleId.value) {
      tasks.value = Array.isArray(data?.tasks) ? data.tasks : (Array.isArray(data) ? data : [])
    }
  } catch (e) { console.error('Failed to fetch tasks', e); tasks.value = [] }
  finally { loadingCounter.value-- }
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

function refreshAll() {
  const cycle = ++fetchCycleId.value
  // Clear current state immediately to avoid showing stale rows under new campaign title
  tasks.value = []
  logs.value = []
  fetchLogs(cycle)
  fetchTasks(cycle)
  fetchCampaignName()
}

watch(() => props.open, (val) => { if (val) refreshAll() })
watch(() => props.campaignId, (newId, oldId) => { if (props.open && newId && newId !== oldId) refreshAll() })
onMounted(() => { if (props.open) refreshAll() })

// Merge each campaign task with its log (if exists). Tasks without log show status from task table (assigned/pending)
const displayLogs = computed(() => {
  const logByTask = new Map<number, VolunteerTaskLog>()
  logs.value.forEach(l => logByTask.set(l.task_id, l))
  return tasks.value.map(t => {
    const log = logByTask.get(t.id)
    if (log) return log
    return {
      id: t.id, // use task id as key
      task_id: t.id,
      status: t.status ?? 'assigned',
      report: 'normal',
      task: { aid_type: t.aid_type },
      volunteer: t.assigned_to_name ? { name: t.assigned_to_name } : undefined,
    } as VolunteerTaskLog
  }).sort((a,b) => {
    const at = a.check_in || ''
    const bt = b.check_in || ''
    return at === bt ? 0 : (at > bt ? -1 : 1)
  })
})

// Search state and filtered list (by volunteer name, case-insensitive)
const search = ref('')
const filteredLogs = computed(() => {
  if (!search.value.trim()) return displayLogs.value
  const q = search.value.trim().toLowerCase()
  return displayLogs.value.filter(l => {
    const name = l.volunteer?.name || (l as any).assigned_to_name || ''
    return name.toLowerCase().includes(q)
  })
})

watch(() => props.open, (val) => { if (val) search.value = '' })
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
          <span v-if="!loading">
            <template v-if="search">
              Showing <span class="font-semibold text-slate-800">{{ filteredLogs.length }}</span> of {{ displayLogs.length }} tasks
            </template>
            <template v-else>
              Showing <span class="font-semibold text-slate-800">{{ displayLogs.length }}</span> tasks
            </template>
          </span>
          <span v-else class="italic">Loading...</span>
        </p>
        <div class="w-64">
          <input v-model="search" type="text" placeholder="Search volunteer..." class="w-full rounded-md border border-slate-300 px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
      </div>
      <div class="rounded-xl border border-slate-200 shadow-sm overflow-hidden bg-white">
        <VolunteerTaskLogTable
          :logs="filteredLogs"
          :loading="loading"
          :search="search"
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

