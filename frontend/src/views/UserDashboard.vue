<script setup lang="ts">
import PrimaryButton from '@/components/PrimaryButton.vue';
import OngoingDisasters from '@/components/OngoingDisasters.vue';
import OngoingCampaigns from '@/components/OngoingCampaigns.vue';
import CampaignMap from '@/components/CampaignMap.vue';
import ProfileView from '@/components/ProfileView.vue';
import { useAuth } from '@/stores/auth';
import { computed, onMounted, ref } from 'vue';
import Modal from '@/components/Modal.vue';
import AidRequestForm from '@/components/AidRequestForm.vue';
import { api } from '@/lib/api';

// Get authenticated user data
const auth = useAuth();

// Modal state
const showAidRequestModal = ref(false);

// NGO Staff state
const isNgoStaff = ref(false);
const ngoId = ref<number | null>(null);

// Volunteer state
const isVolunteer = ref(false);

// Fetch user data and check NGO staff status when component mounts
onMounted(async () => {
  if (auth.token && !auth.user) {
    try {
      await auth.fetchUser();
    } catch (error) {
      console.error('Failed to fetch user:', error);
    }
  }

  // Check if user is NGO staff
  try {
    const staffRes = await api.get('/ngo-staff');
    const staffData = staffRes.data;
    if (staffData.role === 'ngo_staff' && staffData.ngo_id) {
      isNgoStaff.value = true;
      ngoId.value = staffData.ngo_id;
    }
  } catch (error) {
    console.log('User is not NGO staff or error checking status:', error);
    isNgoStaff.value = false;
  }

  // Check if user is a volunteer by attempting to fetch volunteer campaigns
  try {
    await api.get('/campaigns/volunteer');
    isVolunteer.value = true;
  } catch (e:any) {
    isVolunteer.value = false;
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

</script>

<template>
  <div class="min-h-screen">
    <!-- Main Content -->
    <main class="flex flex-col px-8 py-16">
      <!-- Welcome Section -->
      <div class="flex items-center justify-between mb-10">
        <div>
          <h1 class="text-4xl font-bold text-black-800">
            Welcome {{ userName }},
            <span class="text-2xl font-normal">people are depending on you.</span>
          </h1>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-6 items-center">
          <button
            v-if="isVolunteer"
            @click="openAidRequestModal"
            class="text-2xl font-medium inline-flex items-center gap-1 transition-colors text-blue-600 hover:text-blue-700 underline underline-offset-4"
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
      </div>

      <!-- Dashboard Content -->
      <div class="grid grid-cols-1 gap-6" :class="isNgoStaff ? 'lg:grid-cols-3' : 'lg:grid-cols-2'" style="height: 600px;">
        <!-- Left Column - Profile View -->
        <div class="lg:col-span-1" style="height: 600px;">
          <ProfileView class="h-full" />
        </div>

        <!-- Middle Column - Live Feeds -->
        <div class="lg:col-span-1" style="height: 600px;">
          <div class="space-y-6 h-full overflow-y-auto">
            <!-- Ongoing Disasters Component -->
            <OngoingDisasters />

            <!-- Ongoing Campaigns Component -->
            <OngoingCampaigns />
          </div>
        </div>

        <!-- Right Column - Campaign Map (NGO Staff Only) -->
        <div v-if="isNgoStaff" class="lg:col-span-1" style="height: 600px;">
          <CampaignMap class="h-full" />
        </div>
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
