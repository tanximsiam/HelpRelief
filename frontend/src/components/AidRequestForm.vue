<script setup lang="ts">
import { reactive, computed } from 'vue'
import InputField from '@/components/InputField.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import RadioGroup from '@/components/RadioGroup.vue'
import { api } from '@/lib/api'

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
  } catch (e: any) {
    if (e.response?.status === 422) {
      const srv = e.response.data.errors || {}
      for (const k in srv) errors[k] = srv[k][0]
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
          <p v-if="errors.aid_type" class="mt-2 text-sm text-red-600">{{ errors.aid_type }}</p>
        </div>
      </div>

      <!-- Urgency -->
      <div>
        <p class="mb-2 text-lg font-semibold text-slate-800">Urgency</p>
        <div class="rounded-lg border border-slate-400 p-4">
          <RadioGroup inline v-model="state.urgency" :options="[
            { label: 'Low', value: 'low' },
            { label: 'Medium', value: 'medium' },
            { label: 'High', value: 'high' },
            { label: 'Critical', value: 'critical' }
          ]" />
          <p v-if="errors.urgency" class="mt-2 text-sm text-red-600">{{ errors.urgency }}</p>
        </div>
      </div>

      <!-- Description -->
      <div class="md:col-span-2">
        <label class="mb-2 block text-lg font-semibold text-slate-800" for="description">Description</label>
        <textarea
          id="description"
          v-model="state.description"
          rows="4"
          placeholder="Provide request details"
          class="w-full rounded-md border border-slate-400 p-4 text-base font-medium text-slate-900 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
        />
        <p v-if="errors.description" class="mt-2 text-sm text-red-600">{{ errors.description }}</p>
      </div>
    </div>

    <div class="flex justify-end">
      <PrimaryButton type="submit" :disabled="!canSubmit" variant="primary" class="px-8 py-3 text-lg min-w-[8rem]" >
        <span v-if="!loading.submit">Submit</span>
        <span v-else>Submitting...</span>
      </PrimaryButton>
    </div>
  </form>
</template>
