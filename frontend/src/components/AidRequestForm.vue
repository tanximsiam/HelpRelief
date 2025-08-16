<script setup lang="ts">
<<<<<<< HEAD
import { ref, reactive, onMounted } from 'vue'
=======
import { reactive, computed } from 'vue'
import InputField from '@/components/InputField.vue'
>>>>>>> e4bd411 (Added a simple userdashboard and made AidReqform)
import PrimaryButton from '@/components/PrimaryButton.vue'
import RadioGroup from '@/components/RadioGroup.vue'
import { api } from '@/lib/api'

<<<<<<< HEAD
// ---- Types ----
interface Disaster { id: number; name: string; location: string }
interface FormState { disaster_id: string; aid_type: string | null; urgency: string | null; description: string }


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
const form = reactive<FormState>({ disaster_id: '', aid_type: null, urgency: null, description: '' })
const disasters = ref<Disaster[]>([])
const loadingDisasters = ref(false)
const submitting = ref(false)
const errors = reactive<Record<string,string>>({})
const successMessage = ref<string | null>(null)

const emit = defineEmits<{ (e: 'submit', payload: any): void }>()

// ---- Data loading ----
async function loadActiveDisasters() {
  loadingDisasters.value = true
  try {
    const { data } = await api.get('/disasters/active')
    disasters.value = data
    if (!form.disaster_id && disasters.value.length) form.disaster_id = String(disasters.value[0].id)
  } catch {
    errors.root = 'Failed to load active disasters'
  } finally {
    loadingDisasters.value = false
  }
}

onMounted(loadActiveDisasters)

// ---- Validation ----
function validate(): boolean {
  successMessage.value = null
  for (const k of Object.keys(errors)) delete errors[k]
  if (!form.disaster_id) errors.disaster_id = 'Select a disaster'
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
    const payload = { ...form, disaster_id: Number(form.disaster_id) }
    const { data } = await api.post('/submit-aid-requests', payload)
    emit('submit', data.aid_request)
    successMessage.value = 'Aid request submitted successfully.'
    form.aid_type = null
    form.urgency = null
    form.description = ''
=======
interface FormState {
  disaster_id: string
  location: string
  aid_type: string | null
  urgency: string | null
  description: string
}

const state = reactive<FormState>({
  disaster_id: '',
  location: '',
  aid_type: null,
  urgency: null,
  description: ''
})

const errors = reactive<Record<string,string>>({})

const loading = reactive({ submit: false })

const emit = defineEmits<{ (e: 'submitted', payload: any): void; (e: 'submit', payload: any): void }>()

// Basic front-end validation
function validate() {
  Object.keys(errors).forEach(k => delete errors[k])
  if (!state.disaster_id) errors.disaster_id = 'Required'
  if (!state.location) errors.location = 'Required'
  if (!state.aid_type) errors.aid_type = 'Select one'
  if (!state.urgency) errors.urgency = 'Select one'
  if (!state.description) errors.description = 'Required'
  return Object.keys(errors).length === 0
}

async function onSubmit() {
  if (!validate()) return
  loading.submit = true
  try {
    const payload = { ...state, disaster_id: Number(state.disaster_id) }
    const { data } = await api.post('/submit-aid-requests', payload)
    emit('submitted', data.aid_request)
    emit('submit', data.aid_request) // keep for compatibility with parent usage
    // reset
    state.disaster_id = ''
    state.location = ''
    state.aid_type = null
    state.urgency = null
    state.description = ''
>>>>>>> e4bd411 (Added a simple userdashboard and made AidReqform)
  } catch (e: any) {
    if (e.response?.status === 422) {
      const srv = e.response.data.errors || {}
      for (const k in srv) errors[k] = srv[k][0]
<<<<<<< HEAD
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
  <form class="space-y-8" @submit.prevent="submit">
    <div v-if="errors.root" class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ errors.root }}</div>
    <div v-if="successMessage" class="rounded-md bg-green-50 p-3 text-sm text-green-700">{{ successMessage }}</div>

    <div class="grid gap-8 md:grid-cols-2">
      <!-- Disaster selection -->
      <div class="md:col-span-2">
        <label for="disaster" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Active Disaster</label>
        <div class="relative">
          <select id="disaster" v-model="form.disaster_id" :disabled="loadingDisasters || !disasters.length" class="w-full rounded-md border border-slate-300 bg-white px-4 py-3 pr-10 text-base font-medium text-slate-800 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 disabled:cursor-not-allowed disabled:bg-slate-100">
            <option value="" disabled>Select active disaster</option>
            <option v-for="d in disasters" :key="d.id" :value="String(d.id)">{{ d.name }} – {{ d.location }}</option>
          </select>
          <span v-if="loadingDisasters" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 animate-spin text-slate-400">⏳</span>
        </div>
        <p v-if="!loadingDisasters && !disasters.length" class="mt-1 text-sm text-slate-500">No active disasters available.</p>
        <p v-if="errors.disaster_id" class="mt-1 text-sm text-red-600">{{ errors.disaster_id }}</p>
      </div>

      <!-- Aid Type -->
      <div>
        <p class="mb-2 text-lg font-semibold text-slate-800">Type of Aid</p>
        <div class="rounded-lg border border-slate-400 p-4">
          <RadioGroup v-model="form.aid_type" :options="aidTypeOptions" />
=======
    } else if (e.response?.data?.message) {
      errors.root = e.response.data.message
    } else {
      errors.root = 'Submission failed'
    }
  } finally {
    loading.submit = false
  }
}

