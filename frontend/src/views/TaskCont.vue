<template>
  <div class="mx-auto max-w-3xl space-y-8">
    <h1 class="text-2xl font-bold my-6">{{ campaign?.name || 'Campaign' }}</h1>
    <TaskCreatorPanel :campaign-id="props.campaignId" @created="onCreated" />

    <!-- <h2 class="text-xl font-bold mt-6 mb-2">Pending Aid Requests</h2>
    <div v-if="pendingRequests.length === 0" class="text-gray-500">No pending requests.</div>
    <div v-for="request in pendingRequests" :key="request.id" class="border rounded-lg p-4 mb-4 shadow-sm bg-white">

    </div>

    <h2 class="text-xl font-bold mt-10 mb-2">Accepted Aid Requests</h2>
    <div v-if="acceptedRequests.length === 0" class="text-gray-500">No accepted requests yet.</div>
    <div v-for="request in acceptedRequests" :key="request.id" class="border rounded-lg p-4 mb-4 shadow-sm bg-gray-100">

    </div> -->
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import TaskCreatorPanel from '@/components/TaskCreatorPanel.vue'
import { api } from '@/lib/api'

const props = defineProps<{ campaignId: number }>()
const campaign = ref<any>(null)
const pendingRequests = ref([])
const acceptedRequests = ref([])

onMounted(async () => {
  // Fetch campaign info
  const campaignRes = await api.get(`/campaigns/${props.campaignId}`)
  campaign.value = campaignRes.data
  // Fetch tasks/requests for this campaign
  const pendingRes = await api.get(`/aid-requests?campaign_id=${props.campaignId}&status=pending`)
  pendingRequests.value = pendingRes.data
  const acceptedRes = await api.get(`/aid-requests?campaign_id=${props.campaignId}&status=accepted`)
  acceptedRequests.value = acceptedRes.data
})

function onCreated(e:any){ /* toast or refresh */ }

</script>
