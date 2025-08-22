<script setup lang="ts">
import { ref, watch, nextTick, computed } from 'vue'
import { api } from '@/lib/api'

interface DisasterOption { id:number; name:string; location?:string; disaster_type?:string }

const props = defineProps<{ show: boolean }>()
const emit = defineEmits<{ (e:'close'):void; (e:'created', payload:any):void }>()

const disasters = ref<DisasterOption[]>([])
const existingCampaignDisasterIds = ref<Set<number>>(new Set())
const loading = ref(false)
const submitting = ref(false)
const form = ref({ disaster_id: '', help_needed: 'medium', status: 'active' })
const fieldErrors = ref<Record<string,string>>({})
const genericError = ref<string | null>(null)
const successMsg = ref<string | null>(null)
const firstSelectRef = ref<HTMLSelectElement | null>(null)

async function loadDisastersAndExisting() {
  loading.value = true
  try {
    // Load disasters
    const [disastersRes, campaignsRes] = await Promise.all([
      api.get('/disasters/active'),
      api.get('/campaigns/my').catch(()=>({ data: [] }))
    ])
    disasters.value = disastersRes.data
    const ids = new Set<number>()
    if (Array.isArray(campaignsRes.data)) {
      for (const c of campaignsRes.data) {
        if (c.disaster_id) ids.add(Number(c.disaster_id))
      }
    }
    existingCampaignDisasterIds.value = ids
    // Pick first available disaster not already registered
    const firstAvailable = disasters.value.find(d => !ids.has(d.id))
    form.value.disaster_id = firstAvailable ? String(firstAvailable.id) : ''
  } catch (e:any) {
    genericError.value = 'Failed to load data'
  } finally { loading.value = false }
}

const availableDisasters = computed(() => disasters.value)
const allTaken = computed(() => availableDisasters.value.length > 0 && availableDisasters.value.every(d => existingCampaignDisasterIds.value.has(d.id)))

watch(() => props.show, async (open) => {
  if (open) {
    reset()
  await loadDisastersAndExisting()
    await nextTick(); firstSelectRef.value?.focus()
  }
})

function reset() {
  form.value = { disaster_id: '', help_needed: 'medium', status: 'active' }
  fieldErrors.value = {}; genericError.value = null; successMsg.value = null
}

function validate() {
  const errs: Record<string,string> = {}
  if (!form.value.disaster_id) errs.disaster_id = 'Select a disaster'
  else if (existingCampaignDisasterIds.value.has(Number(form.value.disaster_id))) errs.disaster_id = 'Already registered for this disaster'
  if (!['low','medium','high'].includes(form.value.help_needed)) errs.help_needed = 'Invalid'
  if (!['active','pending'].includes(form.value.status)) errs.status = 'Invalid'
  fieldErrors.value = errs
  return !Object.keys(errs).length
}

async function submit() {
  if (submitting.value || !validate()) return
  genericError.value = null; successMsg.value = null
  try {
    submitting.value = true
    const payload = { disaster_id: Number(form.value.disaster_id), help_needed: form.value.help_needed, status: form.value.status }
    const { data } = await api.post('/campaigns', payload)
    emit('created', data)
    successMsg.value = 'Campaign registered.'
    setTimeout(() => emit('close'), 1100)
  } catch (e:any) {
    const resp = e?.response?.data
    if (resp?.errors) {
      const errs: Record<string,string> = {}
      for (const [k,v] of Object.entries(resp.errors)) {
        errs[k] = Array.isArray(v) ? String(v[0]) : String(v)
      }
      fieldErrors.value = errs
    } else {
      genericError.value = resp?.error || resp?.message || 'Failed to register campaign'
    }
  } finally { submitting.value = false }
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white w-full max-w-lg rounded-lg shadow-xl p-6 relative" role="dialog" aria-modal="true" aria-labelledby="registerCampaignHeading">
      <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700" @click="emit('close')" aria-label="Close">✕</button>
      <h2 id="registerCampaignHeading" class="text-2xl font-semibold mb-4">Register Campaign</h2>

      <form @submit.prevent="submit" class="space-y-5" novalidate>
        <div>
          <label for="campaign-disaster" class="block text-sm font-medium mb-1">Disaster</label>
          <select id="campaign-disaster" ref="firstSelectRef" v-model="form.disaster_id" :aria-invalid="!!fieldErrors.disaster_id" class="w-full border rounded px-3 py-2" :disabled="allTaken || loading">
            <option value="" disabled>Select a disaster</option>
            <option v-for="d in availableDisasters" :key="d.id" :value="d.id" :disabled="existingCampaignDisasterIds.has(d.id)">
              {{ d.name }} ({{ d.location }})
              <span v-if="existingCampaignDisasterIds.has(d.id)"> — already registered</span>
            </option>
          </select>
          <p v-if="fieldErrors.disaster_id" class="text-xs text-red-600 mt-1">{{ fieldErrors.disaster_id }}</p>
          <p v-else-if="allTaken" class="text-xs text-yellow-600 mt-1">All active disasters already have a campaign for your NGO.</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1" for="campaign-help">Help Needed</label>
            <select id="campaign-help" v-model="form.help_needed" :aria-invalid="!!fieldErrors.help_needed" class="w-full border rounded px-3 py-2">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
            <p v-if="fieldErrors.help_needed" class="text-xs text-red-600 mt-1">{{ fieldErrors.help_needed }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1" for="campaign-status">Status</label>
            <select id="campaign-status" v-model="form.status" :aria-invalid="!!fieldErrors.status" class="w-full border rounded px-3 py-2">
              <option value="active">Active</option>
              <option value="pending">Pending</option>
            </select>
            <p v-if="fieldErrors.status" class="text-xs text-red-600 mt-1">{{ fieldErrors.status }}</p>
          </div>
        </div>

        <div class="space-y-1">
          <p v-if="genericError" class="text-sm text-red-600">{{ genericError }}</p>
          <p v-if="successMsg" class="text-sm text-green-600">{{ successMsg }}</p>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="emit('close')" class="px-4 py-2 text-sm rounded border">Cancel</button>
          <button type="submit" :disabled="submitting || loading || allTaken" class="px-5 py-2 text-sm rounded text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
            <span v-if="submitting" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full" />
            <span>{{ submitting ? 'Registering...' : 'Register Campaign' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