const canSubmit = computed(() => !loading.submit)
</script>

<template>
  <form class="space-y-8" @submit.prevent="onSubmit">
    <div v-if="errors.root" class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ errors.root }}</div>

    <div class="grid gap-8 md:grid-cols-2">
      <!-- Disaster Context (disaster_id placeholder until we have list) -->
      <div class="md:col-span-2">
        <InputField label="Disaster Context" v-model="state.disaster_id" :error="errors.disaster_id" />
        <p class="mt-1 text-xs text-slate-500">Enter numeric disaster ID (placeholder until selector available).</p>
      </div>

      <!-- Location -->
      <div class="md:col-span-2">
        <InputField label="Location" v-model="state.location" :error="errors.location" />
      </div>

      <!-- Type of Aid -->
      <div>
        <p class="mb-2 text-lg font-semibold text-slate-800">Type of Aid</p>
        <div class="rounded-lg border border-slate-400 p-4">
          <RadioGroup v-model="state.aid_type" :options="[
            { label: 'Food', value: 'food' },
            { label: 'Financial', value: 'financial' },
            { label: 'Medical', value: 'medical' },
            { label: 'Physical', value: 'physical' }
          ]" />
>>>>>>> e4bd411 (Added a simple userdashboard and made AidReqform)
          <p v-if="errors.aid_type" class="mt-2 text-sm text-red-600">{{ errors.aid_type }}</p>
        </div>
      </div>

      <!-- Urgency -->
      <div>
        <p class="mb-2 text-lg font-semibold text-slate-800">Urgency</p>
        <div class="rounded-lg border border-slate-400 p-4">
<<<<<<< HEAD
          <RadioGroup inline v-model="form.urgency" :options="urgencyOptions" />
=======
          <RadioGroup inline v-model="state.urgency" :options="[
            { label: 'Low', value: 'low' },
            { label: 'Medium', value: 'medium' },
            { label: 'High', value: 'high' },
            { label: 'Critical', value: 'critical' }
          ]" />
>>>>>>> e4bd411 (Added a simple userdashboard and made AidReqform)
          <p v-if="errors.urgency" class="mt-2 text-sm text-red-600">{{ errors.urgency }}</p>
        </div>
      </div>

      <!-- Description -->
      <div class="md:col-span-2">
<<<<<<< HEAD
        <label for="description" class="mb-2 block text-lg font-semibold text-slate-800">Description</label>
        <textarea id="description" v-model="form.description" rows="4" placeholder="Provide request details" class="w-full rounded-md border border-slate-400 p-4 text-base font-medium text-slate-900 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20" />
=======
        <label class="mb-2 block text-lg font-semibold text-slate-800" for="description">Description</label>
        <textarea
          id="description"
          v-model="state.description"
          rows="4"
          placeholder="Provide request details"
          class="w-full rounded-md border border-slate-400 p-4 text-base font-medium text-slate-900 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
        />
>>>>>>> e4bd411 (Added a simple userdashboard and made AidReqform)
        <p v-if="errors.description" class="mt-2 text-sm text-red-600">{{ errors.description }}</p>
      </div>
    </div>

    <div class="flex justify-end">
<<<<<<< HEAD
      <PrimaryButton type="submit" :disabled="submitting || !form.disaster_id" variant="primary" class="px-8 py-3 text-lg min-w-[8rem]">
        <span v-if="!submitting">Submit</span>
=======
      <PrimaryButton type="submit" :disabled="!canSubmit" variant="primary" class="px-8 py-3 text-lg min-w-[8rem]" >
        <span v-if="!loading.submit">Submit</span>
>>>>>>> e4bd411 (Added a simple userdashboard and made AidReqform)
        <span v-else>Submitting...</span>
      </PrimaryButton>
    </div>
  </form>
</template>
