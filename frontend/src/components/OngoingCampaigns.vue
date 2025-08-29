<script setup lang="ts">
import { ref, computed, onMounted, defineExpose } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../lib/api'
import Modal from './Modal.vue'
import VolunteerTaskLogOverlay from './VolunteerTaskLogOverlay.vue'

interface Campaign {
  id: number;
  name: string;
  disaster_id: number;
  disaster_name: string;
  ngo_name: string;
  help_needed: 'low' | 'medium' | 'high';
  status: string;
  created_at: string;
}

const campaigns = ref<Campaign[]>([]);
const isLoading = ref(true);
const errorMessage = ref<string>('');
const isNgoStaff = ref(false);
const ngoId = ref<number | null>(null);
const showCampaignListModal = ref(false);
const showTaskLogs = ref(false);
const selectedCampaignForLogs = ref<Campaign | null>(null);
const searchQuery = ref('');
const router = useRouter();

// Computed property for sorted campaigns (by newest and severity)
const sortedCampaigns = computed(() => {
  const priorityOrder = { 'high': 3, 'medium': 2, 'low': 1 };
  let filteredCampaigns = [...campaigns.value];

  // For general users, only show active campaigns
  if (!isNgoStaff.value) {
    filteredCampaigns = filteredCampaigns.filter(campaign => campaign.status === 'active');
  }

  return filteredCampaigns
    .sort((a, b) => {
      // First sort by priority (severity)
      const priorityDiff = priorityOrder[b.help_needed] - priorityOrder[a.help_needed];
      if (priorityDiff !== 0) return priorityDiff;

      // Then by newest (created_at)
      return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
    });
});

// Only show top 3 in dashboard card
const topThreeCampaigns = computed(() => sortedCampaigns.value.slice(0,3));

// Computed property for filtered campaigns in modal
const filteredCampaigns = computed(() => {
  if (!searchQuery.value.trim()) return sortedCampaigns.value;

  const query = searchQuery.value.toLowerCase();
  return sortedCampaigns.value.filter(campaign =>
    campaign.name.toLowerCase().includes(query) ||
    campaign.disaster_name.toLowerCase().includes(query) ||
    campaign.ngo_name?.toLowerCase().includes(query)
  );
});

// Function to navigate to task creation
const navigateToTaskCreation = (campaign: Campaign) => {
  router.push({
    name: 'TaskController',
    query: { campaign_id: campaign.id.toString() }
  });
};

// Fetch user role and dashboard data on mount
onMounted(async () => {
  try {
    const staffRes = await api.get('/ngo-staff');
    const staffData = staffRes.data;
    if (staffData.role === 'ngo_staff' && staffData.ngo_id) {
      isNgoStaff.value = true;
      ngoId.value = staffData.ngo_id;
    } else {
      isNgoStaff.value = false;
      ngoId.value = null;
    }
  } catch (error) {
    console.log('User is not NGO staff:', error);
    isNgoStaff.value = false;
  } finally {
    // Regardless of staff fetch outcome, load campaigns
    await refresh();
  }
});

// Expose methods for parent components
defineExpose({ refresh, append });

// Function to open campaign list modal
const openCampaignList = () => {
  searchQuery.value = ''; // Reset search when opening modal
  showCampaignListModal.value = true;
};

// Function to close campaign list modal
const closeCampaignList = () => {
  showCampaignListModal.value = false;
  searchQuery.value = '';
};

// Function to open task logs
const openTaskLogs = (campaign: Campaign) => {
  selectedCampaignForLogs.value = campaign;
  showTaskLogs.value = true;
};

// Function to close task logs
const closeTaskLogs = () => {
  showTaskLogs.value = false;
  selectedCampaignForLogs.value = null;
};

// Function to toggle campaign status
const toggleCampaignStatus = async (campaign: Campaign) => {
  try {
    const newStatus = campaign.status === 'active' ? 'inactive' : 'active';
    await api.patch(`/campaigns/${campaign.id}/status`, { status: newStatus });

    // Update the campaign status in the local state
    campaign.status = newStatus;
  } catch (error) {
    console.error('Failed to update campaign status:', error);
    // You could add a toast notification here
  }
};

