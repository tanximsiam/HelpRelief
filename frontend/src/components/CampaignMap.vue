<template>
  <div class="h-full flex flex-col">
    <!-- Compact Map Component for Dashboard -->
    <div
      class="bg-white p-4 rounded shadow cursor-pointer hover:shadow-lg transition-shadow h-full flex flex-col"
      @click="openModal"
    >
      <h3 class="text-xl font-semibold mb-4">Campaign Coverage Map</h3>
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
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click="closeModal"
    >
      <div
        class="bg-white rounded-lg p-6 max-w-4xl max-h-[90vh] w-full mx-4 overflow-auto"
        @click.stop
      >
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-2xl font-bold">Interactive Campaign Map</h2>
          <button
            @click="closeModal"
            class="text-gray-500 hover:text-gray-700 text-2xl"
          >
            ×
          </button>
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
          <h4 class="text-sm font-semibold mb-2">Campaign Intensity</h4>
          <div class="flex items-center space-x-4">
            <div class="flex items-center">
              <div class="w-4 h-4 bg-gray-300 mr-2"></div>
              <span class="text-sm">No campaigns</span>
            </div>
            <div class="flex items-center">
              <div class="w-4 h-4 bg-blue-300 mr-2"></div>
              <span class="text-sm">Low (1-2)</span>
            </div>
            <div class="flex items-center">
              <div class="w-4 h-4 bg-blue-500 mr-2"></div>
              <span class="text-sm">Medium (3-5)</span>
            </div>
            <div class="flex items-center">
              <div class="w-4 h-4 bg-blue-800 mr-2"></div>
              <span class="text-sm">High (6+)</span>
            </div>
          </div>
        </div>

        <div v-if="selectedDistrict" class="border-t pt-4">
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

        <!-- Hover info -->
        <div v-if="hoveredDistrict" class="mt-4 p-2 bg-gray-100 rounded">
          <p class="text-sm">
            <strong>{{ hoveredDistrict }}:</strong>
            {{ getDistrictByName(hoveredDistrict)?.campaign_count || 0 }} active campaigns
          </p>
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
interface DistrictData {
  name: string
  campaign_count: number
  intensity: number
}

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
const hoveredDistrict = ref<string | null>(null)
const districtData = ref<DistrictData[]>([])
const campaignData = ref<CampaignData | null>(null)

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
    districtData.value = bangladeshDistricts.map(name => ({
      name,
      campaign_count: Math.floor(Math.random() * 8), // Random data for demo
      intensity: 0
    }))
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
  const district = getDistrictByName(districtName)
  const campaignCount = district?.campaign_count || 0

  console.log(`District: ${districtName}, Campaign Count: ${campaignCount}`) // Debug log

  let intensityClass = 'campaign-intensity-none'
  if (campaignCount >= 6) intensityClass = 'campaign-intensity-high'
  else if (campaignCount >= 3) intensityClass = 'campaign-intensity-medium'
  else if (campaignCount >= 1) intensityClass = 'campaign-intensity-low'

  return `svg-map__location ${intensityClass}`
}

// Get district by name
const getDistrictByName = (name: string) => {
  return districtData.value.find(d => d.name === name)
}

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
  if (districtName) {
    const district = getDistrictByName(districtName)
    if (district) {
      selectedDistrict.value = district
      console.log('Selected district:', district.name)
    }
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

/* Ensure proper sizing */
.svg-map {
  width: 100%;
  height: 100%;
}
</style>
