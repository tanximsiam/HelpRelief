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
        <label class="block text-sm text-gray-700 font-semibold">Disaster</label>
        <select v-model="form.disaster_id" required class="w-full border rounded p-2">
          <option v-for="disaster in disasters" :value="disaster.id" :key="disaster.id">
            {{ disaster.name }}
          </option>
        </select>
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-700 font-semibold">Task Type</label>
        <input v-model="form.task_type" class="w-full border rounded p-2" required />
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
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '@/lib/api'

const route = useRoute()
const router = useRouter()

const aidRequestId = route.query.aid_request_id ?? null

const disasters = ref<any[]>([])
const volunteers = ref<any[]>([])

const form = ref({
  disaster_id: '',
  task_type: '',
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

const fetchVolunteers = async () => {
  const res = await api.get('/volunteers')
  volunteers.value = res.data
}

const submitTask = async () => {
  const payload = {
    ...form.value,
    status: 'pending'
  }

  try {
    if (aidRequestId) {
      await api.post(`/aid-requests/${aidRequestId}/assign`, {
        volunteer_id: payload.volunteer_id,
        start_time: payload.start_time,
        end_time: payload.end_time,
        location: payload.location
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
  fetchVolunteers()
})
</script>
