<script setup lang="ts">
import { ref, watch } from 'vue'
import { api } from '@/lib/api'

interface AidRequest {
  id: number|string
  disaster_id: number|string
  requester?: { id: number|string; name: string }
  aid_type: string
  urgency: 'low'|'medium'|'high'|'critical'|string
  status: string
  description?: string
  created_at?: string
}

const props = defineProps<{
  show: boolean
  stateName: string | null
}>()
const emit = defineEmits<{ (e:'close'): void }>()

const loading = ref(false)
const error = ref<string|null>(null)
const requests = ref<AidRequest[]>([])

watch(() => ({ show: props.show, state: props.stateName }), async (val) => {
  if (!val.show || !val.state) return
  await fetchRequests(val.state as string)
}, { immediate: true })

async function fetchRequests(state: string){
  loading.value = true; error.value = null; requests.value = []
  try {
    const res = await api.get(`/map/aid-requests/state/${encodeURIComponent(state)}`)
    requests.value = res.data?.requests || []
  } catch (e:any) {
    error.value = e?.response?.data?.error || e.message || 'Failed to load aid requests'
  } finally { loading.value = false }
}

function urgencyBadge(u:string){
  const base = 'px-1.5 py-0.5 rounded text-xs font-medium capitalize'
  const map:Record<string,string>={
    low:'bg-gray-100 text-gray-600',
    medium:'bg-yellow-100 text-yellow-700',
    high:'bg-orange-100 text-orange-700',
    critical:'bg-red-100 text-red-700'
  }
  return base + ' ' + (map[u]||'bg-gray-100 text-gray-600')
}
function statusBadge(s:string){
  const base='px-1.5 py-0.5 rounded text-xs font-medium capitalize'
  const map:Record<string,string>={
    pending:'bg-blue-50 text-blue-600',
    assigned:'bg-indigo-50 text-indigo-600',
    completed:'bg-green-50 text-green-600',
    rejected:'bg-red-50 text-red-600'
  }
  return base + ' ' + (map[s]||'bg-gray-50 text-gray-500')
}
function format(ts?:string){
  if(!ts) return ''
  try { return new Date(ts).toLocaleString() } catch { return ts }
}
</script>

<template>
  <transition name="fade-scale">
    <div v-if="show && stateName" class="absolute inset-0 z-20 flex">
      <!-- backdrop inside map area -->
      <div class="absolute inset-0 bg-white/80 backdrop-blur-sm rounded border" />
      <div class="relative flex-1 flex flex-col p-4 overflow-hidden">
        <div class="flex items-start justify-between mb-3">
          <h3 class="text-lg font-semibold">{{ stateName }} Aid Requests</h3>
          <button @click="emit('close')" class="text-gray-500 hover:text-gray-700 text-xl leading-none">×</button>
        </div>
        <div v-if="loading" class="flex-1 flex items-center justify-center text-sm text-gray-500">Loading…</div>
        <div v-else-if="error" class="flex-1 overflow-auto">
          <div class="text-sm text-red-600">{{ error }}</div>
          <button @click="stateName && fetchRequests(stateName)" class="mt-3 text-xs text-blue-600 underline">Retry</button>
        </div>
        <div v-else class="flex-1 overflow-auto space-y-3 pr-1">
          <div v-if="!requests.length" class="text-sm text-gray-500">No aid requests.</div>
          <div v-for="r in requests" :key="r.id" class="bg-white/70 ring-1 ring-black/5 rounded p-3 shadow-sm">
            <div class="flex justify-between items-start gap-3">
              <div class="min-w-0">
                <p class="text-sm font-medium truncate">#{{ r.id }} • <span class="capitalize">{{ r.aid_type }}</span></p>
                <p v-if="r.description" class="text-xs text-gray-600 mt-1 line-clamp-3">{{ r.description }}</p>
                <p class="text-[11px] text-gray-400 mt-1">{{ r.requester?.name || 'Unknown' }} • {{ format(r.created_at) }}</p>
              </div>
              <div class="flex flex-col gap-1 items-end shrink-0">
                <span :class="urgencyBadge(r.urgency)">{{ r.urgency }}</span>
                <span :class="statusBadge(r.status)">{{ r.status }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
.fade-scale-enter-active,.fade-scale-leave-active { transition: all .15s ease; }
.fade-scale-enter-from,.fade-scale-leave-to { opacity:0; transform: scale(.96); }
</style>
