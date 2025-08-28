<template>
  <div>
    <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ stateName }} Operations</h1>
            <p class="text-gray-600 mt-1">Detailed view of campaigns and activities</p>
          </div>
          <SecondaryButton
            @click="goBack"
            class="px-4 py-2"
          >
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Dashboard
          </SecondaryButton>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading state details...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        {{ error }}
      </div>

      <!-- Content -->
      <div v-else-if="stateData">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                  <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Campaigns</h3>
                <p class="text-2xl font-semibold text-gray-900">{{ stateData.statistics.total_campaigns }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                  <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM5 20a2 2 0 002-2 7 7 0 0110 0 2 2 0 002 2H5z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Active Volunteers</h3>
                <p class="text-2xl font-semibold text-gray-900">{{ stateData.statistics.active_volunteers }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-orange-100 rounded-md flex items-center justify-center">
                  <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Aid Distributed</h3>
                <p class="text-2xl font-semibold text-gray-900">{{ stateData.statistics.aid_distributed }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                  <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Beneficiaries Reached</h3>
                <p class="text-2xl font-semibold text-gray-900">{{ stateData.statistics.beneficiaries_reached }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Campaigns List -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Active Campaigns</h2>
          </div>

          <div v-if="stateData.campaigns.length === 0" class="p-6 text-center text-gray-500">
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
                <tr v-for="campaign in stateData.campaigns" :key="campaign.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ campaign.disaster_name }}</div>
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
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ campaign.help_needed || 'General aid' }}
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
                        @click="viewTaskLogs(campaign.id)"
                        class="text-green-600 hover:text-green-900 text-xs font-medium px-3 py-1 bg-green-50 hover:bg-green-100 rounded-md transition"
                      >
                        Task Logs
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Placeholder for Future Features -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Volunteer Activity</h3>
            <div class="text-center text-gray-500 py-8">
              <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p>Volunteer tracking coming soon</p>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Resource Distribution</h3>
            <div class="text-center text-gray-500 py-8">
              <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
              </svg>
              <p>Resource tracking coming soon</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>

    <!-- Volunteer Report Modal -->
    <VolunteerReportModal
      v-if="showVolunteerReportModal && selectedCampaign"
      :campaign="selectedCampaign"
      :show="showVolunteerReportModal"
      @close="closeVolunteerReportModal"
    />

    <!-- Task Logs Overlay -->
    <VolunteerTaskLogOverlay
      v-if="showTaskLogs && selectedCampaign"
      :open="showTaskLogs"
      :campaign-id="selectedCampaign.id"
      @close="closeTaskLogsModal"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '@/lib/api'
import SecondaryButton from '@/components/SecondaryButton.vue'
import VolunteerReportModal from '@/components/VolunteerReportModal.vue'
import VolunteerTaskLogOverlay from '@/components/VolunteerTaskLogOverlay.vue'

// Interfaces
interface Campaign {
  id: number
  disaster_name: string
  disaster_type: string
  severity: string
  status: string
  help_needed: string | null
  created_at: string
}

interface Statistics {
  total_campaigns: number
  active_volunteers: number
  aid_distributed: number
  beneficiaries_reached: number
}

interface StateData {
  state_name: string
  ngo_id: number
  campaigns: Campaign[]
  statistics: Statistics
}

// Router
const route = useRoute()
const router = useRouter()

// Reactive state
const stateName = ref(route.params.stateName as string)
const stateData = ref<StateData | null>(null)
const loading = ref(false)
const error = ref('')

// Modal state
const showVolunteerReportModal = ref(false)
const showTaskLogs = ref(false)
const selectedCampaign = ref<Campaign | null>(null)

// Fetch state details
const fetchStateDetails = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get(`/map/state/${stateName.value}`)
    stateData.value = response.data
  } catch (err: unknown) {
    if (err && typeof err === 'object' && 'response' in err) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Failed to load state details'
    } else {
      error.value = 'Failed to load state details'
    }
    console.error('Error fetching state details:', err)
  } finally {
    loading.value = false
  }
}

// Utility functions
const goBack = () => {
  router.push('/dashboard')
}

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
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'completed':
      return 'bg-blue-100 text-blue-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// New methods for campaign actions
const viewVolunteerReports = (campaignId: number) => {
  // Find the campaign by ID
  const campaign = stateData.value?.campaigns.find(c => c.id === campaignId)
  if (campaign) {
    selectedCampaign.value = campaign
    showVolunteerReportModal.value = true
  }
}

const viewTaskLogs = (campaignId: number) => {
  // Find the campaign by ID
  const campaign = stateData.value?.campaigns.find(c => c.id === campaignId)
  if (campaign) {
    selectedCampaign.value = campaign
    showTaskLogs.value = true
  }
}

// Function to close modals
const closeVolunteerReportModal = () => {
  showVolunteerReportModal.value = false
  selectedCampaign.value = null
}

const closeTaskLogsModal = () => {
  showTaskLogs.value = false
  selectedCampaign.value = null
}

// Lifecycle
onMounted(() => {
  fetchStateDetails()
})
</script>
