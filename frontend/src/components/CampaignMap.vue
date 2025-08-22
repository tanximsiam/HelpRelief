<template>
  <div class="h-full flex flex-col">
    <!-- Compact Map Component for Dashboard -->
    <div
      class="bg-white p-4 rounded shadow cursor-pointer hover:shadow-lg transition-shadow h-full flex flex-col"
      @click="openModal"
    >
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">{{ mode === 'campaigns' ? 'Campaign Coverage Map' : 'Aid Request Heatmap' }}</h3>
        <button
          @click.stop="toggleMode"
          class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 px-2 py-1 rounded"
        >Switch to {{ mode === 'campaigns' ? 'Aid Requests' : 'Campaigns' }}</button>
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
          <h2 class="text-2xl font-bold">Interactive {{ mode === 'campaigns' ? 'Campaign Map' : 'Aid Request Heatmap' }}</h2>
        </div>

        <div class="h-96 w-full rounded border flex items-center justify-center bg-gray-50 mb-4">
          <SvgMap
            :map="bangladeshMap"
            :location-class="getLocationClass"
            class="max-w-full max-h-full cursor-pointer"
            @click="handleLocationClick"
            @mouseover="handleLocationHover"
            @mouseout="clearHover"
          />
        </div>

        <!-- Legend -->
        <div class="mb-4">
          <h4 class="text-sm font-semibold mb-2" v-if="mode==='campaigns'">Campaign Intensity</h4>
          <h4 class="text-sm font-semibold mb-2" v-else>Aid Request Density (Total Requests)</h4>
          <div v-if="mode==='campaigns'" class="flex items-center space-x-4">
            <div class="flex items-center"><div class="w-4 h-4 bg-gray-300 mr-2"></div><span class="text-sm">No campaigns</span></div>
            <div class="flex items-center"><div class="w-4 h-4 bg-blue-300 mr-2"></div><span class="text-sm">Low (1-2)</span></div>
            <div class="flex items-center"><div class="w-4 h-4 bg-blue-500 mr-2"></div><span class="text-sm">Medium (3-5)</span></div>
            <div class="flex items-center"><div class="w-4 h-4 bg-blue-800 mr-2"></div><span class="text-sm">High (6+)</span></div>
          </div>
          <div v-else class="flex items-center space-x-4">
            <div class="flex items-center"><div class="w-4 h-4 heatmap-intensity-0 mr-2"></div><span class="text-sm">None</span></div>
            <div class="flex items-center"><div class="w-4 h-4 heatmap-intensity-1 mr-2"></div><span class="text-sm">Low</span></div>
            <div class="flex items-center"><div class="w-4 h-4 heatmap-intensity-2 mr-2"></div><span class="text-sm">Medium</span></div>
            <div class="flex items-center"><div class="w-4 h-4 heatmap-intensity-3 mr-2"></div><span class="text-sm">High</span></div>
          </div>
        </div>

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
          <button
            @click="viewStateDetails"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
          >
            View Detailed State Report
          </button>
        </div>

        <!-- Aid Request Detail (when in heatmap mode) -->
        <div v-if="selectedDensity && mode==='aid'" class="border-t pt-4">
          <h3 class="text-lg font-semibold mb-2">{{ selectedDensity.name }} - Aid Requests</h3>
          <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
            <div class="text-center"><div class="text-2xl font-bold text-rose-600">{{ selectedDensity.request_count }}</div><div class="text-sm text-gray-600">Total Requests</div></div>
            <div class="text-center"><div class="text-lg font-semibold text-gray-700">{{ selectedDensity.breakdown.low }}</div><div class="text-xs text-gray-500">Low</div></div>
            <div class="text-center"><div class="text-lg font-semibold text-yellow-600">{{ selectedDensity.breakdown.medium }}</div><div class="text-xs text-gray-500">Medium</div></div>
            <div class="text-center"><div class="text-lg font-semibold text-orange-600">{{ selectedDensity.breakdown.high }}</div><div class="text-xs text-gray-500">High</div></div>
            <div class="text-center"><div class="text-lg font-semibold text-red-600">{{ selectedDensity.breakdown.critical }}</div><div class="text-xs text-gray-500">Critical</div></div>
          </div>
        </div>

        <!-- Hover info -->
        <div v-if="hoveredDistrict" class="mt-4 p-2 bg-gray-100 rounded">
          <p v-if="mode==='campaigns'" class="text-sm"><strong>{{ hoveredDistrict }}:</strong> {{ getDistrictByName(hoveredDistrict)?.campaign_count || 0 }} active campaigns</p>
          <p v-else class="text-sm"><strong>{{ hoveredDistrict }}:</strong> {{ getDensityByName(hoveredDistrict)?.request_count || 0 }} aid requests</p>
        </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { SvgMap } from "vue3-svg-map"
import bangladeshMap from "@/assets/maps/bangladeshMap.json"
import "vue3-svg-map/style.css"
import { ref, onMounted, defineEmits } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '@/lib/api'

