<script setup lang="ts">
const search = ref('')
const filteredCampaigns = computed(() => {
  if (!search.value.trim()) return allCampaigns.value
  return allCampaigns.value.filter(c => c.campaign.name.toLowerCase().includes(search.value.toLowerCase()))
})
import { ref, onMounted, computed } from 'vue'
import {api} from '@/lib/api'
import DonationReportViewer from '@/components/DonationReportViewer.vue'

type Report = {
  id: number
  amount_received_financial: number
  amount_used_financial: number
  amount_received_medical: number
  amount_used_medical: number
  amount_received_resource: number
  amount_used_resource: number
  usage_breakdown: string
}

type CampaignWithReports = {
  campaign: {
    id: number
    name: string
    disaster: string
    ngo: string
  }
  reports: Report[]
}

const allCampaigns = ref<CampaignWithReports[]>([])
const selectedCampaignId = ref<number | null>(null)
const showViewer = ref(false)

const selectedReports = computed(() => {
  return allCampaigns.value.find(c => c.campaign.id === selectedCampaignId.value)?.reports || []
})

async function fetchCampaignsWithReports() {
  const res = await api.get('/donation-reports/campaigns')
  allCampaigns.value = res.data || []

  const first = allCampaigns.value[0]
  if (first) {
    selectedCampaignId.value = first.campaign.id
  }
}


onMounted(fetchCampaignsWithReports)
</script>

<template>
  <div>
    <label class="block mb-2 text-sm font-medium">Search Campaigns</label>
    <div class="mb-4">
      <input
        v-model="search"
        type="text"
        placeholder="Type to search..."
        class="w-full py-2 px-4 rounded border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-100"
        style="background-color: #fafafa;"
      />
    </div>

    <div class="overflow-y-auto" style="max-height: 50vh;">
      <div
        v-for="c in filteredCampaigns"
        :key="c.campaign.id"
        class="p-3 border-b cursor-pointer hover:bg-gray-100"
        :class="{
          'bg-slate-300 text-black': selectedCampaignId === c.campaign.id,
          'bg-white': selectedCampaignId !== c.campaign.id
        }"
        @click="selectedCampaignId = c.campaign.id; showViewer = true"
      >
  <div class="font-semibold">{{ c.campaign.name }}</div>
  <div class="text-xs text-gray-500">Disaster: {{ c.campaign.disaster }} | NGO: {{ c.campaign.ngo }}</div>
      </div>
    </div>

    <div v-if="showViewer && selectedReports.length" class="mt-6 relative">
      <button
        class="absolute right-2 top-2 text-gray-400 hover:text-red-500 text-xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow"
        @click="showViewer = false; selectedCampaignId = null"
        aria-label="Close"
      >
        &times;
      </button>
      <DonationReportViewer :reports="selectedReports" />
    </div>
  </div>
</template>
