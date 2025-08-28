<template>
  <div class="max-w-2xl mx-auto mt-8 p-6 bg-white shadow rounded">
    <h2 class="text-2xl font-bold mb-4">
      {{ props.aidRequestId ? 'Create Task from Aid Request' : 'Create Standalone Task' }}
    </h2>

    <form @submit.prevent="submitTask">
      <div v-if="props.aidRequestId" class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Linked Aid Request ID</label>
        <input type="text" :value="props.aidRequestId" class="w-full border rounded p-2 bg-gray-100" disabled />
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Campaign</label>
        <input type="text" :value="campaign?.name || 'Loading...'" class="w-full border rounded p-2 bg-gray-100" disabled />
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Location</label>
        <input v-model="form.location" class="w-full border rounded p-2" required />
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Start Time</label>
        <input type="datetime-local" v-model="form.start_time" class="w-full border rounded p-2" required />
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">End Time</label>
        <input type="datetime-local" v-model="form.end_time" class="w-full border rounded p-2" required />
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Aid Type</label>
        <template v-if="props.aidRequestId">
          <input type="text" :value="form.aid_type" class="w-full border rounded p-2 bg-gray-100" disabled />
        </template>
        <template v-else>
          <select v-model="form.aid_type" class="w-full border rounded p-2" required>
            <option value="financial">Financial</option>
            <option value="medical">Medical</option>
            <option value="resource">Resource</option>
          </select>
        </template>
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Urgency</label>
        <template v-if="props.aidRequestId">
          <input type="text" :value="form.urgency" class="w-full border rounded p-2 bg-gray-100" disabled />
        </template>
        <template v-else>
          <select v-model="form.urgency" class="w-full border rounded p-2" required>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>
        </template>
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Description</label>
        <textarea v-model="form.description" rows="3" class="w-full border rounded p-2" :readonly="!!props.aidRequestId" required></textarea>
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Assign Volunteer</label>
        <select v-model="form.volunteer_id" required class="w-full border rounded p-2">
          <option v-for="vol in volunteers" :value="vol.id" :key="vol.id">{{ vol.name }}</option>
        </select>
      </div>

      <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Create Task
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '@/lib/api'

const props = defineProps<{ campaignId: number, aidRequestId?: number, campaign?: { id: number; name: string } }>()
const router = useRouter()
const campaign = ref<{ id: number; name: string } | null>(props.campaign ?? null)
const form = ref({
  campaign_id: props.campaignId,
  location: '',
  start_time: '',
  end_time: '',
  aid_type: '',
  urgency: 'medium',
  description: '',
  volunteer_id: ''
})

interface Volunteer {
  id: number;
  name: string;
  // add other relevant fields as needed
}
const volunteers = ref<Volunteer[]>([])

const fetchCampaign = async () => {
  if (campaign.value) return
  try {
    const res = await api.get(`/campaigns/${props.campaignId}`)
    campaign.value = res.data
  } catch (err) {
    campaign.value = { id: props.campaignId, name: 'Unknown Campaign' }
  }
}

const fetchVolunteers = async () => {
  const params: Record<string, unknown> = {
    campaign_id: props.campaignId,
    available_only: true
  }
  const res = await api.get('/volunteers', { params })
  volunteers.value = res.data
}

const submitTask = async () => {
  const payload = {
    ...form.value,
    campaign_id: props.campaignId
  }
  try {
    if (props.aidRequestId) {
      await api.post(`/aid-requests/${props.aidRequestId}/assign`, {
        volunteer_id: payload.volunteer_id,
        start_time: payload.start_time,
        end_time: payload.end_time,
        location: payload.location,
        campaign_id: payload.campaign_id
      })
      alert('Task created and aid request assigned successfully!')
    } else {
      await api.post('/tasks/standalone', {
        ...payload,
        aid_request_id: null
      })
      alert('Standalone task created successfully!')
    }
    router.push('/dashboard')
  } catch (err) {
    console.error('Error creating task:', err)
    alert('Something went wrong. See console.')
  }
}

onMounted(() => {
  fetchCampaign()
  fetchVolunteers()
  if (props.aidRequestId) {
    // Pre-fill from query if available
    const query = router.currentRoute.value.query
    if (query.aid_request_aid_type) form.value.aid_type = String(query.aid_request_aid_type)
    if (query.aid_request_urgency) form.value.urgency = String(query.aid_request_urgency)
    if (query.aid_request_description) form.value.description = String(query.aid_request_description)
  }
})
watch(() => props.campaign, (newVal) => {
  if (newVal) campaign.value = newVal
})
</script>
