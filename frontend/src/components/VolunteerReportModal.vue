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
  total_volunteers: number;
  tasks_assigned: number;
  tasks_completed: number;
  completion_rate: number;
  total_hours: number;
}

interface VolunteerIndividual {
  volunteer_id: number;
  name: string;
  tasks_assigned: number;
  tasks_completed: number;
  attendance_days: number;
  first_checkin: string;
  last_checkout: string;
  total_hours: number;
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
    // Fetch aggregate report
    const aggRes = await api.get(`/reports/volunteers/aggregate?disaster_id=${props.campaign.disaster_id}`);
    aggregateReport.value = aggRes.data;

    // Fetch individual reports
    const indRes = await api.get(`/reports/volunteers/individual?disaster_id=${props.campaign.disaster_id}`);
    individualReports.value = Array.isArray(indRes.data) ? indRes.data : [];

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

// Function to format date
const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Function to format hours
const formatHours = (hours: number) => {
  return `${hours.toFixed(1)}h`;
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
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Total Volunteers -->
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
              <h3 class="text-lg font-semibold text-blue-800">Total Volunteers</h3>
              <p class="text-3xl font-bold text-blue-900">{{ aggregateReport.total_volunteers }}</p>
            </div>

            <!-- Tasks Assigned -->
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
              <h3 class="text-lg font-semibold text-purple-800">Tasks Assigned</h3>
              <p class="text-3xl font-bold text-purple-900">{{ aggregateReport.tasks_assigned }}</p>
            </div>

            <!-- Tasks Completed -->
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
              <h3 class="text-lg font-semibold text-green-800">Tasks Completed</h3>
              <p class="text-3xl font-bold text-green-900">{{ aggregateReport.tasks_completed }}</p>
            </div>

            <!-- Completion Rate -->
            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
              <h3 class="text-lg font-semibold text-yellow-800">Completion Rate</h3>
              <p :class="['text-3xl font-bold', completionRateColor]">
                {{ aggregateReport.completion_rate }}%
              </p>
            </div>

            <!-- Total Hours -->
            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
              <h3 class="text-lg font-semibold text-indigo-800">Total Hours</h3>
              <p class="text-3xl font-bold text-indigo-900">{{ formatHours(aggregateReport.total_hours) }}</p>
            </div>

            <!-- Average Hours per Volunteer -->
            <div class="bg-pink-50 p-4 rounded-lg border border-pink-200">
              <h3 class="text-lg font-semibold text-pink-800">Avg Hours/Volunteer</h3>
              <p class="text-3xl font-bold text-pink-900">
                {{ aggregateReport.total_volunteers > 0 ? formatHours(aggregateReport.total_hours / aggregateReport.total_volunteers) : '0h' }}
              </p>
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
                    First Check-in
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Last Check-out
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="volunteer in individualReports" :key="volunteer.volunteer_id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ volunteer.name }}</div>
                    <div class="text-sm text-gray-500">ID: {{ volunteer.volunteer_id }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ volunteer.tasks_assigned }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="volunteer.tasks_completed === volunteer.tasks_assigned ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                      {{ volunteer.tasks_completed }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ volunteer.attendance_days }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatHours(volunteer.total_hours) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(volunteer.first_checkin) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(volunteer.last_checkout) }}
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
