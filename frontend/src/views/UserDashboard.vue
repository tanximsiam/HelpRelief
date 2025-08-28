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
  </div>
</template>
