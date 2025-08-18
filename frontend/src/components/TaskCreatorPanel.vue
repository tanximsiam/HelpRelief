<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-xl font-bold">Pending Aid Requests</h2>
      <RouterLink to="/tasks/create">
        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
          + Create Standalone Task
        </button>
      </RouterLink>
    </div>

    <div v-if="pendingRequests.length === 0" class="text-gray-500">No pending requests.</div>

    <div
      v-for="request in pendingRequests"
      :key="request.id"
      class="border rounded-lg p-4 mb-4 shadow-sm bg-white"
    >
      <p><strong>ID:</strong> {{ request.id }}</p>
      <p><strong>Disaster:</strong> {{ request.disaster?.name || 'N/A' }}</p>
      <p><strong>Type:</strong> {{ request.aid_type }}</p>
      <p><strong>Urgency:</strong> {{ request.urgency }}</p>
      <p><strong>Description:</strong> {{ request.description }}</p>
      <p><strong>Requester:</strong> {{ request.requester?.name || 'Unknown' }}</p>
      <p><strong>NGO:</strong> {{ request.requester?.volunteer_registration?.ngo?.name || 'N/A' }}</p>
      <p><strong>Skills:</strong> {{ request.requester?.volunteer_registration?.skills || 'Not specified' }}</p>

      <div class="mt-3 flex gap-2">
        <button
          class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
          @click="handleAccept(request)"
        >
          Accept
        </button>
        <button
          class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
          @click="openRejectModal(request)"
        >
          Reject
        </button>
      </div>
    </div>

    <h2 class="text-xl font-bold mt-10 mb-4">Accepted Aid Requests</h2>
    <div v-if="acceptedRequests.length === 0" class="text-gray-500">No accepted requests yet.</div>

    <div
      v-for="request in acceptedRequests"
      :key="request.id"
      class="border rounded-lg p-4 mb-4 shadow-sm bg-gray-100"
    >
      <p><strong>ID:</strong> {{ request.id }}</p>
      <p><strong>Disaster:</strong> {{ request.disaster?.name || 'N/A' }}</p>
      <p><strong>Type:</strong> {{ request.aid_type }}</p>
      <p><strong>Urgency:</strong> {{ request.urgency }}</p>
      <p><strong>Description:</strong> {{ request.description }}</p>
      <p><strong>Requester:</strong> {{ request.requester?.name || 'Unknown' }}</p>
      <p><strong>NGO:</strong> {{ request.requester?.volunteer_registration?.ngo?.name || 'N/A' }}</p>
      <p><strong>Skills:</strong> {{ request.requester?.volunteer_registration?.skills || 'Not specified' }}</p>
    </div>

    <!-- Reject Modal -->
    <div
      v-if="showRejectModal"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
    >
      <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-bold mb-4">Reject Request</h3>
        <textarea
          v-model="rejectionRemarks"
          class="w-full border rounded p-2 mb-4"
          placeholder="Enter remarks"
          rows="4"
        ></textarea>
        <div class="flex justify-end gap-2">
          <button class="px-4 py-2 bg-gray-300 rounded" @click="closeRejectModal">Cancel</button>
          <button class="px-4 py-2 bg-red-500 text-white rounded" @click="submitRejection">Reject</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { api } from '@/lib/api'

const router = useRouter()

const pendingRequests = ref<any[]>([])
const acceptedRequests = ref<any[]>([])

const showRejectModal = ref(false)
const currentRejectId = ref<number | null>(null)
const rejectionRemarks = ref('')

const fetchAidRequests = async () => {
  try {
    const res = await api.get('/aid-requests')
    pendingRequests.value = res.data.filter((r: any) => r.status === 'pending')
    acceptedRequests.value = res.data.filter((r: any) => r.status === 'assigned')
  } catch (err) {
    console.error('Error fetching aid requests:', err)
  }
}

const handleAccept = async (request: any) => {
  if (request.aid_type === 'financial') {
    try {
      await api.post(`/aid-requests/${request.id}/assign`)
      request.status = 'assigned'
      acceptedRequests.value.push(request)
      pendingRequests.value = pendingRequests.value.filter(r => r.id !== request.id)
    } catch (err) {
      console.error('Accept error:', err)
    }
  } else {
    router.push({
      name: 'TaskCreate',
      query: { aid_request_id: request.id }
    })
  }
}

const openRejectModal = (request: any) => {
  currentRejectId.value = request.id
  rejectionRemarks.value = ''
  showRejectModal.value = true
}

const closeRejectModal = () => {
  showRejectModal.value = false
  currentRejectId.value = null
}

const submitRejection = async () => {
  if (!rejectionRemarks.value || !currentRejectId.value) return

  try {
    await api.post(`/aid-requests/${currentRejectId.value}/reject`, {
      remarks: rejectionRemarks.value
    })
    pendingRequests.value = pendingRequests.value.filter(r => r.id !== currentRejectId.value)
    closeRejectModal()
  } catch (err) {
    console.error('Rejection error:', err)
  }
}

onMounted(fetchAidRequests)
</script>
