<template>
  <Modal :show="show" title="NGO Report" @close="$emit('close')">
    <div class="max-w-3xl">
      <div v-if="loading" class="text-sm text-gray-500">Loading...</div>
      <div v-if="error" class="text-sm text-red-600">{{ error }}</div>

      <div v-if="report" class="space-y-4">
        <div class="flex items-start gap-4">
          <div class="w-24 h-24 bg-gray-100 rounded-md flex items-center justify-center overflow-hidden">
            <img v-if="report.logo_url" :src="report.logo_url" alt="logo" class="object-contain w-full h-full" />
            <div v-else class="text-gray-400">Logo</div>
          </div>
          <div class="flex-1">
            <h2 class="text-xl font-semibold">{{ report.ngo_name }}</h2>
            <p class="text-sm text-gray-600 mt-1">{{ report.description }}</p>
            <div class="mt-3 text-sm text-gray-500">
              <span class="mr-4"><strong>Based in:</strong> {{ report.based_in }}</span>
              <span class="mr-4"><strong>Reg #:</strong> {{ report.registration_no }}</span>
              <span><strong>Since:</strong> {{ report.established_year }}</span>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mt-3">
          <div class="p-3 bg-white shadow rounded-md flex flex-col items-start">
            <div class="text-sm text-gray-500">Aid requested</div>
            <div class="text-2xl font-bold">{{ aidRequestedDisplay }}</div>
          </div>
          <div class="p-3 bg-white shadow rounded-md flex flex-col items-start">
            <div class="text-sm text-gray-500">Aid supplied</div>
            <div class="text-2xl font-bold">{{ aidSuppliedDisplay }}</div>
          </div>
          <div class="p-3 bg-white shadow rounded-md flex flex-col items-start">
            <div class="text-sm text-gray-500">Volunteers</div>
            <div class="text-2xl font-bold">{{ report.volunteer_count ?? 0 }}</div>
          </div>
        </div>

        <!-- Inline comparative bar chart -->
        <div class="mt-4 bg-white p-4 rounded-md shadow">
          <h3 class="text-sm font-semibold mb-2">Aid: Requested vs Supplied</h3>
          <div class="space-y-3">
            <div>
              <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Requested</span>
                <span>{{ aidRequestedDisplay }} ({{ requestedPct }}%)</span>
              </div>
              <div class="w-full bg-gray-100 h-4 rounded overflow-hidden">
                <div class="h-4 bg-cyan-700" :style="{ width: requestedPct + '%' }"></div>
              </div>
            </div>

            <div>
              <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Supplied</span>
                <span>{{ aidSuppliedDisplay }} ({{ suppliedPct }}%)</span>
              </div>
              <div class="w-full bg-gray-100 h-4 rounded overflow-hidden">
                <div class="h-4 bg-cyan-500" :style="{ width: suppliedPct + '%' }"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-4">
          <div class="bg-white p-4 rounded-md shadow">
            <h3 class="text-sm font-semibold mb-2">Contact & Details</h3>
            <ul class="text-sm text-gray-700 space-y-1">
              <li><strong>Email:</strong> <a :href="`mailto:${report.email}`" class="text-blue-600">{{ report.email }}</a></li>
              <li><strong>Phone:</strong> {{ report.phone }}</li>
              <li><strong>Website:</strong> <a :href="report.website" target="_blank" class="text-blue-600">{{ report.website }}</a></li>
              <li><strong>Director:</strong> {{ report.director_name }} ({{ report.director_phone }})</li>
              <li><strong>Employees:</strong> {{ report.num_employees ?? '-' }}</li>
            </ul>
          </div>

          <div class="bg-white p-4 rounded-md shadow">
            <h3 class="text-sm font-semibold mb-2">Activity</h3>
            <div class="text-sm text-gray-700 space-y-2">
              <div><strong>Tasks assigned:</strong> {{ report.tasks_assigned ?? 0 }}</div>
              <div><strong>Disasters engaged:</strong>
                <template v-if="Array.isArray(report.disasters_engaged) && report.disasters_engaged.length">
                  <ul class="list-disc ml-5 mt-1 text-sm text-gray-700">
                    <li v-for="d in report.disasters_engaged" :key="d">{{ d }}</li>
                  </ul>
                </template>
                <span v-else class="ml-2">{{ Array.isArray(report.disasters_engaged) ? report.disasters_engaged.length : (report.disasters_engaged ?? 0) }}</span>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div v-else-if="!loading && !report" class="text-sm text-gray-600">No report available for your NGO.</div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import Modal from '@/components/Modal.vue'
import { api } from '@/lib/api'

const props = defineProps<{ show: boolean }>();
const emits = defineEmits(['close','loaded'])

const loading = ref(false)
const error = ref<string | null>(null)
const report = ref<any | null>(null)

async function loadReport() {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get('/report/my-ngo')
    report.value = data?.data ?? null
    emits('loaded', report.value)
  } catch (err: any) {
    error.value = err?.response?.data?.message || err?.message || 'Failed to load report'
    report.value = null
  } finally {
    loading.value = false
  }
}

const aidRequested = computed(() => Number(report.value?.aid_requested ?? 0))
const aidSupplied = computed(() => Number(report.value?.aid_supplied ?? 0))
const totalAid = computed(() => Math.max(1, aidRequested.value + aidSupplied.value))
const requestedPct = computed(() => Math.round((aidRequested.value / totalAid.value) * 100))
const suppliedPct = computed(() => Math.round((aidSupplied.value / totalAid.value) * 100))

const aidRequestedDisplay = computed(() => aidRequested.value.toLocaleString())
const aidSuppliedDisplay = computed(() => aidSupplied.value.toLocaleString())

watch(() => props.show, (v) => { if (v) loadReport() })

onMounted(() => { if (props.show) loadReport() })

</script>

<style scoped>
/* aesthetic tweaks */
:root { --hr: #e6e6e6 }
 a { color: #2563eb }
 .shadow { box-shadow: 0 8px 18px rgba(15,23,42,0.06) }
 .bg-white { background-color: #ffffff }
 .rounded-md { border-radius: 0.5rem }
 .list-disc { list-style-type: disc }
</style>
