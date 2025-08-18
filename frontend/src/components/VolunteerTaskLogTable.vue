<script lang="ts" setup>
import { ref, computed } from 'vue'

interface VolunteerTaskLog {
  id: number
  task_id: number
  status: string
  report: string
  check_in?: string
  check_out?: string
  task?: { task_type?: string }
  volunteer?: { name?: string }
}

const props = defineProps<{ logs: VolunteerTaskLog[], loading: boolean, search: string }>()
const emit = defineEmits<{ (e:'checkOut', log: VolunteerTaskLog): void }>()

function formatDate(dt?: string) {
  if (!dt) return '-'
  const d = new Date(dt)
  return d.toLocaleString()
}
</script>

<template>
  <div class="rounded-lg border border-slate-300 overflow-hidden">
    <div class="overflow-auto max-h-[55vh]">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-slate-100 text-left text-slate-600">
            <th class="px-4 py-2 font-semibold">Task</th>
            <th class="px-4 py-2 font-semibold">Task Type</th>
            <th class="px-4 py-2 font-semibold">Volunteer</th>
            <th class="px-4 py-2 font-semibold">Status</th>
            <th class="px-4 py-2 font-semibold">Check In</th>
            <th class="px-4 py-2 font-semibold">Check Out</th>
            <th class="px-4 py-2 font-semibold">Report</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="px-4 py-8 text-center text-slate-500">Loading...</td>
          </tr>
          <tr v-else-if="logs.length === 0">
            <td colspan="7" class="px-4 py-8 text-center text-slate-500">No logs match your search.</td>
          </tr>
          <tr v-for="log in logs" :key="log.id" class="border-t border-slate-200 hover:bg-slate-50">
            <td class="px-4 py-2 font-medium text-slate-800">#{{ log.task_id }}</td>
            <td class="px-4 py-2">{{ log.task?.task_type || '-' }}</td>
            <td class="px-4 py-2">{{ log.volunteer?.name || '-' }}</td>
            <td class="px-4 py-2">
              <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold capitalize text-blue-700 ring-1 ring-inset ring-blue-600/10">{{ log.status }}</span>
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
              <template v-if="log.check_in">{{ formatDate(log.check_in) }}</template>
              <span v-else class="text-xs text-slate-400">—</span>
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
              <template v-if="log.check_out">{{ formatDate(log.check_out) }}</template>
              <button v-else-if="log.check_in" @click="$emit('checkOut', log)" class="text-xs font-semibold text-rose-600 hover:underline">Check Out</button>
              <span v-else class="text-xs text-slate-400">—</span>
            </td>
            <td class="px-4 py-2 capitalize">{{ log.report }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
