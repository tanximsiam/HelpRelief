<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  campaignId: number
  onSubmit: (payload: DonationReportPayload) => void
}>()

interface DonationReportPayload {
  amount_received_financial: number
  amount_used_financial: number
  amount_received_medical: number
  amount_used_medical: number
  amount_received_resource: number
  amount_used_resource: number
  usage_breakdown: string
}

const form = ref<DonationReportPayload>({
  amount_received_financial: 0,
  amount_used_financial: 0,
  amount_received_medical: 0,
  amount_used_medical: 0,
  amount_received_resource: 0,
  amount_used_resource: 0,
  usage_breakdown: ''
})

function submit() {
  props.onSubmit(form.value)
}
</script>

<template>
  <div>
    <div class="mb-6 text-center text-lg font-medium text-gray-700">
      You have to submit the final report to end this campaign.
    </div>
    <form @submit.prevent="submit" class="space-y-4">
      <div class="mb-4">
        <label class="block font-medium mb-1">Financial Aid Received</label>
        <input v-model.number="form.amount_received_financial" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>
      <div class="mb-4">
        <label class="block font-medium mb-1">Financial Aid Used</label>
        <input v-model.number="form.amount_used_financial" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>
      <div class="mb-4">
        <label class="block font-medium mb-1">Medical Aid Received</label>
        <input v-model.number="form.amount_received_medical" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>
      <div class="mb-4">
        <label class="block font-medium mb-1">Medical Aid Used</label>
        <input v-model.number="form.amount_used_medical" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>
      <div class="mb-4">
        <label class="block font-medium mb-1">Resource Aid Received</label>
        <input v-model.number="form.amount_received_resource" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>
      <div class="mb-4">
        <label class="block font-medium mb-1">Resource Aid Used</label>
        <input v-model.number="form.amount_used_resource" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>
      <div class="mb-4">
        <label class="block font-medium mb-1">Usage Breakdown (optional)</label>
        <input v-model="form.usage_breakdown" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. food: 100, medicine: 50" />
      </div>
      <button type="submit" class="w-full py-3 px-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">End Campaign</button>
    </form>
  </div>
</template>
