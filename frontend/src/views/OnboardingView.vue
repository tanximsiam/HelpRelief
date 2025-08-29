<template>
  <div class="p-8">
    <h1 class="text-3xl font-bold mb-6">NGO Onboarding Links</h1>
    <div v-if="loading" class="mb-4">Loading...</div>
    <table v-if="!loading && links.filter(l => !l.is_primary).length" class="w-full mb-8 border border-gray-300 rounded-lg shadow">
      <thead>
        <tr class="bg-blue-100 text-blue-900">
          <th class="px-4 py-3 text-left">Link</th>
          <th class="px-4 py-3 text-left">Usage Limit</th>
          <th class="px-4 py-3 text-left">Used</th>
          <th class="px-4 py-3 text-left">Remaining</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="l in links.filter(l => !l.is_primary)" :key="l.id" class="hover:bg-blue-50 transition">
          <td class="px-4 py-2 break-all"><a :href="l.link" target="_blank" class="text-blue-600 underline">{{ l.link }}</a></td>
          <td class="px-4 py-2">{{ l.usage_limit }}</td>
          <td class="px-4 py-2">{{ l.used_count }}</td>
          <td class="px-4 py-2">{{ l.usage_limit - l.used_count }}</td>
        </tr>
      </tbody>
    </table>
    <div v-else-if="!loading">No links found.</div>

    <div class="mt-12 max-w-xl mx-auto bg-white rounded-lg shadow p-6 border border-gray-200">
      <h2 class="text-xl font-semibold mb-4 text-blue-700">GENERATE ONBOARDING LINKS</h2>
      <form class="flex flex-col gap-4" @submit.prevent="generateLink">
        <div class="flex flex-col">
          <label class="block text-sm font-medium mb-1 text-gray-700">Privilege Role</label>
          <select v-model="form.privilege_role" class="border rounded px-3 py-2 h-10 focus:ring focus:ring-blue-200">
            <option value="ngo_admin">Admin</option>
            <option value="general_staff">Staff</option>
            <option value="manager">Manager</option>
          </select>
        </div>
        <div class="flex flex-col">
          <label class="block text-sm font-medium mb-1 text-gray-700">Usage Limit</label>
          <input type="number" v-model.number="form.usage_limit" min="1" class="border rounded px-3 py-2 w-32 h-10 focus:ring focus:ring-blue-200" />
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-semibold hover:bg-blue-700 transition">Generate</button>
      </form>
      <div v-if="error" class="text-red-600 mt-2">{{ error }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '@/lib/api'
import { useAuth } from '@/stores/auth'

const auth = useAuth()
const links = ref<any[]>([])
const loading = ref(true)
const error = ref('')
const form = ref({ privilege_role: 'general_staff', usage_limit: 1 })

async function fetchLinks() {
  loading.value = true
  try {
    const { data } = await api.get('/ngo-invite-links')
    links.value = data
  } catch (e) {
    error.value = 'Failed to fetch links.'
  } finally {
    loading.value = false
  }
}

async function generateLink() {
  error.value = ''
  try {
    await api.post('/ngo-invite-links', {
      ...form.value,
      ngo_id: auth.user?.ngo_id
    })
    await fetchLinks()
  } catch (e) {
    error.value = 'Failed to generate link.'
  }
}

onMounted(fetchLinks)
</script>
