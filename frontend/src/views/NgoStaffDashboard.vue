<script setup lang="ts">
import PrimaryButton from '@/components/PrimaryButton.vue'
import OngoingDisasters from '@/components/OngoingDisasters.vue'
import OngoingCampaigns from '@/components/OngoingCampaigns.vue'
import CampaignMap from '@/components/CampaignMap.vue'
import ReportDisasterModal from '@/components/ReportDisasterModal.vue'
import RegisterCampaignModal from '@/components/RegisterCampaignModal.vue'
import ProfileView from '@/components/ProfileView.vue'
import { useAuth } from '@/stores/auth'
import { computed, ref, onMounted } from 'vue'
import { api } from '@/lib/api'
import DisasterAlerts from '@/components/DisasterAlerts.vue'

interface Alert {
  id: number
  title: string
  disaster_type: string
  status: string
  description: string
  divisions: string[]
  reported_at: string
  confirmed: 'pending' | 'confirmed' | 'rejected'
}

const alerts = ref<Alert[]>([])

async function loadAlerts() {
  try {
    const { data } = await api.get<Alert[]>('/alerts')
    alerts.value = data.filter(a => a.confirmed === 'pending')
    console.log('alerts:', data)
  } catch (e) {
    console.warn('Failed to load alerts', e)
  }
}

const auth = useAuth()
// Aid request removed per new requirements
// const showAidRequestModal = ref(false)
const showReportDisaster = ref(false)
const showRegisterCampaign = ref(false)
const disastersRef = ref<any>(null)
const campaignsRef = ref<any>(null)
const banner = ref<{type:'success'|'error'; msg:string} | null>(null)
function flash(type:'success'|'error', msg:string, ms=1800){
  banner.value = { type, msg }; setTimeout(()=> { banner.value = null }, ms)
}
const userName = computed(() => auth.user?.name || 'User')

// NGO staff info
const ngoId = ref<number | null>(null)
const ngoName = ref<string | null>(null)

onMounted(async () => {
  if (auth.token && !auth.user) {
    try { await auth.fetchUser() } catch (e) { console.error('fetchUser failed', e) }
  }
  try {
    const { data } = await api.get('/ngo-staff')
    const payload = (data && typeof data === 'object' && 'data' in data) ? (data as any).data : data
    ngoId.value = (payload as any)?.ngo_id ?? (payload as any)?.ngoId ?? null
    if (ngoId.value) {
      try {
        const ngoRes = await api.get(`/ngo/${ngoId.value}`)
        ngoName.value = (ngoRes.data?.name) || (ngoRes.data?.data?.name) || null
      } catch (e) {
        console.warn('Failed to fetch NGO name', e)
      }
    }
  } catch (e) {
    console.warn('Failed to fetch ngo staff details', e)
  }
  await loadAlerts()
})

// Aid request handlers removed
const openReportDisaster = () => showReportDisaster.value = true
const closeReportDisaster = () => showReportDisaster.value = false
const openRegisterCampaign = () => showRegisterCampaign.value = true
const closeRegisterCampaign = () => showRegisterCampaign.value = false
const handleDisasterCreated = () => {
  closeReportDisaster()
  disastersRef.value?.refresh?.()
  flash('success','Disaster reported & activated')
}
const handleCampaignCreated = (payload:any) => {
  closeRegisterCampaign()
  // Add instantly to campaigns list (fallback refresh if component not ready)
  if (campaignsRef.value?.append) { campaignsRef.value.append(payload) } else { campaignsRef.value?.refresh?.() }
  flash('success','Campaign registered')
}
// const handleAidRequestSubmit = () => {}
</script>

<template>
  <div class="min-h-screen">
    <main class="flex flex-col px-8 py-16">
      <div class="flex items-center justify-between mb-10">
        <div>
          <h1 class="text-4xl font-bold text-black-800">
            Welcome {{ userName }}<span v-if="ngoName" class="text-lg font-normal ml-2 text-gray-500">({{ ngoName }})</span>,
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
        class="px-5 py-2 text-sm font-medium"
        @click="openRegisterCampaign"
        >Register Campaign</PrimaryButton>
      </div>
    </div>
    <DisasterAlerts v-if="alerts.length" :alerts="alerts" @refresh="loadAlerts" />
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" style="height:600px;">
        <div class="lg:col-span-1" style="height:600px;">
          <ProfileView class="h-full" />
        </div>
        <div class="lg:col-span-1" style="height:600px;">
          <div class="space-y-6 h-full overflow-y-auto">
            <OngoingDisasters ref="disastersRef" />
            <OngoingCampaigns ref="campaignsRef" />
          </div>
        </div>
        <div class="lg:col-span-1" style="height:600px;">
          <CampaignMap class="h-full" />
        </div>
      </div>
    </main>
  <ReportDisasterModal :show="showReportDisaster" @close="closeReportDisaster" @created="handleDisasterCreated" />
  <RegisterCampaignModal :show="showRegisterCampaign" @close="closeRegisterCampaign" @created="handleCampaignCreated" />
  <div v-if="banner" class="fixed bottom-4 right-4 px-4 py-2 rounded shadow text-sm" :class="banner.type==='success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'">
    {{ banner.msg }}
  </div>
  </div>
</template>
