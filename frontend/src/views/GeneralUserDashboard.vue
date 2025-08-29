<script setup lang="ts">
import PrimaryButton from '@/components/PrimaryButton.vue'
import OngoingDisasters from '@/components/OngoingDisasters.vue'
import OngoingCampaigns from '@/components/OngoingCampaigns.vue'
import TaskActivityComponent from '../components/TaskActivityComponent.vue'
import Modal from '@/components/Modal.vue'
import AidRequestForm from '@/components/AidRequestForm.vue'
import VolunteerRegistrationForm from '@/components/VolunteerRegistrationForm.vue'
import AidSupportOverlay from '@/components/AidSupportOverlay.vue'
import { useAuth } from '@/stores/auth'
import { useVolunteerTaskStore } from '@/stores/volunteerTask'
import { api } from '@/lib/api'
import { computed, ref, onMounted } from 'vue'

const auth = useAuth()
const volunteerTaskStore = useVolunteerTaskStore()
const showAidRequestModal = ref(false)
const showAidSupport = ref(false)
const showVolunteerModal = ref(false)
const resignError = ref('')
const resignSuccess = ref('')

onMounted(async () => {
  if (auth.token && !auth.user) {
    try { await auth.fetchUser() } catch (e) { console.error('fetchUser failed', e) }
  }
  if (auth.user && auth.user.volunteer) {
    await volunteerTaskStore.fetchActiveTask(auth.user.id)
  }
})

const userName = computed(() => auth.user?.name || 'User')

const openAidRequestModal = () => showAidRequestModal.value = true
const closeAidRequestModal = () => showAidRequestModal.value = false
const handleAidRequestSubmit = () => { alert('Aid request submitted successfully!'); closeAidRequestModal() }
const openVolunteerModal = () => showVolunteerModal.value = true
const closeVolunteerModal = () => showVolunteerModal.value = false
const handleVolunteerSubmit = () => {
  alert('Volunteer registration submitted successfully!')
  if (auth.user) auth.user.volunteer = true
  closeVolunteerModal()
}
const openAidSupport = () => showAidSupport.value = true
const closeAidSupport = () => showAidSupport.value = false

async function handleResignVolunteer() {
  resignError.value = ''
  resignSuccess.value = ''
  try {
    const res = await api.post('/volunteer/resign')
    if (res.data && res.data.message) {
      resignSuccess.value = res.data.message
      if (auth.user) {
        auth.user.volunteer = false
      }
      volunteerTaskStore.hasActiveTask = false
    }
  } catch (err: any) {
    const resp = (err as any)?.response
    if (resp?.data?.error) {
      resignError.value = resp.data.error
    } else {
      resignError.value = 'Failed to resign. Please try again.'
    }
  }
}
</script>

<template>
  <div class="min-h-screen">
    <main class="flex flex-col px-8 py-16">
      <div class="flex items-center justify-between mb-10">
        <div>
          <h1 class="text-4xl font-bold text-black-800">
            Welcome {{ userName }},
            <span class="text-2xl font-normal">people are depending on you.</span>
          </h1>
        </div>
        <div class="flex gap-4 items-center">
          <button
            v-if="auth.user && auth.user.volunteer"
            @click="openAidRequestModal"
            class="text-sm font-medium inline-flex items-center gap-1 transition-colors text-blue-600 hover:text-blue-700 underline underline-offset-4"
          >Request Aid</button>
          <button
            v-if="auth.user && !auth.user.volunteer"
            @click="openVolunteerModal"
            class="text-sm font-medium inline-flex items-center gap-1 transition-colors text-blue-600 hover:text-blue-700 underline underline-offset-4"
          >Volunteer Registration</button>
          <button
            v-if="auth.user && auth.user.volunteer"
            @click="handleResignVolunteer"
            class="text-sm font-medium inline-flex items-center gap-1 transition-colors text-red-600 hover:text-red-700 underline underline-offset-4"
          >Resign as Volunteer</button>
          <PrimaryButton
            variant="primary"
            @click="openAidSupport"
            class="px-5 py-2 text-sm font-medium"
          >Offer Help</PrimaryButton>
          <Modal :show="!!resignError || !!resignSuccess" title="Volunteer Resignation" @close="() => { resignError = ''; resignSuccess = '' }">
            <div v-if="resignError" class="text-red-600 text-lg">{{ resignError }}</div>
            <div v-if="resignSuccess" class="text-green-600 text-lg">{{ resignSuccess }}</div>
          </Modal>
        </div>
      </div>

      <div class="space-y-10">
        <!-- Volunteer task panel (full width) -->
        <TaskActivityComponent v-if="auth.user && auth.user.volunteer" />
        <!-- Two-column section: disasters & campaigns -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
          <OngoingDisasters />
          <OngoingCampaigns />
        </div>
      </div>
    </main>

    <Modal :show="showAidRequestModal" title="Aid Request" @close="closeAidRequestModal">
      <AidRequestForm @submit="handleAidRequestSubmit" @open-volunteer-registration="closeAidRequestModal(); openVolunteerModal()" />
    </Modal>
    <Modal :show="showVolunteerModal" title="Volunteer registration" @close="closeVolunteerModal">
      <VolunteerRegistrationForm @submit="handleVolunteerSubmit" />
    </Modal>
  <AidSupportOverlay :show="showAidSupport" @close="closeAidSupport" />
  </div>
</template>
