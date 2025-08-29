<script setup lang="ts">
import { ref, watch } from 'vue'
import { api } from '@/lib/api'
import Modal from '@/components/Modal.vue'

const props = defineProps<{ show: boolean }>()
const emit = defineEmits(['update:show'])

watch(() => props.show, (val) => {
  if (!val) success.value = false
})

const loading = ref(false)
const success = ref(false)
const error = ref('')

const form = ref({
  organization: '',
  contact_person: '',
  designation: '',
  email: '',
  phone: '',
  description: '',
  based_in: ''
})

const closeModal = () => {
  emit('update:show', false)
}

const submitForm = async () => {
  loading.value = true
  error.value = ''
  try {
    await api.post('/ngo-apply', form.value)
    success.value = true
    form.value = {
      organization: '',
      contact_person: '',
      designation: '',
      email: '',
      phone: '',
      description: '',
      based_in: ''
    }
    emit('update:show', false)
  } catch (e: unknown) {
    error.value = (e as any)?.response?.data?.message || 'Submission failed.'
  }
  loading.value = false
}
</script>

<template>
  <Modal :show="props.show" title="NGO Registration" @close="closeModal">
    <form @submit.prevent="submitForm" class="space-y-4">
      <div v-if="success" class="text-green-600 font-semibold mb-4">Application submitted successfully!</div>
      <div v-if="error" class="text-red-600 font-semibold mb-4">{{ error }}</div>
      <div class="mb-2">
        <label class="block font-medium mb-1">Organization Name <span class="text-red-600">*</span></label>
        <input v-model="form.organization" type="text" class="input" required />
      </div>
      <div class="mb-2">
        <label class="block font-medium mb-1">Contact Person <span class="text-red-600">*</span></label>
        <input v-model="form.contact_person" type="text" class="input" required />
      </div>
      <div class="mb-2">
        <label class="block font-medium mb-1">Designation <span class="text-red-600">*</span></label>
        <input v-model="form.designation" type="text" class="input" required />
      </div>
      <div class="mb-2">
        <label class="block font-medium mb-1">Email <span class="text-red-600">*</span></label>
        <input v-model="form.email" type="email" class="input" required />
      </div>
      <div class="mb-2">
        <label class="block font-medium mb-1">Phone</label>
        <input v-model="form.phone" type="text" class="input" />
      </div>
      <div class="mb-2">
        <label class="block font-medium mb-1">Description <span class="text-red-600">*</span></label>
        <textarea v-model="form.description" class="input" rows="2" required></textarea>
      </div>
      <div class="mb-2">
        <label class="block font-medium mb-1">Based In <span class="text-red-600">*</span></label>
        <input v-model="form.based_in" type="text" class="input" required />
      </div>
      <button type="submit" class="primary-btn w-full mt-4" :disabled="loading || !form.organization || !form.contact_person || !form.designation || !form.email || !form.description || !form.based_in">
        <span v-if="loading">Submitting...</span>
        <span v-else>Submit Application</span>
      </button>
    </form>
  </Modal>
</template>

<style scoped>
.input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #cbd5e1;
  border-radius: 0.375rem;
  font-size: 1rem;
}
.primary-btn {
  background: #2563eb;
  color: white;
  padding: 0.75rem;
  border-radius: 0.375rem;
  font-weight: 600;
  transition: background 0.2s;
}
.primary-btn:disabled {
  background: #93c5fd;
  cursor: not-allowed;
}
.primary-btn:hover:not(:disabled) {
  background: #1d4ed8;
}
</style>
