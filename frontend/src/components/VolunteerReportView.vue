<template>
  <div v-if="isNgoStaff && selectedDisasterId" class="bg-white p-4 rounded shadow mb-8">
    <h3 class="text-xl font-semibold mb-4">Volunteer Reports for Disaster ID: {{ selectedDisasterId }}</h3>
    <div class="flex justify-end mb-4">
      <button
        @click="activeTab = 'aggregate'"
        :class="{ 'bg-blue-500 text-white': activeTab === 'aggregate', 'bg-gray-200': activeTab !== 'aggregate' }"
        class="px-4 py-2 rounded-l hover:bg-blue-600 transition-colors mr-1"
      >
        Aggregate Report
      </button>
      <button
        @click="activeTab = 'individual'"
        :class="{ 'bg-blue-500 text-white': activeTab === 'individual', 'bg-gray-200': activeTab !== 'individual' }"
        class="px-4 py-2 rounded-r hover:bg-blue-600 transition-colors"
      >
        Individual Report
      </button>
    </div>
    <div v-if="isLoading" class="text-center text-gray-500">Loading reports...</div>
    <div v-else-if="errorMessage" class="text-center text-red-500">{{ errorMessage }}</div>
    <div v-else>
      <!-- Aggregated Report -->
      <div v-if="activeTab === 'aggregate'">
        <h4 class="text-lg font-semibold mb-2">Aggregated Report</h4>
        <table class="table-auto w-full mb-4" v-if="filteredAggregateReports.length">
          <thead>
            <tr>
              <th>Total Volunteers</th>
              <th>Tasks Assigned</th>
              <th>Tasks Completed</th>
              <th>Completion Rate</th>
              <th>Total Hours</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="agg in filteredAggregateReports" :key="agg.disaster_id">
              <td>{{ agg.total_volunteers }}</td>
              <td>{{ agg.tasks_assigned }}</td>
              <td>{{ agg.tasks_completed }}</td>
              <td>{{ agg.completion_rate }}%</td>
              <td>{{ agg.total_hours }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="text-gray-500">No aggregated report data available.</p>
      </div>

      <!-- Individual Report -->
      <div v-if="activeTab === 'individual'">
        <h4 class="text-lg font-semibold mb-2">Individual Report</h4>
        <table class="table-auto w-full" v-if="filteredIndividualReports.length">
          <thead>
            <tr>
              <th>Name</th>
              <th>Tasks Assigned</th>
              <th>Tasks Completed</th>
              <th>Attendance Days</th>
              <th>First Check-in</th>
              <th>Last Check-out</th>
              <th>Total Hours</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ind in filteredIndividualReports" :key="ind.volunteer_id">
              <td>{{ ind.name }}</td>
              <td>{{ ind.tasks_assigned }}</td>
              <td>{{ ind.tasks_completed }}</td>
              <td>{{ ind.attendance_days }}</td>
              <td>{{ ind.first_checkin }}</td>
              <td>{{ ind.last_checkout }}</td>
              <td>{{ ind.total_hours }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="text-gray-500">No individual report data available.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { api } from '@/lib/api';

// Props
defineProps<{
  isNgoStaff: boolean;
  campaigns: { id: number; disaster_id: number }[];
}>();

// Emits
const emit = defineEmits(['update:errorMessage']);

// Reactive state
const selectedDisasterId = ref<number | null>(null);
const aggregateReports = ref<{ disaster_id: number; total_volunteers: number; tasks_assigned: number; tasks_completed: number; completion_rate: number; total_hours: number }[]>([]);
const individualReports = ref<{ volunteer_id: number; name: string; tasks_assigned: number; tasks_completed: number; attendance_days: number; first_checkin: string; last_checkout: string; total_hours: number; disaster_id: number }[]>([]);
const isLoading = ref(false);
const errorMessage = ref<string>('');
const activeTab = ref<'aggregate' | 'individual'>('aggregate'); // New tab state, defaults to aggregate

// Computed properties
const filteredAggregateReports = computed(() =>
  aggregateReports.value.filter(r => r.disaster_id === selectedDisasterId.value)
);
const filteredIndividualReports = computed(() =>
  individualReports.value.filter(r => r.disaster_id === selectedDisasterId.value)
);

// Watch for selectedDisasterId changes to refetch if needed
watch(selectedDisasterId, async (newId) => {
  if (newId && isNgoStaff) {
    await fetchReports(newId);
  }
});

// Method to fetch reports
const fetchReports = async (disasterId: number) => {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    const aggRes = await api.get(`/reports/volunteers/aggregate?disaster_id=${disasterId}`);
    const indRes = await api.get(`/reports/volunteers/individual?disaster_id=${disasterId}`);

    // Update reports only for the selected disaster
    aggregateReports.value = aggregateReports.value.filter(r => r.disaster_id !== disasterId).concat(aggRes.data);
    individualReports.value = individualReports.value.filter(r => r.disaster_id !== disasterId).concat(indRes.data);
  } catch (reportError) {
    console.error(`Failed to fetch report for disaster_id ${disasterId}:`, reportError);
    errorMessage.value = `Failed to load reports for disaster ID ${disasterId}. Please try again later.`;
    emit('update:errorMessage', errorMessage.value);
  } finally {
    isLoading.value = false;
  }
};

// Method to set selected disaster and fetch reports
const viewReports = (disasterId: number) => {
  selectedDisasterId.value = disasterId;
  if (isNgoStaff) {
    fetchReports(disasterId);
  }
};

// Initial fetch if campaigns exist
onMounted(async () => {
  if (isNgoStaff && campaigns.value.length > 0) {
    selectedDisasterId.value = campaigns.value[0].disaster_id;
    await fetchReports(selectedDisasterId.value);
  }
});
</script>
