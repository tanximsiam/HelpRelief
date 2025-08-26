<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { api } from '@/lib/api'

interface Campaign {
  id: number;
  name: string;
  disaster_id: number;
  disaster_name: string;
  ngo_name: string;
  help_needed: 'low' | 'medium' | 'high';
  status: string;
}

interface VolunteerAggregate {
  disaster_id: number;
  ngo_id: number;
  report_type: string;
  total_volunteers: number;
  active_volunteers: number;
  pending_volunteers: number;
  rejected_volunteers: number;
  completed_volunteers: number;
  tasks_assigned: number;
  tasks_completed: number;
  completion_rate: number;
  total_hours: number;
  volunteers_with_tasks?: number;
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
  campaign: Campaign;
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
  await fetchReports();
});

// Function to fetch both aggregate and individual reports
const fetchReports = async () => {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    // Fetch aggregate report (comprehensive type to get all volunteer registration data)
    const aggRes = await api.get(`/reports/volunteers/aggregate?disaster_id=${props.campaign.disaster_id}&type=all`);
    aggregateReport.value = aggRes.data;

    // Fetch individual reports (comprehensive type to get all volunteer data)
    const indRes = await api.get(`/reports/volunteers/individual?disaster_id=${props.campaign.disaster_id}&type=all`);

    // Handle the response structure - it might be an array or an object with volunteers property
    if (indRes.data && indRes.data.volunteers) {
      individualReports.value = Array.isArray(indRes.data.volunteers) ? indRes.data.volunteers : [];
    } else if (Array.isArray(indRes.data)) {
      individualReports.value = indRes.data;
    } else {
      individualReports.value = [];
    }

  } catch (error) {
    console.error('Failed to fetch volunteer reports:', error);
    errorMessage.value = 'Failed to load volunteer reports. Please try again later.';
  } finally {
    isLoading.value = false;
  }
};

// Function to close modal
const closeModal = () => {
  emit('close');
};

// Function to format hours
const formatHours = (hours: number) => {
  return `${hours.toFixed(1)}h`;
};

// Function to get registration status color
const getRegistrationStatusColor = (status: string) => {
  switch (status.toLowerCase()) {
    case 'approved':
    case 'active':
      return 'bg-green-100 text-green-800';
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    case 'rejected':
      return 'bg-red-100 text-red-800';
    case 'completed':
      return 'bg-blue-100 text-blue-800';
    default:
      return 'bg-gray-100 text-gray-800';
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
  const rate = aggregateReport.value.completion_rate;
  if (rate >= 80) return 'text-green-600';
  if (rate >= 60) return 'text-yellow-600';
  return 'text-red-600';
});
</script>

<template>
  <!-- Modal Backdrop -->
  <div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click="closeModal"
  >
    <!-- Modal Content -->
    <div
      class="bg-white rounded-lg shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Volunteer Reports</h2>
            <p class="text-gray-600 mt-1">{{ campaign.name }} - {{ campaign.disaster_name }}</p>
          </div>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 text-xl"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Tab Navigation -->
        <div class="flex mt-4">
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
      </div>

      <!-- Modal Body -->
      <div class="px-6 py-4">
        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="text-gray-600 mt-2">Loading reports...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="errorMessage" class="text-center py-8">
          <p class="text-red-600">{{ errorMessage }}</p>
        </div>

        <!-- Aggregate Report -->
        <div v-else-if="activeTab === 'aggregate' && aggregateReport" class="space-y-6">
          <!-- Volunteer Registration Statistics -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Volunteer Registration Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
              <!-- Total Registered -->
              <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <h4 class="text-sm font-semibold text-blue-800">Total Registered</h4>
                <p class="text-2xl font-bold text-blue-900">{{ aggregateReport.total_volunteers || 0 }}</p>
              </div>

              <!-- Active Volunteers -->
              <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <h4 class="text-sm font-semibold text-green-800">Active</h4>
                <p class="text-2xl font-bold text-green-900">{{ aggregateReport.active_volunteers || 0 }}</p>
              </div>

              <!-- Pending Volunteers -->
              <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                <h4 class="text-sm font-semibold text-yellow-800">Pending</h4>
                <p class="text-2xl font-bold text-yellow-900">{{ aggregateReport.pending_volunteers || 0 }}</p>
              </div>

              <!-- Rejected Volunteers -->
              <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                <h4 class="text-sm font-semibold text-red-800">Rejected</h4>
                <p class="text-2xl font-bold text-red-900">{{ aggregateReport.rejected_volunteers || 0 }}</p>
              </div>

              <!-- Completed Volunteers -->
              <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <h4 class="text-sm font-semibold text-purple-800">Completed</h4>
                <p class="text-2xl font-bold text-purple-900">{{ aggregateReport.completed_volunteers || 0 }}</p>
              </div>
            </div>
          </div>

          <!-- Task Performance Statistics -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Task Performance Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <!-- Volunteers with Tasks -->
              <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                <h4 class="text-sm font-semibold text-indigo-800">Volunteers with Tasks</h4>
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
                <h4 class="text-sm font-semibold text-orange-800">Total Hours</h4>
                <p class="text-2xl font-bold text-orange-900">{{ formatHours(aggregateReport.total_hours || 0) }}</p>
              </div>

              <!-- Average Hours per Active Volunteer -->
              <div class="bg-pink-50 p-4 rounded-lg border border-pink-200">
                <h4 class="text-sm font-semibold text-pink-800">Avg Hours/Active Volunteer</h4>
                <p class="text-2xl font-bold text-pink-900">
                  {{ aggregateReport.active_volunteers > 0 ? formatHours((aggregateReport.total_hours || 0) / aggregateReport.active_volunteers) : '0h' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Individual Reports -->
        <div v-else-if="activeTab === 'individual'" class="space-y-4">
          <div v-if="individualReports.length" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Volunteer
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Registration Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Skills
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tasks Assigned
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tasks Completed
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Attendance Days
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total Hours
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contact Info
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
                    <div class="max-w-32 truncate" :title="volunteer.skills || 'No skills listed'">
                      {{ volunteer.skills || 'No skills listed' }}
                    </div>
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
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ volunteer.task_statistics?.attendance_days || volunteer.attendance_days || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatHours(volunteer.task_statistics?.total_hours || volunteer.total_hours || 0) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div>{{ volunteer.email || 'N/A' }}</div>
                    <div>{{ volunteer.phone || 'N/A' }}</div>
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
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
        <button
          @click="closeModal"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>
