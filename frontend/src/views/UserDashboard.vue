<script setup lang="ts">
import PrimaryButton from '@/components/PrimaryButton.vue';
import { useAuth } from '@/stores/auth';
import { computed, onMounted, ref } from 'vue';
import Modal from '@/components/Modal.vue';
import AidRequestForm from '@/components/AidRequestForm.vue';
import { api } from "@/lib/api";

// Get authenticated user data
const auth = useAuth();

// Modal state
const showAidRequestModal = ref(false);

// Fetch user data when component mounts if we have a token but no user
onMounted(async () => {
  if (auth.token && !auth.user) {
    try {
      await auth.fetchUser();
    } catch (error) {
      console.error('Failed to fetch user:', error);
    }
  }
});

const userName = computed(() => {
  if (auth.user?.name) {
    return auth.user.name;
  }
  return 'User';
});

function openAidRequestModal() {
  showAidRequestModal.value = true;
}

function closeAidRequestModal() {
  showAidRequestModal.value = false;
}

function handleAidRequestSubmit() {
  // Show success message and close modal
  alert('Aid request submitted successfully!');
  closeAidRequestModal();
}

////////////////////////////////////////////////////
interface Disaster {
  id: number;
  name: string;
  type: string;
  location: string;
  severity: string;
  status: string;
}

interface Campaign {
  id: number;
  name: string;
  disaster_id: number;
  ngo_name: string;
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



// ... (other imports and interfaces remain the same)

const disasters = ref<Disaster[]>([]);
const campaigns = ref<Campaign[]>([]);
const aggregateReports = ref<VolunteerAggregate[]>([]);
const individualReports = ref<VolunteerIndividual[]>([]);
const selectedDisasterId = ref<number | null>(null);
const successMessage = ref<string>('');
const errorMessage = ref<string>('');
const userRole = ref<string>(''); // 'general' or 'ngo_staff'
const isNgoStaff = ref(false); // New flag
const ngoId = ref<number | null>(null); // New NGO ID

// Fetch user role and dashboard data on mount
onMounted(async () => {

    // Fetch NGO staff status to determine role and ngo_id
    const staffRes = await api.get('/ngo-staff');
    const staffData = staffRes.data;
    if (staffData.role === 'ngo_staff' && staffData.ngo_id) {
      isNgoStaff.value = true;
      ngoId.value = staffData.ngo_id;
    } else {
      isNgoStaff.value = false;
      ngoId.value = null;
    }

    // Fetch active disasters
    const disasterRes = await api.get('/active-disasters');
    disasters.value = disasterRes.data;

    // Fetch campaigns based on NGO staff status and ngo_id
    let campaignEndpoint = '/campaigns';
    if (isNgoStaff.value && ngoId.value) {
      campaignEndpoint = `/campaigns/my?ngo_id=${ngoId.value}`;
    }
    const campaignRes = await api.get(campaignEndpoint);
    campaigns.value = Array.isArray(campaignRes.data) ? campaignRes.data : [];

});

// ... (getStatusColor and viewReports remain the same)

// Helper to get status color
const getStatusColor = (severity: string) => {
  if (severity === 'high') return 'bg-red-500 text-white';
  if (severity === 'medium') return 'bg-yellow-500 text-black';
  return 'bg-green-500 text-white';
};

// View reports for a specific disaster
const viewReports = (disasterId: number) => {
  selectedDisasterId.value = disasterId;
};

</script>

<template>
  <div class="min-h-screen">
    <!-- Main Content -->
    <main class="flex items-center justify-between px-8 py-16">
      <!-- Welcome Section -->
      <div>
        <h1 class="text-4xl font-bold text-black-800">
          Welcome {{ userName }},
          <span class="text-2xl font-normal">people are depending on you.</span>
        </h1>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-6 items-center">
        <button
          @click="openAidRequestModal"
          class="text-2xl font-medium inline-flex items-center gap-1 transition-colors
                 text-blue-600 hover:text-blue-700 underline underline-offset-4"
        >
          Request for Aid
        </button>

        <PrimaryButton
          variant="primary"
          to="/offer-help"
          class="px-8 py-4 text-xl"
        >
          Offer Help
        </PrimaryButton>
      </div>
    </main>

    <!-- Aid Request Modal -->
    <Modal
      :show="showAidRequestModal"
      title="Aid Request"
      @close="closeAidRequestModal"
    >
      <AidRequestForm @submit="handleAidRequestSubmit" />
    </Modal>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Live Disaster Feed -->
      <div class="bg-white p-4 rounded shadow">
        <h3 class="text-xl font-semibold mb-4">Live Disaster Feed</h3>
        <ul class="space-y-2">
          <li v-for="disaster in disasters" :key="disaster.id" class="p-2 border-b">
            <div class="flex justify-between">
              <span>{{ disaster.name }} ({{ disaster.type }} in {{ disaster.location }})</span>
              <span :class="getStatusColor(disaster.severity)" class="px-2 py-1 rounded">
                {{ (disaster.severity?.charAt(0).toUpperCase() || 'Urgent') + (disaster.severity?.slice(1) || '') }} help required
              </span>
            </div>
          </li>
        </ul>
        <a href="#" class="text-blue-500 mt-2 inline-block">View More</a>
      </div>

      <!-- Ongoing Campaigns -->
      <div class="bg-white p-4 rounded shadow">
        <h3 class="text-xl font-semibold mb-4">Ongoing Campaigns</h3>
        <div v-if="isLoading" class="text-center text-gray-500">Loading campaigns...</div>
        <div v-else-if="errorMessage" class="text-center text-red-500">{{ errorMessage }}</div>
        <ul class="space-y-2" v-else-if="campaigns.length">
          <li v-for="campaign in campaigns" :key="campaign.id" class="p-2 border-b">
            <div class="flex justify-between">
              <span>{{ campaign.name }} (by {{ campaign.ngo_name || 'Unknown NGO' }})</span>
              <span v-if="isNgoStaff" class="text-blue-500 cursor-pointer" @click="viewReports(campaign.disaster_id)"> > </span>
              <span v-else class="text-blue-500"> > </span>
            </div>
          </li>
        </ul>
        <p v-else class="text-gray-500">No ongoing campaigns found.</p>
        <a href="#" class="text-blue-500 mt-2 inline-block">View More</a>
      </div>
    </div>

    <!-- Volunteer Reports (Only for NGO Staff) -->
    <VolunteerReportView
        v-if="isNgoStaff"
        :is-ngo-staff="isNgoStaff"
        :campaigns="campaigns"
        @update:errorMessage="errorMessage = $event"
    />
    <!-- Success/Error Messages -->
    <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mt-4">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mt-4">
      {{ errorMessage }}
    </div>
  </div>
</template>