// Interfaces
interface DistrictData { name: string; campaign_count: number; intensity: number }
interface DensityData { name: string; request_count: number; breakdown: Record<string, number>; intensity: number }

interface CampaignData {
  ngo_id: number
  states: DistrictData[]
  max_campaigns: number
}

// Emits
const emit = defineEmits(['open-modal'])

// Router
const router = useRouter()

// Reactive state
const showModal = ref(false)
const selectedDistrict = ref<DistrictData | null>(null)
const selectedDensity = ref<DensityData | null>(null)
const hoveredDistrict = ref<string | null>(null)
const districtData = ref<DistrictData[]>([])
const densityData = ref<DensityData[]>([])
const campaignData = ref<CampaignData | null>(null)
const mode = ref<'campaigns' | 'aid'>('campaigns')

// Fetch campaign intensity data
const fetchMapData = async () => {
  try {
    const response = await api.get('/map/campaign-intensity')
    campaignData.value = response.data
    districtData.value = response.data.states
    console.log('Campaign data:', campaignData.value)
  } catch (error) {
    console.error('Error fetching map data:', error)
    // Initialize with empty data if API fails
    const bangladeshDistricts = ['Barisal', 'Chittagong', 'Dhaka', 'Khulna', 'Rajshahi', 'Rangpur', 'Sylhet']
    districtData.value = bangladeshDistricts.map(name => ({ name, campaign_count: 0, intensity: 0 }))
  }
}

// Fetch aid request density data
const fetchDensityData = async () => {
  try {
    const response = await api.get('/map/aid-request-density')
    densityData.value = response.data.states
  } catch (e) {
    console.error('Error fetching density data', e)
  }
}

// Get location class for styling based on campaign intensity
const getLocationClass = (location: { id: string; name: string }) => {
  // Map location ID to district name
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
  } else {
    const density = getDensityByName(districtName)
    const intensity = density?.intensity || 0
    let bucket = 0
    if (intensity >= 0.66) bucket = 3
    else if (intensity >= 0.33) bucket = 2
    else if (intensity > 0) bucket = 1
    return `svg-map__location heatmap-intensity-${bucket}`
  }
}

// Get district by name
const getDistrictByName = (name: string) => {
  return districtData.value.find(d => d.name === name)
}

const getDensityByName = (name: string) => densityData.value.find(d => d.name === name)

// Handle location click
const handleLocationClick = (event: Event) => {
  const target = event.target as SVGElement
  const locationId = target.id

  // Extract district name from ID (BD-A -> Barisal, etc.)
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
  } else {
    const density = getDensityByName(districtName)
    if (density) selectedDensity.value = density
  }
}

// Handle location hover
const handleLocationHover = (event: Event) => {
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
}

// Clear hover
const clearHover = () => {
  hoveredDistrict.value = null
}

const toggleMode = async () => {
  mode.value = mode.value === 'campaigns' ? 'aid' : 'campaigns'
  // Clear selections
  selectedDistrict.value = null
  selectedDensity.value = null
  // Fetch data if switching to aid mode first time
  if (mode.value === 'aid' && densityData.value.length === 0) {
    await fetchDensityData()
  }
}

// Modal functions
const openModal = () => {
  showModal.value = true
  emit('open-modal')
}

const closeModal = () => {
  showModal.value = false
  selectedDistrict.value = null
  hoveredDistrict.value = null
}

const viewStateDetails = () => {
  if (selectedDistrict.value) {
    router.push({
      name: 'StateDetails',
      params: { stateName: selectedDistrict.value.name }
    })
  }
}

// Lifecycle
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

.svg-map__location:hover {
  stroke-width: 2;
  filter: brightness(0.9);
}

/* Campaign intensity colors with higher specificity */
.svg-map__location.campaign-intensity-none {
  fill: #e5e7eb !important; /* gray-200 */
}

.svg-map__location.campaign-intensity-none:hover {
  fill: #bbf7d0 !important; /* green-200 - light green for no campaigns */
  filter: none; /* Remove brightness filter for green hover */
}

.svg-map__location.campaign-intensity-low {
  fill: #bfdbfe !important; /* blue-200 */
}

.svg-map__location.campaign-intensity-medium {
  fill: #3b82f6 !important; /* blue-500 */
}

.svg-map__location.campaign-intensity-high {
  fill: #1e40af !important; /* blue-800 */
}

/* Aid request heatmap buckets */
.svg-map__location.heatmap-intensity-0 { fill: #f3f4f6 !important; }
.svg-map__location.heatmap-intensity-1 { fill: #fde68a !important; }
.svg-map__location.heatmap-intensity-2 { fill: #f59e0b !important; }
.svg-map__location.heatmap-intensity-3 { fill: #dc2626 !important; }

/* Legend squares reuse classes */
.heatmap-intensity-0 { background: #f3f4f6; }
.heatmap-intensity-1 { background: #fde68a; }
.heatmap-intensity-2 { background: #f59e0b; }
.heatmap-intensity-3 { background: #dc2626; }

/* Ensure proper sizing */
.svg-map {
  width: 100%;
  height: 100%;
}
</style>
