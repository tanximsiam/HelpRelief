<template>
  <div class="h-full flex flex-col">
    <!-- Compact Map Component for Dashboard -->
    <div
      class="bg-white p-4 rounded shadow cursor-pointer hover:shadow-lg transition-shadow h-full flex flex-col"
      @click="openModal"
    >
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">
          {{
            mode === 'campaigns'
              ? 'Campaign Coverage Map'
              : mode === 'aid'
              ? 'Aid Request Heatmap'
              : 'Aid Need Density Map'
          }}
        </h3>
        <button
          @click.stop="toggleMode"
          class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 px-2 py-1 rounded"
        >
          Switch to
          {{
            mode === 'campaigns'
              ? 'Aid Requests'
              : mode === 'aid'
              ? 'Aid Need'
              : 'Campaigns'
          }}
        </button>
      </div>

      <div class="flex-1 w-full rounded border flex items-center justify-center bg-gray-50 min-h-[300px]">
        <div class="w-full h-full flex items-center justify-center">
          <SvgMap
            :map="bangladeshMap"
            :location-class="getLocationClass"
            class="max-w-full max-h-full"
            style="pointer-events: none;"
          />
        </div>
      </div>
      <p class="text-sm text-gray-600 mt-4">Click to view interactive map</p>
    </div>

    <!-- Modal for Interactive Map -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4"
      @click="closeModal"
    >
      <div
        class="relative w-full rounded-xl bg-white shadow-xl ring-1 ring-black/5 max-w-5xl max-h-[90vh] overflow-auto"
        @click.stop
      >
        <button
          type="button"
          class="absolute right-3.5 top-3.5 inline-flex h-5 w-5 items-center justify-center rounded-full border border-blue-600 text-blue-600 text-[15px] leading-none font-semibold transition hover:bg-blue-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white"
          @click="closeModal"
          aria-label="Close"
        >
          <span class="-mt-[1px]">×</span>
        </button>

        <div class="p-8">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">
              Interactive
              {{
                mode === 'campaigns'
                  ? 'Campaign Map'
                  : mode === 'aid'
                  ? 'Aid Request Heatmap'
                  : 'Aid Need Density Map'
              }}
            </h2>
          </div>

          <div
            class="h-96 w-full rounded border flex items-center justify-center bg-gray-50 mb-4 relative overflow-hidden"
            ref="mapContainer"
          >
            <SvgMap
              :map="bangladeshMap"
              :location-class="getLocationClass"
              class="max-w-full max-h-full cursor-pointer"
              @click="handleLocationClick"
              @mouseover="handleLocationHover"
              @mousemove="handleMouseMove"
              @mouseout="clearHover"
            />

            <!-- Trigger button to show Aid Requests overlay (appears after selecting a state in aid mode) -->
            <button
              v-if="mode==='aid' && selectedDensity && !showAidOverlay"
              @click="showAidOverlay = true"
              class="absolute bottom-3 left-3 bg-white/90 backdrop-blur px-3 py-1.5 text-sm font-medium rounded shadow hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              See Aid Requests
            </button>

            <!-- Aid Requests inline overlay over heatmap -->
            <AidRequestsOverlay
              :show="showAidOverlay && mode==='aid'"
              :state-name="selectedDensity?.name || null"
              @close="showAidOverlay=false"
            />

            <!-- Hover tooltip -->
            <div
              v-if="hoveredDistrict"
              :style="{ left: hoverX + 'px', top: hoverY + 'px' }"
              class="pointer-events-none absolute z-30 -translate-x-1/2 -translate-y-full whitespace-nowrap"
            >
              <div class="rounded bg-gray-900/90 text-white text-[11px] px-2 py-1 shadow-lg ring-1 ring-black/40">
                <template v-if="mode==='campaigns'">
                  <strong>{{ hoveredDistrict }}:</strong>
                  {{ getDistrictByName(hoveredDistrict)?.campaign_count || 0 }} active campaigns
                </template>
                <template v-else-if="mode==='aid'">
                  <strong>{{ hoveredDistrict }}:</strong>
                  {{ getDensityByName(hoveredDistrict)?.request_count || 0 }} aid requests
                </template>
                <template v-else>
                  <strong>{{ hoveredDistrict }}:</strong>
                  {{ getAidNeedByName(hoveredDistrict)?.aid_needed || 0 }} aid needed
                </template>
              </div>
            </div>
          </div>

          <!-- Legend -->
          <div class="mb-4">
            <h4 class="text-sm font-semibold mb-2" v-if="mode==='campaigns'">Campaign Intensity</h4>
            <h4 class="text-sm font-semibold mb-2" v-else-if="mode==='aid'">Aid Request Density (Total Requests)</h4>
            <h4 class="text-sm font-semibold mb-2" v-else>Aid Need Density (Requests - Support)</h4>

            <div v-if="mode==='campaigns'" class="flex items-center space-x-4">
              <div class="flex items-center"><div class="w-4 h-4 bg-gray-300 mr-2"></div><span class="text-sm">No campaigns</span></div>
              <div class="flex items-center"><div class="w-4 h-4 bg-blue-300 mr-2"></div><span class="text-sm">Low (1-2)</span></div>
              <div class="flex items-center"><div class="w-4 h-4 bg-blue-500 mr-2"></div><span class="text-sm">Medium (3-5)</span></div>
              <div class="flex items-center"><div class="w-4 h-4 bg-blue-800 mr-2"></div><span class="text-sm">High (6+)</span></div>
            </div>

            <div v-else-if="mode==='aid'" class="flex items-center space-x-4">
              <div class="flex items-center"><div class="w-4 h-4 heatmap-intensity-0 mr-2"></div><span class="text-sm">None</span></div>
              <div class="flex items-center"><div class="w-4 h-4 heatmap-intensity-1 mr-2"></div><span class="text-sm">Low</span></div>
              <div class="flex items-center"><div class="w-4 h-4 heatmap-intensity-2 mr-2"></div><span class="text-sm">Medium</span></div>
              <div class="flex items-center"><div class="w-4 h-4 heatmap-intensity-3 mr-2"></div><span class="text-sm">High</span></div>
            </div>

            <div v-else class="flex items-center space-x-4">
              <div class="flex items-center"><div class="w-4 h-4 aid-need-intensity-0 mr-2"></div><span class="text-sm">No Need</span></div>
              <div class="flex items-center"><div class="w-4 h-4 aid-need-intensity-1 mr-2"></div><span class="text-sm">Low Need</span></div>
              <div class="flex items-center"><div class="w-4 h-4 aid-need-intensity-2 mr-2"></div><span class="text-sm">Medium Need</span></div>
              <div class="flex items-center"><div class="w-4 h-4 aid-need-intensity-3 mr-2"></div><span class="text-sm">High Need</span></div>
            </div>
          </div>

          <!-- Campaign Detail (when in campaign mode) -->
          <div v-if="selectedDistrict && mode==='campaigns'" class="border-t pt-4">
            <h3 class="text-lg font-semibold mb-2">{{ selectedDistrict.name }} - Campaign Details</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ selectedDistrict.campaign_count }}</div>
                <div class="text-sm text-gray-600">Active Campaigns</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ Math.floor(selectedDistrict.campaign_count * 15) }}</div>
                <div class="text-sm text-gray-600">Volunteers</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-orange-600">{{ Math.floor(selectedDistrict.campaign_count * 100) }}</div>
                <div class="text-sm text-gray-600">Aid Distributed</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ Math.floor(selectedDistrict.campaign_count * 500) }}</div>
                <div class="text-sm text-gray-600">Beneficiaries</div>
              </div>
            </div>
            <button @click="viewStateDetails" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
              View Detailed State Report
            </button>
          </div>

          <!-- Aid Request Detail (when in heatmap mode) -->
          <div v-if="selectedDensity && mode==='aid'" class="border-t pt-4">
            <h3 class="text-lg font-semibold mb-2">{{ selectedDensity.name }} - Aid Requests</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-rose-600">{{ selectedDensity.request_count }}</div>
                <div class="text-sm text-gray-600">Total Requests</div>
              </div>
              <div class="text-center"><div class="text-lg font-semibold text-gray-700">{{ selectedDensity.breakdown.low }}</div><div class="text-xs text-gray-500">Low</div></div>
              <div class="text-center"><div class="text-lg font-semibold text-yellow-600">{{ selectedDensity.breakdown.medium }}</div><div class="text-xs text-gray-500">Medium</div></div>
              <div class="text-center"><div class="text-lg font-semibold text-orange-600">{{ selectedDensity.breakdown.high }}</div><div class="text-xs text-gray-500">High</div></div>
              <div class="text-center"><div class="text-lg font-semibold text-red-600">{{ selectedDensity.breakdown.critical }}</div><div class="text-xs text-gray-500">Critical</div></div>
            </div>
          </div>

          <!-- Aid Need Detail (when in aid need mode) -->
          <div v-if="selectedAidNeed && mode==='aid-need'" class="border-t pt-4">
            <h3 class="text-lg font-semibold mb-2">{{ selectedAidNeed.name }} - Aid Need Analysis</h3>

            <!-- Aid Need Summary -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-red-600">{{ selectedAidNeed.aid_needed }}</div>
                <div class="text-sm text-gray-600">Aid Needed</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-semibold text-blue-600">{{ selectedAidNeed.request_count }}</div>
                <div class="text-xs text-gray-500">Total Requests</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-semibold text-green-600">{{ selectedAidNeed.support_count }}</div>
                <div class="text-xs text-gray-500">Support Provided</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-semibold text-orange-600">{{ selectedAidNeed.urgency_score }}</div>
                <div class="text-xs text-gray-500">Urgency Score</div>
              </div>
            </div>

            <!-- Urgency Breakdown -->
            <div class="mb-4">
              <h4 class="text-sm font-semibold mb-2">Urgency Breakdown</h4>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <div class="text-center p-2 bg-gray-100 rounded">
                  <div class="text-sm font-semibold text-gray-700">{{ selectedAidNeed.breakdown.low }}</div>
                  <div class="text-xs text-gray-500">Low</div>
                </div>
                <div class="text-center p-2 bg-yellow-100 rounded">
                  <div class="text-sm font-semibold text-yellow-700">{{ selectedAidNeed.breakdown.medium }}</div>
                  <div class="text-xs text-yellow-600">Medium</div>
                </div>
                <div class="text-center p-2 bg-orange-100 rounded">
                  <div class="text-sm font-semibold text-orange-700">{{ selectedAidNeed.breakdown.high }}</div>
                  <div class="text-xs text-orange-600">High</div>
                </div>
                <div class="text-center p-2 bg-red-100 rounded">
                  <div class="text-sm font-semibold text-red-700">{{ selectedAidNeed.breakdown.critical }}</div>
                  <div class="text-xs text-red-600">Critical</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Deployment Zones and Volunteer Tasks (when in aid need mode) -->
          <div v-if="mode==='aid-need'" class="border-t pt-4">
            <!-- Loading State -->
            <div v-if="isLoadingAidNeed" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
              <p class="text-gray-600">Loading aid need data from database...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="aidNeedError" class="text-center py-8">
              <div class="text-red-600 mb-4">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <p class="text-lg font-semibold">{{ aidNeedError }}</p>
              </div>
              <p class="text-gray-600 text-sm">This feature is only available to authorized NGO staff members.</p>
            </div>

            <!-- Data Display -->
            <div v-else-if="deploymentZones.length > 0">
              <h3 class="text-lg font-semibold mb-4">Deployment Zone Recommendations</h3>
            </div>

            <!-- No Data State -->
            <div v-else class="text-center py-8">
              <div class="text-gray-500 mb-4">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-lg font-semibold">No Deployment Zones Found</p>
              </div>
              <p class="text-gray-600 text-sm">All aid requests in your area have been addressed or there are no active aid requests.</p>
            </div>

            <!-- Data Display Content -->
            <div v-if="deploymentZones.length > 0">
              <!-- Priority Zones -->
              <div class="mb-6">
                <h4 class="text-md font-semibold mb-3 text-gray-700">Priority Deployment Zones</h4>
                <div class="space-y-3">
                  <div
                    v-for="zone in deploymentZones.slice(0, 3)"
                    :key="zone.state"
                    class="p-3 border rounded-lg"
                    :class="{
                      'border-red-300 bg-red-50': zone.priority_level === 'critical',
                      'border-orange-300 bg-orange-50': zone.priority_level === 'high',
                      'border-yellow-300 bg-yellow-50': zone.priority_level === 'medium',
                      'border-green-300 bg-green-50': zone.priority_level === 'low'
                    }"
                  >
                    <div class="flex justify-between items-start">
                      <div>
                        <h5 class="font-semibold text-gray-800">{{ zone.state }}</h5>
                        <p class="text-sm text-gray-600">
                          Aid Needed: {{ zone.aid_needed }} |
                          Urgency Score: {{ zone.urgency_score }}
                        </p>
                      </div>
                      <span
                        class="px-2 py-1 text-xs font-medium rounded-full text-white"
                        :class="{
                          'bg-red-500': zone.priority_level === 'critical',
                          'bg-orange-500': zone.priority_level === 'high',
                          'bg-yellow-500': zone.priority_level === 'medium',
                          'bg-green-500': zone.priority_level === 'low'
                        }"
                      >
                        {{ zone.priority_level.toUpperCase() }}
                      </span>
                    </div>
                    <p class="text-sm text-gray-700 mt-2">
                      Recommended Volunteers: {{ zone.recommended_volunteers }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Volunteer Task Prioritization -->
              <div v-if="volunteerTasks.length > 0">
                <h4 class="text-md font-semibold mb-3 text-gray-700">Volunteer Task Prioritization</h4>
                <div class="space-y-3">
                  <div
                    v-for="task in volunteerTasks"
                    :key="task.state"
                    class="p-3 border border-gray-200 rounded-lg bg-gray-50"
                  >
                    <div class="flex justify-between items-start mb-2">
                      <h5 class="font-semibold text-gray-800">{{ task.state }}</h5>
                      <span
                        class="px-2 py-1 text-xs font-medium rounded-full text-white"
                        :class="{
                          'bg-red-500': task.priority_level === 'critical',
                          'bg-orange-500': task.priority_level === 'high',
                          'bg-yellow-500': task.priority_level === 'medium',
                          'bg-green-500': task.priority_level === 'low'
                        }"
                      >
                        {{ task.priority_level.toUpperCase() }}
                      </span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                      <div>
                        <span class="font-medium text-gray-700">Volunteers:</span>
                        {{ task.recommended_volunteers }}
                      </div>
                      <div>
                        <span class="font-medium text-gray-700">Duration:</span>
                        {{ task.estimated_duration }}
                      </div>
                    </div>
                    <div class="mt-2">
                      <span class="font-medium text-gray-700 text-sm">Task Types:</span>
                      <div class="flex flex-wrap gap-1 mt-1">
                        <span
                          v-for="taskType in task.task_types"
                          :key="taskType"
                          class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded"
                        >
                          {{ formatTaskType(taskType) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Summary Statistics -->
              <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <h4 class="text-md font-semibold mb-2 text-blue-800">Summary</h4>
                <div class="grid grid-cols-3 gap-4 text-sm">
                  <div>
                    <span class="font-medium text-blue-700">Total Aid Needed:</span>
                    <div class="text-lg font-bold text-blue-800">{{ totalAidNeeded }}</div>
                  </div>
                  <div>
                    <span class="font-medium text-blue-700">Total Requests:</span>
                    <div class="text-lg font-bold text-blue-800">{{ totalRequests }}</div>
                  </div>
                  <div>
                    <span class="font-medium text-blue-700">Total Support:</span>
                    <div class="text-lg font-bold text-blue-800">{{ totalSupport }}</div>
                  </div>
                </div>
              </div>
            </div> 
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { SvgMap } from 'vue3-svg-map'
import bangladeshMap from '@/assets/maps/bangladeshMap.json'
import 'vue3-svg-map/style.css'
import { ref, onMounted, defineEmits } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '@/lib/api'
import AidRequestsOverlay from '@/components/AidRequestsOverlay.vue'

interface DistrictData { name: string; campaign_count: number; intensity: number }
interface DensityData { name: string; request_count: number; breakdown: Record<string, number>; intensity: number }
interface AidNeedData {
  name: string; aid_needed: number; request_count: number; support_count: number;
  urgency_score: number; breakdown: Record<string, number>; intensity: number
}
interface CampaignData { ngo_id: number; states: DistrictData[]; max_campaigns: number }

const emit = defineEmits(['open-modal'])
const router = useRouter()

const showModal = ref(false)
const selectedDistrict = ref<DistrictData | null>(null)
const selectedDensity = ref<DensityData | null>(null)
const selectedAidNeed = ref<AidNeedData | null>(null)
const hoveredDistrict = ref<string | null>(null)
const hoverX = ref(0)
const hoverY = ref(0)
const mapContainer = ref<HTMLElement | null>(null)
const districtData = ref<DistrictData[]>([])
const densityData = ref<DensityData[]>([])
const aidNeedData = ref<AidNeedData[]>([])
const campaignData = ref<CampaignData | null>(null)
const mode = ref<'campaigns' | 'aid' | 'aid-need'>('campaigns')

// Aid need specific data
const deploymentZones = ref<any[]>([])
const volunteerTasks = ref<any[]>([])
const totalAidNeeded = ref(0)
const totalRequests = ref(0)
const totalSupport = ref(0)
const isLoadingAidNeed = ref(false)
const aidNeedError = ref<string | null>(null)
const showAidOverlay = ref(false)

// -------- helpers (moved from template) ----------
const formatTaskType = (s: string) =>
  s.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
// -------------------------------------------------

const fetchMapData = async () => {
  try {
    const response = await api.get('/map/campaign-intensity')
    campaignData.value = response.data
    districtData.value = response.data.states
  } catch (error) {
    console.error('Error fetching map data:', error)
    const bangladeshDistricts = ['Barisal', 'Chittagong', 'Dhaka', 'Khulna', 'Rajshahi', 'Rangpur', 'Sylhet']
    districtData.value = bangladeshDistricts.map((name) => ({ name, campaign_count: 0, intensity: 0 }))
  }
}

const fetchDensityData = async () => {
  try {
    const response = await api.get('/map/aid-request-density')
    densityData.value = response.data.states
  } catch (e) {
    console.error('Error fetching density data', e)
  }
}

const fetchAidNeedData = async () => {
  isLoadingAidNeed.value = true
  aidNeedError.value = null
  try {
    const response = await api.get('/map/aid-need')
    aidNeedData.value = response.data.states
    deploymentZones.value = response.data.deployment_zones || []
    volunteerTasks.value = response.data.volunteer_tasks || []
    totalAidNeeded.value = response.data.total_aid_needed || 0
    totalRequests.value = response.data.total_requests || 0
    totalSupport.value = response.data.total_support || 0
  } catch (e: any) {
    if (e.response?.status === 403) {
      aidNeedError.value = 'Access denied: NGO staff authorization required'
      aidNeedData.value = []
      deploymentZones.value = []
      volunteerTasks.value = []
      totalAidNeeded.value = 0
      totalRequests.value = 0
      totalSupport.value = 0
    } else if (e.response?.status === 401) {
      aidNeedError.value = 'Authentication required'
    } else {
      aidNeedError.value = 'Failed to load aid need data'
    }
  } finally {
    isLoadingAidNeed.value = false
  }
}

const getLocationClass = (location: { id: string; name: string }) => {
  const districtMap: Record<string, string> = {
    'BD-A': 'Barisal',
    'BD-B': 'Chittagong',
    'BD-C': 'Dhaka',
    'BD-D': 'Khulna',
    'BD-E': 'Rajshahi',
    'BD-F': 'Rangpur',
    'BD-G': 'Sylhet'
  }
  const districtName = districtMap[location.id] || location.name

  if (mode.value === 'campaigns') {
    const district = getDistrictByName(districtName)
    const campaignCount = district?.campaign_count || 0
    let intensityClass = 'campaign-intensity-none'
    if (campaignCount >= 6) intensityClass = 'campaign-intensity-high'
    else if (campaignCount >= 3) intensityClass = 'campaign-intensity-medium'
    else if (campaignCount >= 1) intensityClass = 'campaign-intensity-low'
    return `svg-map__location ${intensityClass}`
  } else if (mode.value === 'aid') {
    const density = getDensityByName(districtName)
    const intensity = density?.intensity || 0
    let bucket = 0
    if (intensity >= 0.66) bucket = 3
    else if (intensity >= 0.33) bucket = 2
    else if (intensity > 0) bucket = 1
    return `svg-map__location heatmap-intensity-${bucket}`
  } else {
    const aidNeed = getAidNeedByName(districtName)
    const intensity = aidNeed?.intensity || 0
    let bucket = 0
    if (intensity >= 0.66) bucket = 3
    else if (intensity >= 0.33) bucket = 2
    else if (intensity > 0) bucket = 1
    return `svg-map__location aid-need-intensity-${bucket}`
  }
}

const getDistrictByName = (name: string) => districtData.value.find((d) => d.name === name)
const getDensityByName = (name: string) => densityData.value.find((d) => d.name === name)
const getAidNeedByName = (name: string) => aidNeedData.value.find((d) => d.name === name)

const handleLocationClick = (event: Event) => {
  const target = event.target as SVGElement
  const locationId = target.id
  const districtMap: Record<string, string> = {
    'BD-A': 'Barisal',
    'BD-B': 'Chittagong',
    'BD-C': 'Dhaka',
    'BD-D': 'Khulna',
    'BD-E': 'Rajshahi',
    'BD-F': 'Rangpur',
    'BD-G': 'Sylhet'
  }
  const districtName = districtMap[locationId]
  if (!districtName) return

  if (mode.value === 'campaigns') {
    const district = getDistrictByName(districtName)
    if (district) selectedDistrict.value = district
  } else if (mode.value === 'aid') {
    const density = getDensityByName(districtName)
    if (density) selectedDensity.value = density
  } else {
    const aidNeed = getAidNeedByName(districtName)
    if (aidNeed) selectedAidNeed.value = aidNeed
  }
}

const handleLocationHover = (event: MouseEvent) => {
  const target = event.target as SVGElement
  const locationId = target.id
  const districtMap: Record<string, string> = {
    'BD-A': 'Barisal',
    'BD-B': 'Chittagong',
    'BD-C': 'Dhaka',
    'BD-D': 'Khulna',
    'BD-E': 'Rajshahi',
    'BD-F': 'Rangpur',
    'BD-G': 'Sylhet'
  }
  hoveredDistrict.value = districtMap[locationId] || null
  if (hoveredDistrict.value) updateHoverPosition(event)
}

const handleMouseMove = (event: MouseEvent) => {
  if (!hoveredDistrict.value) return
  updateHoverPosition(event)
}

function updateHoverPosition(event: MouseEvent) {
  if (!mapContainer.value) return
  const rect = mapContainer.value.getBoundingClientRect()
  hoverX.value = event.clientX - rect.left
  hoverY.value = event.clientY - rect.top - 6
}

const clearHover = () => {
  hoveredDistrict.value = null
}

const toggleMode = async () => {
  mode.value = mode.value === 'campaigns' ? 'aid' : mode.value === 'aid' ? 'aid-need' : 'campaigns'
  selectedDistrict.value = null
  selectedDensity.value = null
  selectedAidNeed.value = null
  if (mode.value === 'aid' && densityData.value.length === 0) await fetchDensityData()
  if (mode.value === 'aid-need' && aidNeedData.value.length === 0) await fetchAidNeedData()
}

const openModal = () => {
  showModal.value = true
  emit('open-modal')
}

const closeModal = () => {
  showModal.value = false
  selectedDistrict.value = null
  selectedDensity.value = null
  selectedAidNeed.value = null
  hoveredDistrict.value = null
  showAidOverlay.value = false
}

const viewStateDetails = () => {
  if (selectedDistrict.value) {
    router.push({ name: 'StateDetails', params: { stateName: selectedDistrict.value.name } })
  }
}

onMounted(async () => {
  await fetchMapData()
})
</script>

<style>
/* Base styles for svg-map locations */
.svg-map__location {
  fill: #e5e7eb !important; /* gray-200 */
  stroke: #374151; /* gray-700 */
  stroke-width: 1;
  cursor: pointer;
  transition: all 0.3s ease;
}
.svg-map__location:hover { stroke-width: 2; filter: brightness(0.9); }

/* Campaign intensity colors */
.svg-map__location.campaign-intensity-none { fill: #e5e7eb !important; }
.svg-map__location.campaign-intensity-none:hover { fill: #bbf7d0 !important; filter: none; }
.svg-map__location.campaign-intensity-low { fill: #bfdbfe !important; }
.svg-map__location.campaign-intensity-medium { fill: #3b82f6 !important; }
.svg-map__location.campaign-intensity-high { fill: #1e40af !important; }

/* Aid request heatmap buckets */
.svg-map__location.heatmap-intensity-0 { fill: #f3f4f6 !important; }
.svg-map__location.heatmap-intensity-1 { fill: #fde68a !important; }
.svg-map__location.heatmap-intensity-2 { fill: #f59e0b !important; }
.svg-map__location.heatmap-intensity-3 { fill: #dc2626 !important; }

/* Aid need intensity buckets */
.svg-map__location.aid-need-intensity-0 { fill: #f3f4f6 !important; }
.svg-map__location.aid-need-intensity-1 { fill: #fde68a !important; }
.svg-map__location.aid-need-intensity-2 { fill: #f59e0b !important; }
.svg-map__location.aid-need-intensity-3 { fill: #dc2626 !important; }

/* Legend squares */
.heatmap-intensity-0, .aid-need-intensity-0 { background: #f3f4f6; }
.heatmap-intensity-1, .aid-need-intensity-1 { background: #fde68a; }
.heatmap-intensity-2, .aid-need-intensity-2 { background: #f59e0b; }
.heatmap-intensity-3, .aid-need-intensity-3 { background: #dc2626; }

/* Ensure proper sizing */
.svg-map { width: 100%; height: 100%; }
</style>
