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
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Help Needed
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Severity
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Disaster Type
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
              <span
                class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full"
                :class="getStatusColor(campaign.status)"
              >
                <span class="w-2 h-2 rounded-full mr-1" :class="getStatusDotColor(campaign.status)"></span>
                {{ campaign.status.toUpperCase() }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full"
                :class="getHelpNeededColor(campaign.help_needed)"
              >
                <span class="w-2 h-2 rounded-full mr-1" :class="getHelpNeededDotColor(campaign.help_needed)"></span>
                {{ (campaign.help_needed || 'General aid').toUpperCase() }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full"
                :class="getSeverityColor(campaign.severity)"
              >
                <span class="w-2 h-2 rounded-full mr-1" :class="getSeverityDotColor(campaign.severity)"></span>
                {{ campaign.severity.toUpperCase() }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ campaign.disaster_type || 'N/A' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(campaign.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex space-x-2">
                <PrimaryButton
                  @click="viewVolunteerReports(campaign.id)"
                  class="text-xs font-medium px-3 py-1"
                >
                  View Reports
                </PrimaryButton>
                <SecondaryButton
                  v-if="isNgoStaff && campaign.status.toLowerCase() === 'active'"
                  @click="toggleCampaignStatus(campaign)"
                  class="text-xs font-medium px-3 py-1"
                >
                  Complete
                </SecondaryButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import PrimaryButton from './PrimaryButton.vue'
import SecondaryButton from './SecondaryButton.vue'

// Interfaces
interface Campaign {
  id: number
  campaign_name: string
  disaster_name: string
  disaster_type: string
  disaster?: {
    disaster_type: string
  }
  severity: string
  status: string
  help_needed: string | null
  created_at: string
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
}>()

// Utility functions
const getSeverityColor = (severity: string) => {
  switch (severity?.toLowerCase()) {
    case 'high':
      return 'bg-red-50 text-red-700'
    case 'medium':
      return 'bg-yellow-50 text-yellow-700'
    case 'low':
      return 'bg-green-50 text-green-700'
    default:
      return 'bg-gray-50 text-gray-700'
  }
}

const getSeverityDotColor = (severity: string) => {
  switch (severity?.toLowerCase()) {
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

const getStatusColor = (status: string) => {
  switch (status?.toLowerCase()) {
    case 'active':
      return 'bg-green-50 text-green-700'
    case 'inactive':
      return 'bg-gray-50 text-gray-700'
    default:
      return 'bg-gray-50 text-gray-700'
  }
}

const getStatusDotColor = (status: string) => {
  switch (status?.toLowerCase()) {
    case 'active':
      return 'bg-green-400'
    case 'inactive':
      return 'bg-gray-400'
    default:
      return 'bg-gray-400'
  }
}

const getHelpNeededColor = (helpNeeded: string | null) => {
  switch (helpNeeded?.toLowerCase()) {
    case 'high':
      return 'bg-red-50 text-red-700'
    case 'medium':
      return 'bg-yellow-50 text-yellow-700'
    case 'low':
      return 'bg-green-50 text-green-700'
    default:
      return 'bg-blue-50 text-blue-700'
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
      return 'bg-blue-400'
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
</script>
