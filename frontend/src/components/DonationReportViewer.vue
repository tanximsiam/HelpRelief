<script setup lang="ts">
defineProps<{
  reports: {
    id: number
    amount_received_financial: number
    amount_used_financial: number
    amount_received_medical: number
    amount_used_medical: number
    amount_received_resource: number
    amount_used_resource: number
    usage_breakdown: string
  }[]
}>()

function parseBreakdown(json: string): Record<string, number> {
  try {
    return JSON.parse(json)
  } catch {
    return {}
  }
}
</script>

<template>
  <div class="mt-6 border-t pt-4">
    <h2 class="text-lg font-semibold mb-4">Detailed Report Viewer</h2>

    <div
      v-for="r in reports"
      :key="r.id"
      class="mb-6 p-4 border rounded-md bg-white shadow-sm"
    >
      <h3 class="font-bold text-base mb-2">Donation Report</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
        <div>
          <span class="font-semibold">Financial Aid:</span><br>
          Received: {{ r.amount_received_financial }}<br>
          Used: {{ r.amount_used_financial }}
        </div>
        <div>
          <span class="font-semibold">Medical Aid:</span><br>
          Received: {{ r.amount_received_medical }}<br>
          Used: {{ r.amount_used_medical }}
        </div>
        <div>
          <span class="font-semibold">Resource Aid:</span><br>
          Received: {{ r.amount_received_resource }}<br>
          Used: {{ r.amount_used_resource }}
        </div>
      </div>
      <div class="mt-2">
        <p class="font-medium mb-1">Usage Breakdown:</p>
        <ul class="list-disc list-inside text-sm text-gray-700">
          <li
            v-for="(value, key) in parseBreakdown(r.usage_breakdown)"
            :key="key"
          >
            {{ key }}: {{ value }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
