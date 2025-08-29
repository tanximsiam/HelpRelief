<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useAuth } from '@/stores/auth'
import PrimaryButton from '@/components/PrimaryButton.vue'
import RadioGroup from '@/components/RadioGroup.vue'
import { api } from '@/lib/api'

// ---- Types ----
interface Campaign { id: number; name: string; disaster_id: number; disaster_location: string; ngo_id: number; ngo_name: string }
interface FormState { campaign_id: string; aid_type: string | null; urgency: string | null; description: string }

// ---- Static option sets (must match backend enums) ----
const aidTypeOptions = [
  { label: 'Financial', value: 'financial' },
  { label: 'Medical', value: 'medical' },
  { label: 'Resources / Supplies', value: 'resource' },
]
const urgencyOptions = [
  { label: 'Low', value: 'low' },
  { label: 'Medium', value: 'medium' },
  { label: 'High', value: 'high' },
  { label: 'Critical', value: 'critical' },
]

// ---- Reactive state ----
const form = reactive<FormState>({ campaign_id: '', aid_type: null, urgency: null, description: '' })
const campaign = ref<Campaign | null>(null)
const loadingCampaigns = ref(false)
const volunteerOnlyError = ref<string | null>(null)
// Auth-based optimistic volunteer flag to avoid initial flicker
const auth = useAuth()
// Start with known auth volunteer flag (if loaded) to prevent UI flash; will be confirmed by API
const isVolunteer = ref<boolean>(!!auth.user?.volunteer)
// Track when we've finished the verification request
const volunteerCheckDone = ref(false)
const submitting = ref(false)
const errors = reactive<Record<string,string>>({})
const successMessage = ref<string | null>(null)

const emit = defineEmits<{ (e: 'submit', payload: any): void; (e:'open-volunteer-registration'): void }>()

// ---- Data loading ----
async function loadVolunteerCampaigns() {
  loadingCampaigns.value = true
  try {
    const { data } = await api.get('/campaigns/volunteer')
    isVolunteer.value = true
    // Pick the first campaign (assuming only one per volunteer)
    if (Array.isArray(data) && data.length) {
      campaign.value = data[0]
      form.campaign_id = String(data[0].id)
    } else {
      volunteerOnlyError.value = 'No active campaign found for your volunteer membership.'
    }
  } catch (e: any) {
    if (e.response?.status === 403) {
      volunteerOnlyError.value = 'Only active volunteers can submit aid requests.'
      isVolunteer.value = false
    } else {
      errors.root = 'Failed to load campaign'
    }
  } finally {
    loadingCampaigns.value = false
    volunteerCheckDone.value = true
  }
}

onMounted(loadVolunteerCampaigns)

// ---- Validation ----
function validate(): boolean {
  successMessage.value = null
  for (const k of Object.keys(errors)) delete errors[k]
  if (!form.campaign_id) errors.campaign_id = 'Select a campaign'
  if (!form.aid_type) errors.aid_type = 'Select an aid type'
  if (!form.urgency) errors.urgency = 'Select urgency'
  if (!form.description) errors.description = 'Provide description'
  return Object.keys(errors).length === 0
}

// ---- Submit ----
async function submit() {
  if (!validate()) return
  submitting.value = true
  try {
    const selected = campaign.value
    if (!selected) {
      errors.campaign_id = 'No campaign found for your volunteer membership.'
      submitting.value = false
      return
    }
    const payload = { campaign_id: selected.id, aid_type: form.aid_type, urgency: form.urgency, description: form.description }
    const { data } = await api.post('/submit-aid-requests', payload)
    emit('submit', data.aid_request)
    successMessage.value = 'Aid request submitted successfully.'
    form.aid_type = null
    form.urgency = null
    form.description = ''
  } catch (e: any) {
    if (e.response?.status === 422) {
      const srv = e.response.data.errors || {}
      for (const k in srv) errors[k] = srv[k][0]
    } else if (e.response?.status === 403) {
      errors.root = e.response.data.message || 'Not authorized to submit (volunteer required).'
    } else {
      errors.root = e.response?.data?.message || 'Submission failed'
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <!-- Show volunteer registration prompt only after check completes and user isn't volunteer -->
  <div v-if="volunteerCheckDone && !isVolunteer" class="p-6">
    <h3 class="text-xl font-semibold">Volunteer registration required</h3>
    <p class="mt-2 text-sm text-slate-600">You must register as a volunteer before submitting aid requests.</p>
    <div class="mt-6 flex justify-end">
      <button type="button" @click="emit('open-volunteer-registration')" class="inline-flex items-center gap-1 rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Register as Volunteer</button>
    </div>
  </div>
  <form v-else-if="isVolunteer" class="space-y-8" @submit.prevent="submit">
    <div v-if="errors.root" class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ errors.root }}</div>
    <div v-if="successMessage" class="rounded-md bg-green-50 p-3 text-sm text-green-700">{{ successMessage }}</div>

  <div class="grid gap-8 md:grid-cols-2" v-if="!loadingCampaigns">
      <!-- Campaign info (read-only) -->
      <div class="md:col-span-2">
        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Your Campaign</label>
        <div class="rounded-md border p-3 bg-slate-50 text-base font-medium text-slate-800">
          <span v-if="campaign">{{ campaign.name }} – {{ campaign.disaster_location }} ({{ campaign.ngo_name }})</span>
          <span v-else>No campaign found.</span>
        </div>
      </div>

      <!-- Aid Type -->
      <div>
        <p class="mb-2 text-lg font-semibold text-slate-800">Type of Aid</p>
        <div class="rounded-lg border border-slate-400 p-4">
          <RadioGroup v-model="form.aid_type" :options="aidTypeOptions" />
          <p v-if="errors.aid_type" class="mt-2 text-sm text-red-600">{{ errors.aid_type }}</p>
        </div>
      </div>

      <!-- Urgency -->
      <div>
        <p class="mb-2 text-lg font-semibold text-slate-800">Urgency</p>
        <div class="rounded-lg border border-slate-400 p-4">
          <RadioGroup inline v-model="form.urgency" :options="urgencyOptions" />
          <p v-if="errors.urgency" class="mt-2 text-sm text-red-600">{{ errors.urgency }}</p>
        </div>
      </div>

      <!-- Description -->
      <div class="md:col-span-2">
        <label for="description" class="mb-2 block text-lg font-semibold text-slate-800">Description</label>
        <textarea id="description" v-model="form.description" rows="4" placeholder="Provide request details" class="w-full rounded-md border border-slate-400 p-4 text-base font-medium text-slate-900 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20" />
        <p v-if="errors.description" class="mt-2 text-sm text-red-600">{{ errors.description }}</p>
      </div>
    </div>

    <div class="flex justify-end" v-if="!loadingCampaigns">
      <PrimaryButton type="submit" :disabled="submitting || !form.campaign_id" variant="primary" class="px-8 py-3 text-lg min-w-[8rem]">
        <span v-if="!submitting">Submit</span>
        <span v-else>Submitting...</span>
      </PrimaryButton>
    </div>
    <div v-else class="text-center text-sm text-slate-500">Loading your campaign...</div>
  </form>
  <!-- While verifying volunteer status show nothing (avoid flicker) -->
</template>
