<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import {api} from '@/lib/api'
import DonationReportViewer from '@/components/DonationReportViewer.vue'

type Report = {
  id: number
  aid_type: string
  amount_received: number
  amount_used: number
  usage_breakdown: string
  reporting_period: string
  confirmed: boolean
}

type DisasterWithReports = {
  disaster: {
    id: number
    name: string
  }
  reports: Report[]
}

const allDisasters = ref<DisasterWithReports[]>([])
const selectedDisasterId = ref<number | null>(null)
const selectedReportId = ref<number | null>(null)
const showViewer = ref(false)

const reports = computed(() => {
  return allDisasters.value.find(d => d.disaster.id === selectedDisasterId.value)?.reports || []
})

const selectedReport = computed(() => {
  return reports.value.find(r => r.id === selectedReportId.value) || null
})

async function fetchDisastersWithReports() {
  const res = await api.get('/donation-reports/disasters')
  allDisasters.value = res.data || []

  const first = allDisasters.value[0]
  if (first) {
    selectedDisasterId.value = first.disaster.id
  }
}

function viewSelectedReport() {
  if (selectedReportId.value) showViewer.value = true
}

onMounted(fetchDisastersWithReports)
</script>

<template>
  <div>
    <label class="block mb-2 text-sm font-medium">Select Disaster</label>
    <select v-model="selectedDisasterId" class="select select-bordered w-full max-w-md">
      <option value="" disabled>Select one</option>
      <option v-for="d in allDisasters" :key="d.disaster.id" :value="d.disaster.id">
        {{ d.disaster.name }}
      </option>
    </select>

    <div v-if="reports.length > 0" class="mt-4">
      <p class="mb-2 font-medium">Available Reports:</p>
      <div class="flex flex-col gap-2">
        <div
          v-for="r in reports"
          :key="r.id"
          class="p-3 border rounded cursor-pointer hover:bg-gray-100"
          :class="{
            'bg-slate-300 hover:bg-slate-300 text-black': selectedReportId === r.id,
            'bg-white': selectedReportId !== r.id
          }"
          @click="selectedReportId = r.id"
        >
          <p><span class="font-semibold">Aid Type:</span> {{ r.aid_type }}</p>
          <p><span class="font-semibold">Period:</span> {{ r.reporting_period }}</p>
        </div>
      </div>

      <button
        class="btn btn-primary mt-4"
        :disabled="!selectedReportId"
        @click="viewSelectedReport"
      >
        View Report
      </button>
    </div>

    <DonationReportViewer v-if="showViewer && selectedReport" :reports="[selectedReport]" />
  </div>
</template>
