<script setup lang="ts">
import PrimaryButton from '@/components/PrimaryButton.vue';
import { useAuth } from '@/stores/auth';
import { computed, onMounted, ref } from 'vue';
import Modal from '@/components/Modal.vue';
import AidRequestForm from '@/components/AidRequestForm.vue';

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
  </div>
</template>
