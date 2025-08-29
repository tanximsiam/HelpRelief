<template>
  <Modal 
    :show="isOpen" 
    title="Your Contribution History"
    max-width="max-w-4xl"
    z-index="z-50"
    @close="$emit('close')"
  >
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-2"></div>
      Loading history...
    </div>
    <div v-else-if="contributionHistory.length === 0" class="text-center py-8 text-gray-500">
      No contribution history found.
    </div>
    <div v-else class="space-y-4">
      <div v-for="contribution in contributionHistory" :key="contribution.id" class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200 p-6">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h5 class="text-lg font-bold text-gray-900 mb-1">{{ contribution.campaign_name }}</h5>
            <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
              {{ contribution.aid_type?.toUpperCase() || 'VOLUNTEER WORK' }}
            </span>
          </div>
          <span class="inline-flex items-center px-3 py-1 text-sm font-bold rounded-full bg-green-500 text-white">
            ✓ COMPLETED
          </span>
        </div>
        
        <div class="bg-white rounded-lg p-4 border border-green-100">
          <h6 class="text-sm font-semibold text-gray-700 mb-3">Service Duration</h6>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <span class="text-gray-600">Check-in:</span>
              <p class="font-medium text-gray-900">{{ formatDateTime(contribution.check_in) }}</p>
            </div>
            <div>
              <span class="text-gray-600">Check-out:</span>
              <p class="font-medium text-gray-900">{{ formatDateTime(contribution.check_out) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import Modal from './Modal.vue'

interface ContributionHistory {
  id: number
  campaign_name: string
  aid_type: string
  check_in: string
  check_out: string
}

interface Props {
  isOpen: boolean
  contributionHistory: ContributionHistory[]
  loading: boolean
}

defineProps<Props>()

defineEmits<{
  close: []
}>()

const formatDateTime = (dateString: string | undefined) => {
  if (!dateString) return 'N/A'

  try {
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateString
  }
}
</script>
