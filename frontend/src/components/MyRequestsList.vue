<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { AxiosError} from 'axios'
import { api } from '@/lib/api'

type AidRequest = {
  id: number
  disaster_id: number
  requester_id: number
  location: string
  aid_type: 'financial' | 'medical' | 'resource'
  urgency: 'low' | 'medium' | 'high' | 'critical'
  description: string
  status: string
  task_id: number | null
  ngo_remarks: string | null
  created_at: string
  updated_at: string
}

const loading = ref(true)
const errorMsg = ref('')
const requests = ref<AidRequest[]>([])

async function load() {
  loading.value = true
  errorMsg.value = ''
  try {
    const { data } = await api.get<AidRequest[]>('/my-requests')
    requests.value = data
  } catch (err) {
    const e = err as AxiosError<{ message?: string }>
    errorMsg.value = e.response?.data?.message ?? 'Failed to load requests'
  } finally {
    loading.value = false
  }
}

async function verifyReceipt(id: number) {
  try {
    await api.post(`/aid-requests/${id}/verify`)
    const r = requests.value.find(x => x.id === id)
    if (r) r.status = 'verified'
  } catch (err) {
    const e = err as AxiosError<{ message?: string }>
    alert(e.response?.data?.message ?? 'Verification failed')
  }
}

onMounted(load)
</script>

<template>
  <div class="space-y-4">
    <div v-if="loading" class="text-sm text-gray-500">Loading…</div>
    <div v-else-if="errorMsg" class="text-sm text-red-600">{{ errorMsg }}</div>

    <ul v-else class="divide-y divide-gray-200 rounded-2xl border border-gray-200 overflow-hidden">
      <li v-for="r in requests" :key="r.id" class="p-4 flex items-start gap-4 hover:bg-gray-50">
        <div class="flex-1">
          <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm px-2 py-0.5 rounded-full bg-gray-100">{{ r.aid_type }}</span>
            <span class="text-xs px-2 py-0.5 rounded-full"
                  :class="{
                    'bg-red-100 text-red-700': r.urgency==='critical',
                    'bg-orange-100 text-orange-700': r.urgency==='high',
                    'bg-yellow-100 text-yellow-700': r.urgency==='medium',
                    'bg-green-100 text-green-700': r.urgency==='low'
                  }">{{ r.urgency }}</span>
            <span class="text-xs px-2 py-0.5 rounded-full"
                  :class="r.status==='verified' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700'">
              {{ r.status }}
            </span>
          </div>
          <p class="mt-1 text-sm font-medium text-gray-900">{{ r.location }}</p>
          <p class="mt-1 text-sm text-gray-600">{{ r.description }}</p>
          <p class="mt-1 text-xs text-gray-400">#{{ r.id }} · Disaster {{ r.disaster_id }}</p>
        </div>

        <div class="flex items-center">
          <button
            class="px-3 py-1.5 text-sm rounded-xl bg-black text-white disabled:opacity-40"
            :disabled="r.status==='verified'"
            @click="verifyReceipt(r.id)"
          >
            {{ r.status==='verified' ? 'Verified' : 'Verify receipt' }}
          </button>
        </div>
      </li>

      <li v-if="requests.length===0" class="p-6 text-sm text-gray-500">No requests found.</li>
    </ul>
  </div>
</template>
