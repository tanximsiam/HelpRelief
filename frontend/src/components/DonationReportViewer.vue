<script setup lang="ts">
defineProps<{
  reports: {
    id: number
    aid_type: string
    amount_received: number
    amount_used: number
    usage_breakdown: string
    reporting_period: string
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
      <div class="flex justify-between items-center">
        <h3 class="font-bold text-base">
          {{ r.aid_type.toUpperCase() }} Report ({{ r.reporting_period }})
        </h3>
        <p class="text-sm text-gray-500">
          Used {{ r.amount_used }} / {{ r.amount_received }}
        </p>
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
