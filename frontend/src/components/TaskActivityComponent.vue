<template>
  <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-500">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900">Volunteer Activity</h3>
      <span
        :class="getStatusColor()"
        class="px-3 py-1 text-xs font-medium rounded-full"
      >
        {{ getStatusText() }}
      </span>
    </div>

    <div v-if="loading" class="text-center text-gray-500 py-8">
      <div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-2"></div>
      Loading volunteer status...
    </div>

    <div v-else-if="error" class="text-center text-red-500 py-8">
      <div class="mb-2">
        <svg class="w-12 h-12 mx-auto text-red-300" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
      </div>
      {{ error }}
    </div>

    <!-- Not registered as volunteer -->
    <div v-else-if="!hasVolunteerRegistrations" class="text-center py-8">
      <div class="mb-4">
        <svg class="w-16 h-16 mx-auto text-blue-300" fill="currentColor" viewBox="0 0 20 20">
          <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
        </svg>
      </div>
      <h4 class="text-lg font-medium text-gray-900 mb-2">Become a Volunteer</h4>
      <p class="text-gray-600 mb-4">Register as a volunteer under a campaign to help communities in need.</p>
      <button
        @click="$emit('openVolunteerRegistration')"
        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors"
      >
        Register as Volunteer
      </button>
    </div>

    <!-- Waiting for task assignment -->
    <div v-else-if="!currentTask && !completedTask && hasVolunteerRegistrations" class="space-y-6">
      <div class="text-center mb-6">
        <svg class="w-16 h-16 mx-auto text-yellow-400 mb-4" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
        </svg>
        <h4 class="text-2xl font-bold text-gray-900 mb-2">Waiting for Task Assignment</h4>
        <p class="text-gray-600">You'll receive notification once a task is assigned</p>
      </div>

      <!-- Show the campaign user registered under -->
      <div v-if="getPrimaryCampaign()" class="space-y-4">
        <h5 class="text-lg font-semibold text-gray-900 text-center mb-4">
          Your Volunteer Registration
        </h5>

        <!-- Show the single campaign they registered under -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
          <div class="mb-4">
            <h6 class="text-xl font-bold text-gray-900 mb-1">{{ getPrimaryCampaign().disaster_name }} Campaign</h6>
            <p class="text-lg text-gray-700 font-medium">📍 {{ getPrimaryCampaign().disaster_location }}</p>
          </div>

          <div class="bg-white rounded-lg p-4 border border-blue-100">
            <h6 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wider">NGO Contact Information</h6>
            <div class="space-y-2">
              <p class="text-lg font-bold text-gray-900">{{ getPrimaryCampaign().ngo_name }}</p>
              <div v-if="getPrimaryCampaign().ngo_phone" class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                </svg>
                <span class="text-sm text-gray-700">{{ getPrimaryCampaign().ngo_phone }}</span>
              </div>
              <div v-if="getPrimaryCampaign().ngo_email" class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                  <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                </svg>
                <span class="text-sm text-gray-700">{{ getPrimaryCampaign().ngo_email }}</span>
              </div>
              <div v-if="!getPrimaryCampaign().ngo_phone && !getPrimaryCampaign().ngo_email" class="text-center text-gray-500 text-sm">
                Contact information not available
              </div>
            </div>
          </div>

          <div class="mt-4 text-center">
            <p class="text-sm text-gray-600 bg-yellow-50 px-4 py-2 rounded-lg border border-yellow-200">
              <strong>Waiting for task assignment</strong> from this campaign
            </p>
          </div>
        </div>

        <!-- Add History Button for volunteers who have completed tasks -->
        <div class="text-center mt-6" v-if="hasCompletedTasks">
          <button
            @click="showContributionHistory = true"
            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg transition-colors"
          >
            View Contribution History
          </button>
        </div>

        <!-- Resign button at bottom as non-focused option -->
        <div class="text-center mt-8 pt-4 border-t border-gray-200">
          <button
            @click="resignAsVolunteer"
            :disabled="resigning"
            class="text-sm text-gray-500 hover:text-red-600 disabled:text-gray-400 underline transition-colors"
          >
            {{ resigning ? 'Resigning...' : 'Resign as Volunteer' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Active task assigned -->
    <div v-else-if="currentTask" class="space-y-6">
      <!-- Task Assignment Details -->
      <div class="bg-white rounded-xl border-2 border-blue-200 shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
          <h4 class="text-xl font-bold text-white">Task Assignment</h4>
        </div>
        <div class="p-6 space-y-6">
          <!-- Primary Task Info -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
              <div>
                <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ currentTask.campaign_name || currentTask.disaster || 'Campaign' }}</h5>
                <p class="text-gray-600 text-base">{{ currentTask.description || 'No description available' }}</p>
              </div>

              <div class="flex items-center space-x-4 flex-wrap">
                <div class="flex items-center">
                  <svg class="w-5 h-5 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                  </svg>
                  <span class="text-lg font-medium text-gray-900">{{ currentTask.location || 'Location not specified' }}</span>
                </div>
              </div>

              <!-- Task Details Row -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                  <span class="text-sm font-medium text-gray-600">Aid Type:</span>
                  <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ currentTask.aid_type?.toUpperCase() || 'N/A' }}
                  </span>
                </div>

                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                  <span class="text-sm font-medium text-gray-600">Priority:</span>
                  <span
                    :class="getUrgencyColor(currentTask.urgency)"
                    class="px-3 py-1 text-sm font-bold rounded-full"
                  >
                    {{ currentTask.urgency?.toUpperCase() || 'NORMAL' }}
                  </span>
                </div>

                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                  <span class="text-sm font-medium text-gray-600">Status:</span>
                  <span
                    :class="getCheckInStatusColor(currentTaskLog?.status)"
                    class="px-3 py-1 text-sm font-bold rounded-full"
                  >
                    {{ getCheckInStatusText(currentTaskLog?.status) }}
                  </span>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <!-- Timing Information -->
              <div class="bg-gray-50 rounded-lg p-4">
                <h6 class="text-sm font-semibold text-gray-700 mb-3">Schedule</h6>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Start Time:</span>
                    <span class="font-medium">{{ formatDateTime(currentTask.start_time) }}</span>
                  </div>
                  <div v-if="currentTask.end_time" class="flex justify-between">
                    <span class="text-gray-600">End Time:</span>
                    <span class="font-medium">{{ formatDateTime(currentTask.end_time) }}</span>
                  </div>
                  <div v-if="currentTaskLog?.check_in" class="flex justify-between">
                    <span class="text-gray-600">Checked In:</span>
                    <span class="font-medium text-green-600">{{ formatDateTime(currentTaskLog.check_in) }}</span>
                  </div>
                  <div v-if="currentTaskLog?.check_out" class="flex justify-between">
                    <span class="text-gray-600">Checked Out:</span>
                    <span class="font-medium text-green-600">{{ formatDateTime(currentTaskLog.check_out) }}</span>
                  </div>
                </div>
              </div>

              <!-- NGO Contact Information (smaller, bottom right) -->
              <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                <h6 class="text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wider">NGO Contact</h6>
                <div v-if="getCurrentCampaign()" class="space-y-3">
                  <div class="text-center border-b border-blue-200 pb-2">
                    <p class="text-lg font-bold text-gray-900">{{ getCurrentCampaign()?.ngo_name }}</p>
                  </div>
                  <div class="space-y-2">
                    <div class="flex items-center justify-center" v-if="getCurrentCampaign()?.ngo_phone">
                      <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                      </svg>
                      <span class="text-sm font-medium text-gray-700">{{ getCurrentCampaign()?.ngo_phone }}</span>
                    </div>
                    <div class="flex items-center justify-center" v-if="getCurrentCampaign()?.ngo_email">
                      <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                      </svg>
                      <span class="text-sm font-medium text-gray-700">{{ getCurrentCampaign()?.ngo_email }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Task completed - Thank you message -->
    <div v-else-if="completedTask" class="text-center py-8 space-y-6">
      <div class="mb-6">
        <svg class="w-20 h-20 mx-auto text-green-500 mb-4" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <h4 class="text-3xl font-bold text-gray-900 mb-3">Thank You for Your Contribution!</h4>
        <p class="text-lg text-gray-600 max-w-md mx-auto">Your volunteer work has made a real difference in helping those in need during this crisis.</p>
      </div>

      <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200 max-w-md mx-auto">
        <h5 class="text-lg font-semibold text-gray-900 mb-2">Recent Contribution</h5>
        <p class="text-gray-700 font-medium">{{ completedTask.campaign_name || completedTask.disaster }}</p>
        <p class="text-sm text-gray-600">{{ completedTask.aid_type?.toUpperCase() || 'VOLUNTEER WORK' }}</p>
      </div>

      <div class="space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <button
            @click="showContributionHistory = true"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg transition-colors shadow-lg"
          >
            View Contribution History
          </button>
          <button
            @click="resignAsVolunteer"
            :disabled="resigning"
            class="bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white font-semibold px-8 py-3 rounded-lg transition-colors shadow-lg"
          >
            {{ resigning ? 'Resigning...' : 'Resign as Volunteer' }}
          </button>
        </div>
        <p class="text-sm text-gray-600">
          You are still registered as a volunteer and may receive new task assignments.
        </p>
      </div>
    </div>

    <!-- Contribution History Modal -->
    <ContributionHistoryModal
      :isOpen="showContributionHistory"
      :contributions="contributionHistory"
      :loading="loadingHistory"
      @close="showContributionHistory = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { api } from '../lib/api'
import { useAuth } from '../stores/auth'
import ContributionHistoryModal from './ContributionHistoryModal.vue'

interface Task {
  task_id: number
  disaster: string
  campaign_name?: string
  location: string
  aid_type: string
  urgency: string
  start_time: string
  end_time: string
  description: string
  status: string
}

interface Campaign {
  id: number
  disaster_name: string
  disaster_location: string
  ngo_id: number
  ngo_name: string
  ngo_phone?: string
  ngo_email?: string
  status: string
}

interface TaskLog {
  id: number
  task_id: number
  status: string
  check_in: string | null
  check_out: string | null
}

interface ContributionHistory {
  id: number
  campaign_name: string
  aid_type: string
  check_in: string
  check_out: string
}

const emit = defineEmits<{
  openVolunteerRegistration: []
}>()

const auth = useAuth()
const currentTask = ref<Task | null>(null)
const currentTaskLog = ref<TaskLog | null>(null)
const registeredCampaigns = ref<Campaign[]>([])
const completedTask = ref<Task | null>(null)
const contributionHistory = ref<ContributionHistory[]>([])
const loading = ref(false)
const loadingHistory = ref(false)
const error = ref('')
const hasVolunteerRegistrations = ref(false)
const showContributionHistory = ref(false)
const resigning = ref(false)

const fetchVolunteerStatus = async () => {
  if (!auth.user?.id) return

  loading.value = true
  error.value = ''

  try {
    // First refresh user data to get latest volunteer status
    await auth.fetchUser()

    // Check if user has volunteer registrations
    const volunteerResponse = await api.get('/campaigns/volunteer')
    hasVolunteerRegistrations.value = true

    // Process campaigns and fetch NGO details
    const campaignPromises = volunteerResponse.data
      .filter((campaign: any) => campaign.status === 'active') // Only show active campaigns
      .map(async (campaign: any) => {
        let ngoPhone = campaign.ngo?.phone || null
        let ngoEmail = campaign.ngo?.email || null

        // Always try to fetch NGO details from the dedicated endpoint
        if (campaign.ngo_id) {
          try {
            console.log(`Fetching NGO details for ID: ${campaign.ngo_id}`)
            const ngoResponse = await api.get(`/ngo/${campaign.ngo_id}`)
            const ngoData = ngoResponse.data
            console.log('NGO Response:', ngoData)

            // Update with fetched data, prioritizing API response
            ngoPhone = ngoData.phone || ngoPhone
            ngoEmail = ngoData.email || ngoEmail
          } catch (error) {
            console.error('Failed to fetch NGO details for ID:', campaign.ngo_id, error)
          }
        }

        return {
          id: campaign.id,
          disaster_name: campaign.disaster_name,
          disaster_location: campaign.disaster_location,
          ngo_id: campaign.ngo_id,
          ngo_name: campaign.ngo_name || campaign.ngo?.name,
          ngo_phone: ngoPhone,
          ngo_email: ngoEmail,
          status: campaign.status
        }
      })

    registeredCampaigns.value = await Promise.all(campaignPromises)

    // Check if there are active registrations
    if (registeredCampaigns.value.length === 0) {
      hasVolunteerRegistrations.value = false
      return
    }

    // User has registration(s) - show volunteer interface
    hasVolunteerRegistrations.value = true

    // Check for active tasks
    const tasksResponse = await api.get('/my-tasks')
    const tasks = tasksResponse.data

    // Find active task (assigned status from task logs)
    const activeTask = tasks.find((task: Task) =>
      task.status === 'assigned' || task.status === 'pending'
    )

    if (activeTask) {
      // Get task log for this task
      const taskLogResponse = await api.get(`/task-logs?task_id=${activeTask.task_id}`)
      const logs = taskLogResponse.data

      if (logs.length > 0) {
        const taskLog = logs[0] // Get the latest log

        // Condition 1: If task is checked out and user.volunteer is false, show register as volunteer
        if (taskLog.check_out && !auth.user?.volunteer) {
          hasVolunteerRegistrations.value = false
          currentTask.value = null
          currentTaskLog.value = null
          completedTask.value = null
          return
        }

        // Condition 2: If task status is assigned and no check-in/check-out, show assigned task view
        if (activeTask.status === 'assigned' && !taskLog.check_in && !taskLog.check_out) {
          currentTask.value = activeTask
          currentTaskLog.value = taskLog
          return
        }

        // If task is checked out, don't show as current task
        if (taskLog.check_out) {
          currentTask.value = null
          currentTaskLog.value = null
          // Check if this was completed very recently (last 24 hours) for thank you message
          // Only show if user is still a volunteer (hasn't resigned) and no new task assigned
          const completedTime = new Date(taskLog.check_out)
          const now = new Date()
          const hoursDiff = (now.getTime() - completedTime.getTime()) / (1000 * 60 * 60)

          if (hoursDiff <= 24 && auth.user?.volunteer && hasVolunteerRegistrations.value) {
            completedTask.value = activeTask
          }
        } else {
          currentTask.value = activeTask
          currentTaskLog.value = taskLog
        }
      } else {
        // No task log exists yet, but task is assigned - show as current task
        if (activeTask.status === 'assigned') {
          currentTask.value = activeTask
          currentTaskLog.value = null
        }
      }
    } else {
      // Check if user has completed tasks recently
      const recentCompletedTask = tasks.find((task: Task) =>
        task.status === 'ended' || task.status === 'completed'
      )

      if (recentCompletedTask) {
        // Check if this was completed very recently (last 24 hours)
        // Only show if user is still a volunteer (hasn't resigned) and no new task assigned
        const completedTime = new Date(recentCompletedTask.end_time || recentCompletedTask.start_time)
        const now = new Date()
        const hoursDiff = (now.getTime() - completedTime.getTime()) / (1000 * 60 * 60)

        if (hoursDiff <= 24 && auth.user?.volunteer && hasVolunteerRegistrations.value) {
          completedTask.value = recentCompletedTask
        }
      }
    }

  } catch (err: any) {
    if (err.response?.status === 403) {
      // User is not a volunteer or has no registrations
      hasVolunteerRegistrations.value = false
    } else {
      console.error('Failed to fetch volunteer status:', err)
      error.value = 'Failed to load volunteer status'
    }
  } finally {
    loading.value = false
  }
}

const fetchContributionHistory = async () => {
  if (!auth.user?.id) return

  loadingHistory.value = true

  try {
    // Get completed task logs for this volunteer where status is 'ended'
    // Use the /task-logs endpoint with volunteer_id parameter
    const response = await api.get('/task-logs', {
      params: {
        volunteer_id: auth.user.id
      }
    })
    const logs = response.data

    console.log('Fetched volunteer task logs:', logs)

    contributionHistory.value = logs
      .filter((log: any) => log.status === 'ended' && log.check_in && log.check_out)
      .map((log: any) => {
        // Try to get campaign name from different possible sources
        let campaignName = 'Unknown Campaign'
        if (log.task?.disaster_id) {
          campaignName = `Disaster Response #${log.task.disaster_id}`
        } else if (log.task?.campaign_id) {
          campaignName = `Campaign #${log.task.campaign_id}`
        } else if (log.campaign_id) {
          campaignName = `Campaign #${log.campaign_id}`
        }

        return {
          id: log.id,
          campaign_name: campaignName,
          aid_type: log.task?.aid_type || 'Volunteer Work',
          check_in: log.check_in,
          check_out: log.check_out
        }
      })

    console.log('Processed contribution history:', contributionHistory.value)
  } catch (err: any) {
    console.error('Failed to fetch contribution history:', err)
  } finally {
    loadingHistory.value = false
  }
}

const resignAsVolunteer = async () => {
  if (!auth.user?.id) return

  resigning.value = true

  try {
    await api.post('/volunteer/resign')

    // Update user state immediately
    if (auth.user) {
      auth.user.volunteer = false
    }

    // Reset component state to show registration card
    hasVolunteerRegistrations.value = false
    currentTask.value = null
    currentTaskLog.value = null
    completedTask.value = null
    registeredCampaigns.value = []
    contributionHistory.value = []
    error.value = ''

    // Don't refresh fetchVolunteerStatus as it might override our state
    // The user should now see the "Register as Volunteer" card

  } catch (err: any) {
    console.error('Failed to resign as volunteer:', err)
    error.value = 'Failed to resign as volunteer'
  } finally {
    resigning.value = false
  }
}

const getStatusColor = () => {
  if (!hasVolunteerRegistrations.value) {
    return 'bg-gray-100 text-gray-800'
  }
  if (currentTask.value) {
    return 'bg-blue-100 text-blue-800'
  }
  if (completedTask.value) {
    return 'bg-green-100 text-green-800'
  }
  return 'bg-yellow-100 text-yellow-800'
}

const getStatusText = () => {
  if (!hasVolunteerRegistrations.value) {
    return 'NOT REGISTERED'
  }
  if (currentTask.value) {
    return 'TASK ASSIGNED'
  }
  if (completedTask.value) {
    return 'TASK COMPLETED'
  }
  return 'WAITING'
}

const getUrgencyColor = (urgency: string | undefined) => {
  switch (urgency?.toLowerCase()) {
    case 'high':
    case 'critical':
      return 'bg-red-100 text-red-800'
    case 'medium':
      return 'bg-yellow-100 text-yellow-800'
    case 'low':
      return 'bg-green-100 text-green-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getCheckInStatusColor = (status: string | undefined) => {
  switch (status?.toLowerCase()) {
    case 'assigned':
      return 'bg-yellow-100 text-yellow-800'
    case 'started':
      return 'bg-blue-100 text-blue-800'
    case 'ended':
      return 'bg-green-100 text-green-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getCheckInStatusText = (status: string | undefined) => {
  switch (status?.toLowerCase()) {
    case 'assigned':
      return 'Awaiting Check-in'
    case 'started':
      return 'Checked In'
    case 'ended':
      return 'Checked Out'
    default:
      return 'Unknown'
  }
}

const getCurrentCampaign = () => {
  if (currentTask.value) {
    // Try to find the campaign from registered campaigns that matches current task
    return registeredCampaigns.value.find(campaign =>
      campaign.disaster_name === currentTask.value?.disaster ||
      campaign.disaster_name === currentTask.value?.campaign_name
    ) || registeredCampaigns.value[0] // fallback to first campaign
  }
  return null
}

// Get the campaign user is registered under (should be only one)
const getPrimaryCampaign = () => {
  if (registeredCampaigns.value.length === 0) return null

  // Return the campaign user is registered under
  return registeredCampaigns.value[0]
}

const formatDateTime = (dateString: string | undefined) => {
  if (!dateString) return 'N/A'

  try {
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateString
  }
}

// Check if user should see this component
const shouldShowComponent = computed(() => {
  return true // Always show for general users
})

// Check if user has completed tasks
const hasCompletedTasks = computed(() => {
  return contributionHistory.value.length > 0
})

// Method to refresh component data (to be called after volunteer registration)
const refreshData = async () => {
  // Reset state first
  currentTask.value = null
  currentTaskLog.value = null
  completedTask.value = null
  error.value = ''

  // If user is not a volunteer, they should see registration card
  if (!auth.user?.volunteer) {
    hasVolunteerRegistrations.value = false
    registeredCampaigns.value = []
    return
  }

  // Otherwise fetch the current status
  await fetchVolunteerStatus()
  await fetchContributionHistory()
}

// Watch for changes in showContributionHistory to fetch data
watch(showContributionHistory, (newVal) => {
  if (newVal) {
    fetchContributionHistory()
  }
})

onMounted(() => {
  fetchVolunteerStatus()
  // Fetch contribution history to check if user has completed tasks
  fetchContributionHistory()
})

// Expose shouldShowComponent and refreshData for parent component
defineExpose({
  shouldShowComponent,
  refreshData
})
</script>
