<script setup lang="ts">
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { useAuth } from '@/stores/auth'
import PrimaryButton from '@/components/PrimaryButton.vue'
import RadioGroup from '@/components/RadioGroup.vue'
import { api } from '@/lib/api'

interface FormState { campaign_id: string; ngo_id: string; skills: string }
interface Campaign { id: number; name: string; ngo_id?: number }
interface Ngo { id: number; name: string }

const form = reactive<FormState>({ campaign_id: '', ngo_id: '', skills: '' })
const campaigns = ref<Campaign[]>([])
const ngos = ref<Ngo[]>([])
const loading = ref(false)
const submitting = ref(false)
const errors = reactive<Record<string,string>>({})
const successMessage = ref<string | null>(null)

const auth = useAuth()
const isVolunteer = ref(false)

const emit = defineEmits<{ (e: 'submit', payload: any): void }>()

async function loadLookups() {
  loading.value = true
  try {
    const [cResp, nResp] = await Promise.all([
      api.get('/campaigns'),
      api.get('/ngos')
    ])
  campaigns.value = cResp.data || []
  ngos.value = nResp.data || []
  // default NGO first (so campaign list can be filtered)
  if (!form.ngo_id && ngos.value.length) form.ngo_id = String(ngos.value[0].id)
  // pick a default campaign that belongs to the selected NGO (if any)
  const initialList = campaigns.value.filter(c => String(c.ngo_id) === String(form.ngo_id))
  if (!form.campaign_id && initialList.length) form.campaign_id = String(initialList[0].id)
  } catch (e) {
    errors.root = 'Failed to load campaigns or NGOs.'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (auth.token && !auth.user) {
    try { await auth.fetchUser() } catch (e) { console.error('fetchUser failed', e) }
  }
  isVolunteer.value = !!auth.user?.volunteer
  await loadLookups()
})

// Only show campaigns that belong to the selected NGO; when no NGO is selected, show all
const filteredCampaigns = computed(() => {
  if (!form.ngo_id) return campaigns.value
  return campaigns.value.filter(c => String(c.ngo_id) === String(form.ngo_id))
})

// Keep campaign selection in sync when NGO changes
watch(() => form.ngo_id, (next) => {
  if (!next) {
    form.campaign_id = ''
    return
  }
  const list = filteredCampaigns.value
  form.campaign_id = list.length ? String(list[0].id) : ''
})

function validate() {
  successMessage.value = null
  for (const k of Object.keys(errors)) delete errors[k]
  if (!form.campaign_id) errors.campaign_id = 'Select a campaign'
  if (!form.ngo_id) errors.ngo_id = 'Select an NGO'
  return Object.keys(errors).length === 0
}

async function submit() {
  if (!validate()) return
  submitting.value = true
  try {
    const payload = { campaign_id: Number(form.campaign_id), ngo_id: Number(form.ngo_id), skills: form.skills }
    const { data } = await api.post('/volunteer-registrations', payload)
    emit('submit', data)
    successMessage.value = 'Volunteer registration submitted.'
    form.skills = ''
  } catch (e: any) {
    if (e.response?.status === 422) {
      const srv = e.response.data.errors || {}
      for (const k in srv) errors[k] = srv[k][0]
    } else {
      errors.root = e.response?.data?.message || 'Submission failed.'
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div>
    <div v-if="isVolunteer" class="p-6">
      <h3 class="text-xl font-semibold">You are already registered as a volunteer</h3>
      <p class="mt-2 text-sm text-slate-600">Thank you for volunteering — no further action is required.</p>
    </div>

    <form v-else class="space-y-6" @submit.prevent="submit">
      <div v-if="errors.root" class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ errors.root }}</div>
      <div v-if="successMessage" class="rounded-md bg-green-50 p-3 text-sm text-green-700">{{ successMessage }}</div>

      <div>
        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Select NGO</label>
        <select v-model="form.ngo_id" :disabled="loading || !ngos.length" class="w-full rounded-md border p-3">
          <option value="" disabled>Select an NGO</option>
          <option v-for="n in ngos" :key="n.id" :value="String(n.id)">{{ n.name }}</option>
        </select>
        <p v-if="errors.ngo_id" class="mt-1 text-sm text-red-600">{{ errors.ngo_id }}</p>
      </div>

      <div>
        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Select campaign</label>
        <select v-model="form.campaign_id" :disabled="loading || !filteredCampaigns.length" class="w-full rounded-md border p-3">
          <option value="" disabled>Select a campaign</option>
          <option v-for="c in filteredCampaigns" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
        </select>
        <p v-if="!loading && !filteredCampaigns.length" class="mt-1 text-sm text-slate-500">No campaigns available for the selected NGO.</p>
        <p v-if="errors.campaign_id" class="mt-1 text-sm text-red-600">{{ errors.campaign_id }}</p>
      </div>

    <div>
      <label class="block text-lg font-semibold mb-2">Skills (optional)</label>
      <textarea v-model="form.skills" rows="3" class="w-full rounded-md border p-3" placeholder="List relevant skills or experience"></textarea>
    </div>

      <div class="flex justify-end">
      <PrimaryButton type="submit" :disabled="submitting" variant="primary" class="px-8 py-3 min-w-[8rem]">
        <span v-if="!submitting">Register</span>
        <span v-else>Submitting...</span>
      </PrimaryButton>
    </div>
    </form>
  </div>
</template>
