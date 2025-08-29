<template>
  <div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
      <h2 class="text-xl font-semibold text-gray-900">Active Campaigns</h2>
    </div>

    <div v-if="campaigns.length === 0" class="p-6 text-center text-gray-500">
      <div class="mb-4">
        <svg class="w-16 h-16 mx-auto text-gray-300" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Active Campaigns</h3>
      <p class="text-gray-500">There are currently no active disaster relief campaigns in {{ stateName }}.</p>
    </div>

    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Campaign
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Disaster Type
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Severity
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Help Needed
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Created Date
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="campaign in campaigns" :key="campaign.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ campaign.campaign_name }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ campaign.disaster_type }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                :class="getSeverityColor(campaign.severity)"
              >
                {{ campaign.severity }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                :class="getStatusColor(campaign.status)"
              >
                {{ campaign.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <span
                  class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full mr-2"
                  :class="getHelpNeededColor(campaign.help_needed)"
                >
                  <span class="w-2 h-2 rounded-full mr-1" :class="getHelpNeededDotColor(campaign.help_needed)"></span>
                  {{ (campaign.help_needed || 'General aid').toUpperCase() }}
                </span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(campaign.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex space-x-2">
                <button
                  @click="viewVolunteerReports(campaign.id)"
                  class="text-blue-600 hover:text-blue-900 text-xs font-medium px-3 py-1 bg-blue-50 hover:bg-blue-100 rounded-md transition"
                >
                  View Reports
                </button>
                <button
                  v-if="isNgoStaff && campaign.status.toLowerCase() === 'active'"
                  @click="openReportModal(campaign)"
                  class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition"
                >
                  End Campaign
                </button>

              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal :show="showReportModal" title="Submit Donation Report" @close="closeReportModal">
      <DonationReportForm
        v-if="selectedCampaign"
        :campaignId="selectedCampaign.id"
        :onSubmit="handleDonationReportSubmit"
      />
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, ref } from 'vue'
import Modal from './Modal.vue'
import DonationReportForm from './DonationReportForm.vue'
import { api } from '../lib/api'

// Interfaces
interface Campaign {
  id: number
  campaign_name: string
  disaster_name: string
  disaster_type: string
  severity: string
  status: string
  help_needed: string | null
  created_at: string
}

interface DonationReportPayload {
  amount_received_financial: number
  amount_used_financial: number
  amount_received_medical: number
  amount_used_medical: number
  amount_received_resource: number
  amount_used_resource: number
  usage_breakdown: string
}

// Props
defineProps<{
  campaigns: Campaign[]
  stateName: string
  isNgoStaff?: boolean
}>()

// Emits
const emit = defineEmits<{
  viewVolunteerReports: [campaignId: number]
  toggleCampaignStatus: [campaign: Campaign]
  submitDonationReport: [payload: DonationReportPayload, campaign: Campaign]
}>()

// Modal state
const showReportModal = ref(false)
const selectedCampaign = ref<Campaign | null>(null)

// Utility functions
const getSeverityColor = (severity: string) => {
  switch (severity?.toLowerCase()) {
    case 'high':
      return 'bg-red-100 text-red-800'
    case 'medium':
      return 'bg-yellow-100 text-yellow-800'
    case 'low':
      return 'bg-green-100 text-green-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getStatusColor = (status: string) => {
  switch (status?.toLowerCase()) {
    case 'active':
      return 'bg-green-100 text-green-800'
    case 'inactive':
      return 'bg-gray-100 text-gray-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getHelpNeededColor = (helpNeeded: string | null) => {
  switch (helpNeeded?.toLowerCase()) {
    case 'high':
      return 'bg-red-50 text-red-700 border border-red-200'
    case 'medium':
      return 'bg-yellow-50 text-yellow-700 border border-yellow-200'
    case 'low':
      return 'bg-green-50 text-green-700 border border-green-200'
    default:
      return 'bg-gray-50 text-gray-700 border border-gray-200'
  }
}

const getHelpNeededDotColor = (helpNeeded: string | null) => {
  switch (helpNeeded?.toLowerCase()) {
    case 'high':
      return 'bg-red-400'
    case 'medium':
      return 'bg-yellow-400'
    case 'low':
      return 'bg-green-400'
    default:
      return 'bg-gray-400'
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const viewVolunteerReports = (campaignId: number) => {
  emit('viewVolunteerReports', campaignId)
}

const toggleCampaignStatus = (campaign: Campaign) => {
  emit('toggleCampaignStatus', campaign)
}

const openReportModal = (campaign: Campaign) => {
  selectedCampaign.value = campaign
  showReportModal.value = true
}

const closeReportModal = () => {
  showReportModal.value = false
  selectedCampaign.value = null
}

const handleDonationReportSubmit = async (payload: DonationReportPayload) => {
  if (selectedCampaign.value) {
    try {
      // Call backend to store donation report and end campaign
      await api.post('/donation-reports/store', {
        ...payload,
        campaign_id: selectedCampaign.value.id,
      })
      selectedCampaign.value.status = 'inactive'
      // Optionally, refresh campaigns list here
    } catch (err) {
      // Handle error (show notification, etc.)
    }
    closeReportModal()
  }
}
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
