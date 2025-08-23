<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">NGO Reports</h1>
      <div>
        <button @click="fetchReports" class="px-3 py-1 bg-blue-600 text-white rounded">Refresh</button>
      </div>
    </div>

    <div v-if="loading" class="text-sm text-gray-500">Loading...</div>
    <div v-if="error" class="text-sm text-red-600">{{ error }}</div>

    <div v-if="!loading && reports.length === 0" class="text-sm text-gray-600">No reports available.</div>

    <div v-if="reports.length" class="overflow-x-auto bg-white rounded shadow">
      <table class="min-w-full text-left">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2">NGO</th>
            <th class="px-4 py-2">Aid Requested</th>
            <th class="px-4 py-2">Aid Supplied</th>
            <th class="px-4 py-2">Volunteers</th>
            <th class="px-4 py-2">Tasks</th>
            <th class="px-4 py-2">Disasters Engaged</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in reports" :key="r.ngo_id" class="border-t">
            <td class="px-4 py-3">
              <div class="font-medium">{{ r.ngo_name }}</div>
              <div class="text-sm text-gray-500">{{ r.email || r.phone }}</div>
            </td>
            <td class="px-4 py-3">{{ r.aid_requested ?? 0 }}</td>
            <td class="px-4 py-3">{{ r.aid_supplied ?? 0 }}</td>
            <td class="px-4 py-3">{{ r.volunteer_count ?? 0 }}</td>
            <td class="px-4 py-3">{{ r.tasks_assigned ?? 0 }}</td>
            <td class="px-4 py-3">{{ Array.isArray(r.disasters_engaged) ? r.disasters_engaged.length : (r.disasters_engaged ?? 0) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '@/lib/api'

const props = defineProps<{ myNgo?: boolean }>()

const loading = ref(false)
const error = ref<string | null>(null)
const reports = ref<Array<any>>([])

async function fetchReports() {
  loading.value = true
  error.value = null
  try {
    if (props.myNgo) {
      const { data } = await api.get('/report/my-ngo')
      if (data && data.data) {
        // single NGO report -> normalize to array
        reports.value = Array.isArray(data.data) ? data.data : [data.data]
      } else {
        reports.value = []
      }
    } else {
      const { data } = await api.get('/report/ngos')
      reports.value = Array.isArray(data.data) ? data.data : (data.data ? [data.data] : [])
    }
  } catch (err: any) {
    error.value = err?.response?.data?.message || err?.message || 'Failed to load reports'
    reports.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchReports())
</script>

<style scoped>
/* small responsive tweaks */
table th, table td { white-space: nowrap }
</style>
