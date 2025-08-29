<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { api } from '@/lib/api'
import Modal from './Modal.vue'

interface Campaign {
  id: number;
  name: string;
  campaign_name?: string;
  disaster_id: number;
  disaster_name: string;
  ngo_name: string;
  help_needed: 'low' | 'medium' | 'high';
  status: string;
}

interface VolunteerAggregate {
  disaster_id: number;
  campaign_id: number;
  ngo_id: number;
  report_type: string;
  total_volunteers: number;
  active_volunteers: number;
  flagged_volunteers: number;
  volunteers_with_tasks: number;
  tasks_assigned: number;
  tasks_completed: number;
  tasks_started: number;
  completion_rate: number;
  total_hours: number;
}

interface VolunteerIndividual {
  volunteer_id: number;
  registration_id?: number;
  name: string;
  email?: string;
  phone?: string;
  registration_status?: string;
  skills?: string;
  availability?: string;
  registered_at?: string;
  notes?: string;
  tasks_assigned: number;
  tasks_completed: number;
  attendance_days: number;
  first_checkin: string | null;
  last_checkout: string | null;
  total_hours: number;
  task_statistics?: {
    tasks_assigned: number;
    tasks_completed: number;
    tasks_in_progress: number;
    attendance_days: number;
    total_hours: number;
    first_checkin: string | null;
    last_checkout: string | null;
  };
}

// Props
const props = defineProps<{
  campaign: Campaign | null;
  show: boolean;
}>();

// Emits
const emit = defineEmits<{
  close: [];
}>();

// State
const activeTab = ref<'aggregate' | 'individual'>('aggregate');
const isLoading = ref(true);
const errorMessage = ref<string>('');
const aggregateReport = ref<VolunteerAggregate | null>(null);
const individualReports = ref<VolunteerIndividual[]>([]);

// Fetch reports when component mounts
onMounted(async () => {
  if (props.show && props.campaign) {
    await fetchReports();
  }
});

// Watch for show prop changes to fetch reports when modal opens
watch(() => props.show, async (newShow) => {
  if (newShow && props.campaign) {
    await fetchReports();
  }
});

// Function to fetch both aggregate and individual reports
const fetchReports = async () => {
  if (!props.campaign) return;

  isLoading.value = true;
  errorMessage.value = '';

  try {
    // Fetch aggregate report using campaign_id
    const aggRes = await api.get(`/reports/volunteers/aggregate?campaign_id=${props.campaign.id}`);
    console.log('Aggregate response:', aggRes.data);
    aggregateReport.value = aggRes.data;

    // Fetch individual reports using campaign_id
    const indRes = await api.get(`/reports/volunteers/individual?campaign_id=${props.campaign.id}`);
    console.log('Individual response:', indRes.data);
    console.log('Individual volunteers array:', indRes.data.volunteers);

    // Handle the response structure
    if (indRes.data && Array.isArray(indRes.data.volunteers)) {
      individualReports.value = indRes.data.volunteers;
    } else if (Array.isArray(indRes.data)) {
      individualReports.value = indRes.data;
    } else {
      individualReports.value = [];
    }

  } catch (error: any) {
    console.error('Failed to fetch volunteer reports:', error);
    errorMessage.value = error.response?.data?.error || 'Failed to load volunteer reports. Please try again later.';
  } finally {
    isLoading.value = false;
  }
};

// Function to close modal
const closeModal = () => {
  emit('close');
};

// Function to format hours with better precision
const formatHours = (hours: number) => {
  if (hours === 0) return '0h';

  // For very small values (less than 0.1 hours = 6 minutes)
  if (hours < 0.1) {
    const minutes = Math.round(hours * 60);
    const seconds = Math.round((hours * 3600) % 60);
    if (minutes === 0) {
      return `${seconds}s`;
    }
    return `${minutes}m ${seconds}s`;
  }

  // For values less than 1 hour
  if (hours < 1) {
    const minutes = Math.round(hours * 60);
    return `${minutes}m`;
  }

  // For 1 hour or more
  const wholeHours = Math.floor(hours);
  const minutes = Math.round((hours - wholeHours) * 60);

  if (minutes === 0) {
    return `${wholeHours}h`;
  }

  return `${wholeHours}h ${minutes}m`;
};

