<script setup lang="ts">
import PrimaryButton from '@/components/PrimaryButton.vue'
import OngoingDisasters from '@/components/OngoingDisasters.vue'
import OngoingCampaigns from '@/components/OngoingCampaigns.vue'
import ProfileView from '@/components/ProfileView.vue'
import Modal from '@/components/Modal.vue'
import AidRequestForm from '@/components/AidRequestForm.vue'
import { useAuth } from '@/stores/auth'
import { computed, ref, onMounted } from 'vue'

const auth = useAuth()
const showAidRequestModal = ref(false)

onMounted(async () => {
  if (auth.token && !auth.user) {
    try { await auth.fetchUser() } catch (e) { console.error('fetchUser failed', e) }
  }
})

const userName = computed(() => auth.user?.name || 'User')

const openAidRequestModal = () => showAidRequestModal.value = true
const closeAidRequestModal = () => showAidRequestModal.value = false
const handleAidRequestSubmit = () => { alert('Aid request submitted successfully!'); closeAidRequestModal() }
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
        <div class="flex gap-6 items-center">
          <button @click="openAidRequestModal" class="text-2xl font-medium inline-flex items-center gap-1 transition-colors text-blue-600 hover:text-blue-700 underline underline-offset-4">Request for Aid</button>
          <PrimaryButton variant="primary" to="/offer-help" class="px-8 py-4 text-xl">Offer Help</PrimaryButton>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" style="height:600px;">
        <div class="lg:col-span-1" style="height:600px;">
          <ProfileView class="h-full" />
        </div>
        <div class="lg:col-span-1" style="height:600px;">
          <div class="space-y-6 h-full overflow-y-auto">
            <OngoingDisasters />
            <OngoingCampaigns />
          </div>
        </div>
      </div>
    </main>

    <Modal :show="showAidRequestModal" title="Aid Request" @close="closeAidRequestModal">
      <AidRequestForm @submit="handleAidRequestSubmit" />
    </Modal>
  </div>
</template>