// Function to get priority badge color
const getPriorityColor = (priority: string) => {
  switch (priority) {
    case 'high': return 'bg-red-100 text-red-800';
    case 'medium': return 'bg-yellow-100 text-yellow-800';
    case 'low': return 'bg-green-100 text-green-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};

// Function to format date
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString();
};
</script>

<template>
  <div class="bg-white p-4 rounded shadow">
    <h3 class="text-xl font-semibold mb-4">{{ isNgoStaff ? 'My Campaigns' : 'Ongoing Campaigns' }}</h3>
    <div v-if="isLoading" class="text-center text-gray-500">Loading campaigns...</div>
    <div v-else-if="errorMessage" class="text-center text-red-500">{{ errorMessage }}</div>
    <div v-else-if="sortedCampaigns.length">
  <!-- Campaign list (no inner scrollbar; page scrolls instead) -->
      <div class="space-y-3">
        <div v-for="campaign in topThreeCampaigns" :key="campaign.id" class="p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <h4 class="font-medium text-gray-900">{{ campaign.name }}</h4>
                <span
                  :class="['px-2 py-1 text-xs rounded-full', getPriorityColor(campaign.help_needed)]"
                >
                  {{ campaign.help_needed.toUpperCase() }}
                </span>
                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                  {{ campaign.status.toUpperCase() }}
                </span>
              </div>
              <p class="text-sm text-gray-600">by {{ campaign.ngo_name || 'Unknown NGO' }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ campaign.disaster_name }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ formatDate(campaign.created_at) }}</p>
            </div>

            <!-- Action buttons for NGO staff -->
            <div v-if="isNgoStaff && campaign.status === 'active'" class="flex flex-col gap-2 ml-4">
              <button
                @click="openTaskLogs(campaign)"
                class="bg-blue-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-600 transition-colors"
              >
                Task Log
              </button>
              <button
                @click="navigateToTaskCreation(campaign)"
                class="bg-green-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-600 transition-colors"
              >
                Create Task
              </button>
            </div>
          </div>
        </div>
      </div>

      <button
        @click="openCampaignList"
        class="text-blue-500 hover:text-blue-700 mt-4 inline-block font-medium"
      >
        See More
      </button>
    </div>
    <p v-else class="text-gray-500">No ongoing campaigns found.</p>

    <!-- Campaign List Modal with Search -->
    <Modal
      :show="showCampaignListModal"
      :title="isNgoStaff ? 'All My Campaigns' : 'All Campaigns'"
      maxWidth="max-w-5xl"
      @close="closeCampaignList"
    >
      <div class="space-y-4">
        <!-- Search Bar -->
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search campaigns by name, disaster, or NGO..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>

        <!-- Campaign List -->
  <div v-if="filteredCampaigns.length" class="space-y-4">
          <div
            v-for="campaign in filteredCampaigns"
            :key="campaign.id"
            class="p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                  <h3 class="text-base font-semibold text-gray-900">{{ campaign.name }}</h3>
                  <span
                    :class="['px-2 py-0.5 text-xs rounded-full font-medium', getPriorityColor(campaign.help_needed)]"
                  >
                    {{ campaign.help_needed.toUpperCase() }} PRIORITY
                  </span>
                  <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full font-medium">
                    {{ campaign.status.toUpperCase() }}
                  </span>
                </div>
                <p class="text-gray-600 mb-1 text-sm">
                  <strong>Disaster:</strong> {{ campaign.disaster_name }}
                </p>
                <p class="text-gray-600 mb-1 text-sm">
                  <strong>Managed by:</strong> {{ campaign.ngo_name || 'Unknown NGO' }}
                </p>
                <p class="text-xs text-gray-500">
                  Created: {{ formatDate(campaign.created_at) }} | #ID {{ campaign.id }}
                </p>
              </div>

              <!-- Action buttons for NGO staff -->
              <div v-if="isNgoStaff && campaign.status === 'active'" class="flex gap-3 ml-4">
                <button
                  @click="openTaskLogs(campaign)"
                  class="bg-blue-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-600 transition-colors"
                >
                  View Task Log
                </button>
                <button
                  @click="navigateToTaskCreation(campaign)"
                  class="bg-green-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-600 transition-colors"
                >
                  Create Task
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8">
          <p class="text-gray-500">{{ searchQuery ? 'No campaigns found matching your search.' : 'No campaigns found.' }}</p>
        </div>
      </div>
    </Modal>

    <!-- Task Logs Overlay -->
    <VolunteerTaskLogOverlay
      :open="showTaskLogs"
      :campaignId="selectedCampaignForLogs?.id || null"
      @close="closeTaskLogs"
    />
  </div>
</template>