// Function to get exact hours tooltip
const getExactHoursTooltip = (hours: number) => {
  if (hours < 0.001) {
    return `Exact: ${(hours * 3600).toFixed(1)} seconds`;
  } else if (hours < 0.1) {
    return `Exact: ${(hours * 60).toFixed(2)} minutes`;
  } else {
    return `Exact: ${hours.toFixed(4)} hours`;
  }
};

// Function to get registration status color
const getRegistrationStatusColor = (status: string) => {
  switch (status?.toLowerCase()) {
    case 'approved':
      return 'bg-green-100 text-green-800';
    case 'flagged':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

// Function to flag a volunteer
const flagVolunteer = async (volunteerId: number) => {
  if (!props.campaign || !confirm('Are you sure you want to flag this volunteer? This action cannot be undone and the volunteer will be permanently banned.')) {
    return;
  }

  try {
    await api.post('/reports/volunteers/flag', {
      volunteer_id: volunteerId,
      campaign_id: props.campaign.id
    });

    // Refresh the reports to show updated status
    await fetchReports();

    alert('Volunteer has been flagged successfully');
  } catch (error) {
    console.error('Failed to flag volunteer:', error);
    alert('Failed to flag volunteer. Please try again.');
  }
};

// Function to get task completion color
const getTaskCompletionColor = (completed: number, assigned: number) => {
  if (assigned === 0) return 'bg-gray-100 text-gray-800';
  if (completed === assigned) return 'bg-green-100 text-green-800';
  if (completed > assigned * 0.7) return 'bg-yellow-100 text-yellow-800';
  return 'bg-red-100 text-red-800';
};

// Computed property for completion rate color
const completionRateColor = computed(() => {
  if (!aggregateReport.value) return 'text-gray-500';
  const rate = aggregateReport.value.completion_rate || 0;
  if (rate >= 80) return 'text-green-600';
  if (rate >= 60) return 'text-yellow-600';
  return 'text-red-600';
});

// Computed property for safe campaign access
const safeCampaign = computed(() => {
  if (!props.campaign) {
    return {
      campaign_name: 'Unknown Campaign',
      name: 'Unknown Campaign',
      disaster_name: 'Unknown Disaster'
    };
  }
  return {
    ...props.campaign,
    campaign_name: props.campaign.campaign_name || props.campaign.name || 'Unknown Campaign',
    name: props.campaign.name || props.campaign.campaign_name || 'Unknown Campaign'
  };
});
</script>

<template>
  <Modal
    :show="show"
    :title="`Volunteer Reports - ${safeCampaign.campaign_name || safeCampaign.name}`"
    maxWidth="max-w-full"
    zIndex="z-50"
    @close="closeModal"
  >
    <!-- Tab Navigation -->
    <div class="flex mb-6">
      <button
        @click="activeTab = 'aggregate'"
        :class="[
          'px-4 py-2 font-medium text-sm rounded-l-md transition-colors',
          activeTab === 'aggregate'
            ? 'bg-blue-600 text-white'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
        ]"
      >
        Aggregate Report
      </button>
      <button
        @click="activeTab = 'individual'"
        :class="[
          'px-4 py-2 font-medium text-sm rounded-r-md transition-colors',
          activeTab === 'individual'
            ? 'bg-blue-600 text-white'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
        ]"
      >
        Individual Reports
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <p class="text-gray-600 mt-2">Loading reports...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage" class="text-center py-8">
      <p class="text-red-600">{{ errorMessage }}</p>
      <button
        @click="fetchReports"
        class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
      >
        Retry
      </button>
    </div>

    <!-- Aggregate Report -->
    <div v-else-if="activeTab === 'aggregate' && aggregateReport" class="space-y-6">
      <!-- Volunteer Registration Statistics -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Volunteer Registration Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Total Registered -->
          <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <h4 class="text-sm font-semibold text-blue-800">Total Registered</h4>
            <p class="text-2xl font-bold text-blue-900">{{ aggregateReport.total_volunteers || 0 }}</p>
          </div>

          <!-- Active Volunteers -->
          <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <h4 class="text-sm font-semibold text-green-800">Approved</h4>
            <p class="text-2xl font-bold text-green-900">{{ aggregateReport.active_volunteers || 0 }}</p>
          </div>

          <!-- Flagged Volunteers -->
          <div class="bg-red-50 p-4 rounded-lg border border-red-200">
            <h4 class="text-sm font-semibold text-red-800">Flagged</h4>
            <p class="text-2xl font-bold text-red-900">{{ aggregateReport.flagged_volunteers || 0 }}</p>
          </div>
        </div>
      </div>

      <!-- Task Performance Statistics -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Task Performance Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
          <!-- Working Volunteers -->
          <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
            <h4 class="text-sm font-semibold text-indigo-800">Working Volunteers</h4>
            <p class="text-2xl font-bold text-indigo-900">{{ aggregateReport.volunteers_with_tasks || 0 }}</p>
          </div>

          <!-- Tasks Assigned -->
          <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <h4 class="text-sm font-semibold text-purple-800">Tasks Assigned</h4>
            <p class="text-2xl font-bold text-purple-900">{{ aggregateReport.tasks_assigned || 0 }}</p>
          </div>

          <!-- Tasks Completed -->
          <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <h4 class="text-sm font-semibold text-green-800">Tasks Completed</h4>
            <p class="text-2xl font-bold text-green-900">{{ aggregateReport.tasks_completed || 0 }}</p>
          </div>

          <!-- Completion Rate -->
          <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <h4 class="text-sm font-semibold text-yellow-800">Completion Rate</h4>
            <p :class="['text-2xl font-bold', completionRateColor]">
              {{ aggregateReport.completion_rate || 0 }}%
            </p>
          </div>

          <!-- Total Hours -->
          <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
            <h4 class="text-sm font-semibold text-orange-800">Total Hours Worked</h4>
            <p class="text-2xl font-bold text-orange-900" :title="getExactHoursTooltip(aggregateReport.total_hours || 0)">
              {{ formatHours(aggregateReport.total_hours || 0) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Individual Reports -->
    <div v-else-if="activeTab === 'individual'" class="space-y-4">
      <div v-if="individualReports.length" class="overflow-x-auto max-h-96 overflow-y-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 sticky top-0">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Volunteer
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Assigned
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Completed
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Hours
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="volunteer in individualReports" :key="volunteer.volunteer_id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ volunteer.name }}</div>
                <div class="text-sm text-gray-500">ID: {{ volunteer.volunteer_id }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getRegistrationStatusColor(volunteer.registration_status || 'unknown')">
                  {{ volunteer.registration_status || 'N/A' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ volunteer.task_statistics?.tasks_assigned || volunteer.tasks_assigned || 0 }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getTaskCompletionColor(volunteer.task_statistics?.tasks_completed || volunteer.tasks_completed || 0, volunteer.task_statistics?.tasks_assigned || volunteer.tasks_assigned || 0)">
                  {{ volunteer.task_statistics?.tasks_completed || volunteer.tasks_completed || 0 }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                  :title="getExactHoursTooltip(volunteer.task_statistics?.total_hours || volunteer.total_hours || 0)">
                {{ formatHours(volunteer.task_statistics?.total_hours || volunteer.total_hours || 0) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <button
                  v-if="volunteer.registration_status !== 'flagged'"
                  @click="flagVolunteer(volunteer.volunteer_id)"
                  class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1 rounded transition-colors"
                >
                  Report
                </button>
                <span v-else class="text-red-600 text-xs font-medium">
                  Flagged
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="text-center py-8">
        <p class="text-gray-500">No individual volunteer reports found for this campaign.</p>
      </div>
    </div>

    <!-- No Data State -->
    <div v-else class="text-center py-8">
      <p class="text-gray-500">No report data available for this campaign.</p>
    </div>
  </Modal>
</template>
