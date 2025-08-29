<script setup lang="ts">
import { ref, computed } from 'vue'
import { api } from '../lib/api'
import VolunteerTaskLogOverlay from './VolunteerTaskLogOverlay.vue'

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

// Task logs state
const showTaskLogs = ref(false);
const selectedCampaignForLogs = ref<Campaign | null>(null);

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

// Function to close main modal
const closeModal = () => {
  emit('close');
};
</script>

<template>
  <div>
    <!-- Modal Backdrop -->
    <div
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
    <!-- Modal Content -->
    <div
      class="rounded-xl shadow-xl max-w-5xl w-full mx-4 max-h-[90vh] overflow-y-auto border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-white backdrop-blur-sm"
      @click.stop
    >
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-white/70 backdrop-blur-sm rounded-t-xl">
        <h2 class="text-xl font-semibold tracking-tight text-slate-800">
          {{ isNgoStaff ? 'My NGO Campaigns' : 'All Ongoing Campaigns' }}
        </h2>
        <button
          @click="closeModal"
          class="text-slate-400 hover:text-slate-600 transition"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="px-6 py-5">
        <div v-if="sortedCampaigns.length" class="space-y-4">
          <div
            v-for="campaign in sortedCampaigns"
            :key="campaign.id"
            class="p-5 bg-white/80 backdrop-blur rounded-lg border border-slate-200 shadow-sm hover:shadow-md transition group"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3 mb-2">
                  <h3 class="text-base font-semibold text-slate-900 group-hover:text-blue-600 transition">{{ campaign.name }}</h3>
                  <span
                    :class="['px-2 py-0.5 text-[11px] rounded-full font-medium', getPriorityColor(campaign.help_needed)]"
                  >
                    {{ campaign.help_needed.toUpperCase() }} PRIORITY
                  </span>
                  <span class="px-2 py-0.5 text-[11px] bg-blue-100 text-blue-800 rounded-full font-medium">
                    {{ campaign.status.toUpperCase() }}
                  </span>
                </div>
                <p class="text-slate-600 mb-1 text-sm">
                  <strong>Disaster:</strong> {{ campaign.disaster_name }}
                </p>
                <p class="text-slate-600 mb-1 text-sm">
                  <strong>Managed by:</strong> {{ campaign.ngo_name || 'Unknown NGO' }}
                </p>
                <p class="text-xs text-slate-500 font-mono">
                  #ID {{ campaign.id }}
                </p>
              </div>

              <!-- Action buttons for NGO staff -->
              <div v-if="isNgoStaff" class="flex gap-2 ml-4">
                <button
                  @click="openTaskLogs(campaign)"
                  class="bg-blue-500 text-white px-3 py-1 rounded-md text-xs hover:bg-blue-600 transition-colors"
                >
                  View Task Log
                </button>
                <button
                  @click="toggleCampaignStatus(campaign)"
                  :class="campaign.status === 'active' ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-500 hover:bg-green-600'"
                  class="text-white px-3 py-1 rounded-md text-xs transition-colors"
                >
                  {{ campaign.status === 'active' ? 'Inactive' : 'Activate' }}
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8">
      <p class="text-slate-500 text-sm">No campaigns found.</p>
        </div>
      </div>

      <!-- Modal Footer -->
    <div class="px-6 py-4 border-t border-slate-200 flex justify-end bg-white/70 backdrop-blur rounded-b-xl">
        <button
          @click="closeModal"
      class="px-4 py-2 text-xs font-medium text-slate-700 bg-slate-100 border border-slate-300 rounded-md hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          Close
        </button>
      </div>
    </div>
    </div>

    <!-- Use existing VolunteerTaskLogOverlay component -->
    <VolunteerTaskLogOverlay
      :open="showTaskLogs"
      :campaignId="selectedCampaignForLogs?.id || null"
      @close="closeTaskLogs"
    />
  </div>
</template>
