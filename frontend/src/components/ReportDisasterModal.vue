<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'
import { api } from '@/lib/api'

interface DisasterForm {
  name: string
  disaster_type: string
  location: string
  start_date: string
  severity: string
  description: string
}

const props = defineProps<{ show: boolean }>()
const emit = defineEmits<{
  (e:'close'): void
  (e:'created', payload: any): void
}>()

// Constants
const DIVISIONS = Object.freeze([
  'Dhaka','Chattogram','Rajshahi','Khulna','Barishal','Sylhet','Rangpur'
])
const DISASTER_TYPES = Object.freeze(['flood','earthquake','storm','wildfire','drought','other'])
const SEVERITIES = Object.freeze(['low','medium','high'])

// Reactive state
const form = ref<DisasterForm>(defaultForm())
const loading = ref(false)
const successMsg = ref<string | null>(null)
const genericError = ref<string | null>(null)
const fieldErrors = ref<Record<string,string>>({})
const firstFieldRef = ref<HTMLInputElement | null>(null)

function defaultForm(): DisasterForm {
  return { name:'', disaster_type: DISASTER_TYPES[0], location: DIVISIONS[0], start_date:'', severity: SEVERITIES[0], description:'' }
}

function resetForm() {
  form.value = defaultForm()
  fieldErrors.value = {}
  genericError.value = null
}

watch(() => props.show, async (open) => {
  if (open) {
    resetForm()
    await nextTick()
    firstFieldRef.value?.focus()
  } else {
    successMsg.value = null
  }
})

function validateLocal(): boolean {
  const errs: Record<string,string> = {}
  if (!form.value.name.trim()) errs.name = 'Name is required'
  if (!form.value.start_date) errs.start_date = 'Start date required'
  if (!DISASTER_TYPES.includes(form.value.disaster_type)) errs.disaster_type = 'Invalid type'
  if (!SEVERITIES.includes(form.value.severity)) errs.severity = 'Invalid severity'
  if (!DIVISIONS.includes(form.value.location)) errs.location = 'Select a valid division'
  fieldErrors.value = errs
  return Object.keys(errs).length === 0
}

async function submit() {
  if (loading.value) return
  genericError.value = null
  successMsg.value = null
  if (!validateLocal()) return
  try {
    loading.value = true
    // Prefer canonical endpoint; fallback handled server-side if alias removed
    const { data } = await api.post('/disasters', form.value).catch(async err => {
      // Retry legacy path if 404
      if (err?.response?.status === 404) {
        return await api.post('/disasters/store', form.value)
      }
      throw err
    })
    emit('created', data)
    successMsg.value = 'Disaster reported successfully.'
    const closeDelay = 1100
    setTimeout(() => { emit('close') }, closeDelay)
  } catch (e: any) {
    const resp = e?.response?.data
    if (resp?.errors && typeof resp.errors === 'object') {
      const fe: Record<string,string> = {}
      for (const [k,v] of Object.entries(resp.errors)) {
        if (Array.isArray(v) && v.length) fe[k] = String(v[0])
        else if (typeof v === 'string') fe[k] = v
      }
      fieldErrors.value = fe
      if (!Object.keys(fe).length) genericError.value = resp.message || 'Submission failed'
    } else {
      genericError.value = resp?.message || 'Submission failed'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white w-full max-w-xl rounded-lg shadow-xl p-6 relative" role="dialog" aria-modal="true" aria-labelledby="reportDisasterHeading">
      <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700" @click="emit('close')" aria-label="Close">✕</button>
      <h2 id="reportDisasterHeading" class="text-2xl font-semibold mb-4">Report a Disaster</h2>
      <form @submit.prevent="submit" class="space-y-5" novalidate>
        <div>
          <label class="block text-sm font-medium mb-1" for="disaster-name">Name</label>
          <input ref="firstFieldRef" id="disaster-name" v-model="form.name" type="text" :aria-invalid="!!fieldErrors.name" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400" />
          <p v-if="fieldErrors.name" class="text-xs text-red-600 mt-1">{{ fieldErrors.name }}</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="disaster-type" class="block text-sm font-medium mb-1">Type</label>
            <select id="disaster-type" v-model="form.disaster_type" :aria-invalid="!!fieldErrors.disaster_type" class="w-full border rounded px-3 py-2">
              <option v-for="t in DISASTER_TYPES" :key="t" :value="t">{{ t.charAt(0).toUpperCase()+t.slice(1) }}</option>
            </select>
            <p v-if="fieldErrors.disaster_type" class="text-xs text-red-600 mt-1">{{ fieldErrors.disaster_type }}</p>
          </div>
          <div>
            <label for="disaster-severity" class="block text-sm font-medium mb-1">Severity</label>
            <select id="disaster-severity" v-model="form.severity" :aria-invalid="!!fieldErrors.severity" class="w-full border rounded px-3 py-2">
              <option v-for="s in SEVERITIES" :key="s" :value="s">{{ s.charAt(0).toUpperCase()+s.slice(1) }}</option>
            </select>
            <p v-if="fieldErrors.severity" class="text-xs text-red-600 mt-1">{{ fieldErrors.severity }}</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="disaster-division" class="block text-sm font-medium mb-1">Division</label>
            <select id="disaster-division" v-model="form.location" :aria-invalid="!!fieldErrors.location" class="w-full border rounded px-3 py-2">
              <option v-for="d in DIVISIONS" :key="d" :value="d">{{ d }}</option>
            </select>
            <p v-if="fieldErrors.location" class="text-xs text-red-600 mt-1">{{ fieldErrors.location }}</p>
          </div>
          <div>
            <label for="disaster-start" class="block text-sm font-medium mb-1">Start Date</label>
            <input id="disaster-start" v-model="form.start_date" type="date" :aria-invalid="!!fieldErrors.start_date" class="w-full border rounded px-3 py-2" />
            <p v-if="fieldErrors.start_date" class="text-xs text-red-600 mt-1">{{ fieldErrors.start_date }}</p>
          </div>
        </div>
        <div>
          <label for="disaster-desc" class="block text-sm font-medium mb-1">Description <span class="text-xs text-gray-400">(optional)</span></label>
          <textarea id="disaster-desc" v-model="form.description" rows="3" class="w-full border rounded px-3 py-2 resize-y" />
        </div>
        <div class="space-y-1">
          <p v-if="genericError" class="text-sm text-red-600">{{ genericError }}</p>
          <p v-if="successMsg" class="text-sm text-green-600">{{ successMsg }}</p>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="emit('close')" class="px-4 py-2 text-sm rounded border">Cancel</button>
          <button type="submit" :disabled="loading" class="px-5 py-2 text-sm rounded text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
            <span v-if="loading" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full" />
            <span>{{ loading ? 'Submitting...' : 'Submit Report' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
