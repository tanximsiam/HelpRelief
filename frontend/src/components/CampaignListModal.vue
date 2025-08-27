<script setup lang="ts">
import { ref, computed } from 'vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import VolunteerReportModal from '@/components/VolunteerReportModal.vue'
import VolunteerTaskLogOverlay from '@/components/VolunteerTaskLogOverlay.vue'

interface Campaign {
  id: number;
  name: string;
  disaster_id: number;
  disaster_name: string;
  ngo_name: string;
  help_needed: 'low' | 'medium' | 'high';
  status: string;
}

// Props
const props = defineProps<{
  campaigns: Campaign[];
  isNgoStaff: boolean;
}>();

// Emits
const emit = defineEmits<{
  close: [];
}>();

// State
const showVolunteerReportModal = ref(false);
const selectedCampaign = ref<Campaign | null>(null);
const showTaskLogs = ref(false);
const taskLogCampaign = ref<Campaign | null>(null);

function openTaskLogs(campaign: Campaign) {
  taskLogCampaign.value = campaign;
  showTaskLogs.value = true;
}

// Computed property to sort campaigns by priority
const sortedCampaigns = computed(() => {
  const priorityOrder = { 'high': 3, 'medium': 2, 'low': 1 };
  return [...props.campaigns]
    .sort((a, b) => priorityOrder[b.help_needed] - priorityOrder[a.help_needed]);
});

// Function to get priority badge color
const getPriorityColor = (priority: string) => {
  switch (priority) {
    case 'high': return 'bg-red-100 text-red-800';
    case 'medium': return 'bg-yellow-100 text-yellow-800';
    case 'low': return 'bg-green-100 text-green-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};

// Function to view volunteer reports
const viewVolunteerReports = (campaign: Campaign) => {
  selectedCampaign.value = campaign;
  showVolunteerReportModal.value = true;
};

// Function to close volunteer report modal
const closeVolunteerReportModal = () => {
  showVolunteerReportModal.value = false;
  selectedCampaign.value = null;
};

// Function to close main modal
const closeModal = () => {
  emit('close');
};
</script>

<template>
  <!-- Modal Backdrop -->
  <div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click.self="closeModal"
  >
    <!-- Modal Content -->
    <div
      class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-2xl font-bold">
          {{ isNgoStaff ? 'My NGO Campaigns' : 'All Ongoing Campaigns' }}
        </h2>
        <button
          @click="closeModal"
          class="text-gray-400 hover:text-gray-600 text-xl"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="px-6 py-4">
        <div v-if="sortedCampaigns.length" class="space-y-4">
          <div
            v-for="campaign in sortedCampaigns"
            :key="campaign.id"
            class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="text-lg font-semibold text-gray-900">{{ campaign.name }}</h3>
                  <span
                    :class="['px-2 py-1 text-xs rounded-full', getPriorityColor(campaign.help_needed)]"
                  >
                    {{ campaign.help_needed.toUpperCase() }} PRIORITY
                  </span>
                  <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                    {{ campaign.status.toUpperCase() }}
                  </span>
                </div>
                <p class="text-gray-600 mb-1">
                  <strong>Disaster:</strong> {{ campaign.disaster_name }}
                </p>
                <p class="text-gray-600 mb-1">
                  <strong>Managed by:</strong> {{ campaign.ngo_name || 'Unknown NGO' }}
                </p>
                <p class="text-sm text-gray-500">
                  Campaign ID: {{ campaign.id }}
                </p>
              </div>

              <!-- Action Button (only for NGO staff) -->
              <div v-if="isNgoStaff" class="ml-4 flex flex-col gap-2 w-36">
                <PrimaryButton
                  variant="primary"
                  @click="viewVolunteerReports(campaign)"
                  class="px-4 py-2 text-sm w-full"
                >
                  View Reports
                </PrimaryButton>
                <button
                  type="button"
                  @click="openTaskLogs(campaign)"
                  class="bg-blue-600 text-white font-medium px-4 py-2 rounded-md hover:bg-blue-700 text-sm w-full shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                >View Task Logs</button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8">
          <p class="text-gray-500 text-lg">No campaigns found.</p>
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

  <!-- Volunteer Report Modal -->
  <VolunteerReportModal
    v-if="showVolunteerReportModal && selectedCampaign"
    :campaign="selectedCampaign"
    @close="closeVolunteerReportModal"
  />
  <VolunteerTaskLogOverlay
    v-if="showTaskLogs"
    :open="showTaskLogs"
    :campaign-id="taskLogCampaign?.id || null"
    @close="() => { showTaskLogs = false; taskLogCampaign = null }"
  />
</template>
