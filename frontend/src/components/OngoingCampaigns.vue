<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { api } from '@/lib/api'
import CampaignListModal from '@/components/CampaignListModal.vue'

interface Campaign {
  id: number;
  name: string;
  disaster_id: number;
  disaster_name: string;
  ngo_name: string;
  help_needed: 'low' | 'medium' | 'high';
  status: string;
}

const campaigns = ref<Campaign[]>([]);
const isLoading = ref(true);
const errorMessage = ref<string>('');
const isNgoStaff = ref(false);
const ngoId = ref<number | null>(null);
const showCampaignListModal = ref(false);

// Computed property for top 3 priority campaigns
const topPriorityCampaigns = computed(() => {
  const priorityOrder = { 'high': 3, 'medium': 2, 'low': 1 };
  return [...campaigns.value]
    .sort((a, b) => priorityOrder[b.help_needed] - priorityOrder[a.help_needed])
    .slice(0, 3);
});

async function loadCampaigns() {
  isLoading.value = true;
  try {
    // Fetch NGO staff status
    const staffRes = await api.get('/ngo-staff');
    const staffData = staffRes.data;
    if (staffData?.ngo_id) {
      isNgoStaff.value = true;
      ngoId.value = staffData.ngo_id;
    } else {
      isNgoStaff.value = false;
      ngoId.value = null;
    }
  } catch (error) {
    isNgoStaff.value = false;
  }
  try {
    let endpoint = '/campaigns';
    if (isNgoStaff.value && ngoId.value) endpoint = '/campaigns/my';
    const campaignRes = await api.get(endpoint);
    campaigns.value = Array.isArray(campaignRes.data) ? campaignRes.data : [];
    errorMessage.value = '';
  } catch (e) {
    errorMessage.value = 'Failed to load campaigns';
  } finally {
    isLoading.value = false;
  }
}

function appendCampaign(c: Campaign) {
  // Avoid duplicates
  if (!campaigns.value.find(x => x.id === c.id)) {
    campaigns.value.unshift(c as any);
  }
}

onMounted(loadCampaigns);

defineExpose({ refresh: loadCampaigns, append: appendCampaign });

// Function to open campaign list modal
const openCampaignList = () => {
  showCampaignListModal.value = true;
};

// Function to close campaign list modal
const closeCampaignList = () => {
  showCampaignListModal.value = false;
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
</script>

<template>
  <div class="bg-white p-4 rounded shadow">
    <h3 class="text-xl font-semibold mb-4">Ongoing Campaigns</h3>
    <div v-if="isLoading" class="text-center text-gray-500">Loading campaigns...</div>
    <div v-else-if="errorMessage" class="text-center text-red-500">{{ errorMessage }}</div>
    <div v-else-if="topPriorityCampaigns.length">
      <ul class="space-y-3">
        <li v-for="campaign in topPriorityCampaigns" :key="campaign.id" class="p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <h4 class="font-medium text-gray-900">{{ campaign.name }}</h4>
                <span
                  :class="['px-2 py-1 text-xs rounded-full', getPriorityColor(campaign.help_needed)]"
                >
                  {{ campaign.help_needed.toUpperCase() }}
                </span>
              </div>
              <p class="text-sm text-gray-600">by {{ campaign.ngo_name || 'Unknown NGO' }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ campaign.disaster_name }}</p>
            </div>
          </div>
        </li>
      </ul>
      <button
        @click="openCampaignList"
        class="text-blue-500 hover:text-blue-700 mt-4 inline-block font-medium"
      >
        View More
      </button>
    </div>
    <p v-else class="text-gray-500">No ongoing campaigns found.</p>

    <!-- Campaign List Modal -->
    <CampaignListModal
      v-if="showCampaignListModal"
      :campaigns="campaigns"
      :is-ngo-staff="isNgoStaff"
      @close="closeCampaignList"
    />
  </div>
</template>
