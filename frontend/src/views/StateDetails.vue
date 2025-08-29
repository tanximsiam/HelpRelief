<template>
  <div>
    <div class="bg-gray-50 p-6 h-screen overflow-auto">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ stateName }} Operations</h1>
            <p class="text-gray-600 mt-1">Detailed view of campaigns and activities</p>
          </div>
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
        </div>

        <!-- Campaigns List -->
        <StateCampaignList
          :campaigns="stateData.campaigns"
          :state-name="stateName"
          :is-ngo-staff="isNgoStaff"
          @view-volunteer-reports="viewVolunteerReports"

        />
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
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '../lib/api'
import VolunteerReportModal from '../components/VolunteerReportModal.vue'
import StateCampaignList from '../components/StateCampaignList.vue'

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

interface Statistics {
  total_campaigns: number
  active_volunteers: number
  aid_distributed: number
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
const isNgoStaff = ref(false)

// Modal state
const showVolunteerReportModal = ref(false)
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
// New methods for campaign actions
const viewVolunteerReports = (campaignId: number) => {
  // Find the campaign by ID
  const campaign = stateData.value?.campaigns.find(c => c.id === campaignId)
  if (campaign) {
    selectedCampaign.value = campaign
    showVolunteerReportModal.value = true
  }
}

// Function to close modals
const closeVolunteerReportModal = () => {
  showVolunteerReportModal.value = false
  selectedCampaign.value = null
}


// Function to check NGO staff status
const checkNgoStaffStatus = async () => {
  try {
    const staffRes = await api.get('/ngo-staff')
    const staffData = staffRes.data
    isNgoStaff.value = staffData.role === 'ngo_staff' && staffData.ngo_id
  } catch (error) {
    console.log('User is not NGO staff:', error)
    isNgoStaff.value = false
  }
}

// Lifecycle
onMounted(() => {
  checkNgoStaffStatus()
  fetchStateDetails()
})
</script>
