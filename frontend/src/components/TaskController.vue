<template>
  <div class="max-w-2xl mx-auto mt-8 p-6 bg-white shadow rounded">
    <h2 class="text-2xl font-bold mb-4">
      {{ aidRequestId ? 'Create Task from Aid Request' : 'Create Standalone Task' }}
    </h2>

    <form @submit.prevent="submitTask">
      <div v-if="aidRequestId" class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Linked Aid Request ID</label>
        <input type="text" :value="aidRequestId" class="w-full border rounded p-2 bg-gray-100" disabled />
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Campaign</label>
        <select v-model="form.campaign_id" required class="w-full border rounded p-2">
          <option v-for="campaign in campaigns" :value="campaign.id" :key="campaign.id">
            {{ campaign.name }}
          </option>
        </select>
      </div>

  <!-- task_type removed from form - field intentionally omitted -->

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
        <select v-model="form.aid_type" class="w-full border rounded p-2" required>
          <option value="financial">Financial</option>
          <option value="medical">Medical</option>
          <option value="resource">Resource</option>
        </select>
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Urgency</label>
        <select v-model="form.urgency" class="w-full border rounded p-2" required>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Description</label>
        <textarea v-model="form.description" rows="3" class="w-full border rounded p-2" required></textarea>
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
import { useRoute, useRouter } from 'vue-router'
import { api } from '@/lib/api'

const route = useRoute()
const router = useRouter()

const aidRequestId = route.query.aid_request_id ?? null
const aidRequestData = ref<any | null>(null)

const disasters = ref<any[]>([])
const campaigns = ref<any[]>([])
const volunteers = ref<any[]>([])

  const form = ref({
  disaster_id: '',
  campaign_id: '',
  location: '',
  start_time: '',
  end_time: '',
  aid_type: '',
  urgency: 'medium',
  description: '',
  volunteer_id: ''
})

const fetchDisasters = async () => {
  const res = await api.get('/active-disasters')
  disasters.value = res.data
}

const fetchCampaigns = async () => {
  const res = await api.get('/campaigns')
  campaigns.value = res.data
}

const fetchVolunteers = async (campaignId: string | null = null) => {
  const params: any = {}
  if (campaignId) params.campaign_id = campaignId

  const res = await api.get('/volunteers', { params })
  volunteers.value = res.data
}

const fetchAidRequest = async (id: string) => {
  try {
    const res = await api.get(`/aid-requests/${id}`)
    aidRequestData.value = res.data

    // populate form with authoritative aid-request data
    form.value.campaign_id = res.data.campaign_id ?? ''
    form.value.aid_type = res.data.aid_type ?? form.value.aid_type
    form.value.urgency = res.data.urgency ?? form.value.urgency
    form.value.description = res.data.description ?? form.value.description
  } catch (err) {
    console.error('Error fetching aid request:', err)
  }
}

const submitTask = async () => {
  const payload = {
    ...form.value,
    status: 'pending',
    // support legacy 'disaster_id' if present
    campaign_id: form.value.campaign_id || (form.value as any).disaster_id || null
  }

  try {
    if (aidRequestId) {
      await api.post(`/aid-requests/${aidRequestId}/assign`, {

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
  fetchDisasters()
  fetchCampaigns()
  if (aidRequestId) {
    fetchAidRequest(String(aidRequestId)).then(() => {
      fetchVolunteers(form.value.campaign_id || null)
    })
  } else {
    fetchVolunteers()
  }
})

watch(() => form.value.campaign_id, (newVal) => {
  if (newVal) fetchVolunteers(newVal)
})
</script>
