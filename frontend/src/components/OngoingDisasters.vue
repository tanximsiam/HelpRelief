<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { api } from '@/lib/api'
import Modal from '@/components/Modal.vue'

interface Disaster {
  id: number;
  name: string;
  type: string;
  location: string;
  severity: string;
  status: string;
}

const disasters = ref<Disaster[]>([]);
const isNgoStaff = ref(false);
const ngoId = ref<number | null>(null);
const showAllDisastersModal = ref(false);

// Compute the first 3 disasters to show on the dashboard
const topDisasters = computed(() => {
  return disasters.value.slice(0, 3);
});

// Helper to get status color
const getStatusColor = (severity: string) => {
  if (severity === 'high') return 'bg-red-500 text-white';
  if (severity === 'medium') return 'bg-yellow-500 text-black';
  return 'bg-green-500 text-white';
};

// Fetch user role and dashboard data on mount
onMounted(async () => {
  try {
    // Fetch NGO staff status to determine ngo_id
    const staffRes = await api.get('/ngo-staff');
    const staffData = staffRes.data;
    if (staffData.ngo_id) {
      isNgoStaff.value = true;
      ngoId.value = staffData.ngo_id;
    } else {
      isNgoStaff.value = false;
      ngoId.value = null;
    }

    // Fetch active disasters
    const disasterRes = await api.get('/disasters/active');
    disasters.value = disasterRes.data;
  } catch (error) {
    console.error('Failed to load disasters:', error);
  }
});

// Open and close modal functions
const openAllDisastersModal = () => {
  showAllDisastersModal.value = true;
};

const closeAllDisastersModal = () => {
  showAllDisastersModal.value = false;
};
</script>

<template>
  <div class="bg-white p-4 rounded shadow">
    <h3 class="text-xl font-semibold mb-4">Live Disaster Feed</h3>

    <!-- Show only top 3 disasters -->
    <ul class="space-y-2">
      <li v-for="disaster in topDisasters" :key="disaster.id" class="p-2 border-b">
        <div class="flex justify-between">
          <span>{{ disaster.name }} ({{ disaster.type }} in {{ disaster.location }})</span>
          <span :class="getStatusColor(disaster.severity)" class="px-2 py-1 rounded text-sm">
            {{ (disaster.severity?.charAt(0).toUpperCase() || 'Urgent') + (disaster.severity?.slice(1) || '') }} help required
          </span>
        </div>
      </li>
    </ul>

    <!-- See more button -->
    <button
      @click="openAllDisastersModal"
      class="text-blue-500 mt-4 inline-block hover:underline"
    >
      See More
    </button>
  </div>

  <!-- Modal for showing all disasters -->
  <Modal
    :show="showAllDisastersModal"
    title="All Active Disasters"
    @close="closeAllDisastersModal"
  >
    <div class="max-h-[70vh] overflow-y-auto">
      <ul class="space-y-3">
        <li v-for="disaster in disasters" :key="disaster.id" class="p-3 border rounded hover:bg-gray-50">
          <div class="flex flex-col sm:flex-row sm:justify-between gap-2">
            <div>
              <h4 class="font-medium">{{ disaster.name }}</h4>
              <p class="text-gray-600">{{ disaster.type }} in {{ disaster.location }}</p>
            </div>
            <div class="flex items-center">
              <span :class="getStatusColor(disaster.severity)" class="px-3 py-1 rounded text-sm whitespace-nowrap">
                {{ (disaster.severity?.charAt(0).toUpperCase() || 'Urgent') + (disaster.severity?.slice(1) || '') }} help required
              </span>
            </div>
          </div>
          <div class="mt-2 flex justify-end">
            <a :href="`/disasters/${disaster.id}`" class="text-blue-500 text-sm hover:underline">View Details</a>
          </div>
        </li>
      </ul>
    </div>
  </Modal>
</template>
