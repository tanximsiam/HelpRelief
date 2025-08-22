<script setup lang="ts">
import PrimaryButton from '@/components/PrimaryButton.vue'
import OngoingDisasters from '@/components/OngoingDisasters.vue'
import OngoingCampaigns from '@/components/OngoingCampaigns.vue'
import CampaignMap from '@/components/CampaignMap.vue'
import ReportDisasterModal from '@/components/ReportDisasterModal.vue'
import ProfileView from '@/components/ProfileView.vue'
import { useAuth } from '@/stores/auth'
import { computed, ref, onMounted } from 'vue'
import { api } from '@/lib/api'

const auth = useAuth()
// Aid request removed per new requirements
// const showAidRequestModal = ref(false)
const showReportDisaster = ref(false)
const disastersRef = ref<any>(null)
const userName = computed(() => auth.user?.name || 'User')

// NGO staff info
const ngoId = ref<number | null>(null)

onMounted(async () => {
  if (auth.token && !auth.user) {
    try { await auth.fetchUser() } catch (e) { console.error('fetchUser failed', e) }
  }
  try {
    const { data } = await api.get('/ngo-staff')
    const payload = (data && typeof data === 'object' && 'data' in data) ? (data as any).data : data
    ngoId.value = (payload as any)?.ngo_id ?? (payload as any)?.ngoId ?? null
  } catch (e) {
    console.warn('Failed to fetch ngo staff details', e)
  }
})

// Aid request handlers removed
const openReportDisaster = () => showReportDisaster.value = true
const closeReportDisaster = () => showReportDisaster.value = false
const handleDisasterCreated = () => {
  closeReportDisaster()
  disastersRef.value?.refresh?.()
}
// const handleAidRequestSubmit = () => {}
</script>

<template>
  <div class="min-h-screen">
    <main class="flex flex-col px-8 py-16">
      <div class="flex items-center justify-between mb-10">
        <div>
          <h1 class="text-4xl font-bold text-black-800">
            Welcome {{ userName }}<span v-if="ngoId" class="text-lg font-normal ml-2 text-gray-500">(NGO ID: {{ ngoId }})</span>,
            <span class="text-2xl font-normal">your impact extends across regions.</span>
          </h1>
        </div>
        <div class="flex gap-4 items-center">
          <!-- Report Disaster trigger -->
          <button
            type="button"
            @click="openReportDisaster"
            class="text-sm font-medium inline-flex items-center gap-1 transition-colors text-blue-600 hover:text-blue-700 underline underline-offset-4"
          >
            Report a disaster
          </button>
          <PrimaryButton
            variant="primary"
            to="/register-campaign"
            class="px-5 py-2 text-sm font-medium"
          >
            Register Campaign
          </PrimaryButton>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" style="height:600px;">
        <div class="lg:col-span-1" style="height:600px;">
          <ProfileView class="h-full" />
        </div>
        <div class="lg:col-span-1" style="height:600px;">
          <div class="space-y-6 h-full overflow-y-auto">
            <OngoingDisasters ref="disastersRef" />
            <OngoingCampaigns />
          </div>
        </div>
        <div class="lg:col-span-1" style="height:600px;">
          <CampaignMap class="h-full" />
        </div>
      </div>
    </main>
  <ReportDisasterModal :show="showReportDisaster" @close="closeReportDisaster" @created="handleDisasterCreated" />
  </div>
</template>
