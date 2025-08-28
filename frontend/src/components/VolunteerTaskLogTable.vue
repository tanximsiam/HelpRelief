<script lang="ts" setup>
import { ref, computed } from 'vue'

interface VolunteerTaskLog {
  id: number
  task_id: number
  status: string
  report: string
  check_in?: string
  check_out?: string
  task?: { aid_type?: string }
  volunteer?: { name?: string }
  placeholder?: boolean
}

const props = defineProps<{ logs: VolunteerTaskLog[], loading: boolean, search: string }>()
const emit = defineEmits<{ (e:'checkOut', log: VolunteerTaskLog): void; (e:'checkIn', log: VolunteerTaskLog): void }>()

function formatDate(dt?: string) {
  if (!dt) return '-'
  const d = new Date(dt)
  return d.toLocaleString()
}

function volunteerName(log: VolunteerTaskLog) {
  return log.volunteer?.name
    || (log as any).assigned_to_name
    || (log as any).task?.assigned_to_name
    || '-'
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
            <td class="px-4 py-2">{{ log.task?.aid_type || '-' }}</td>
            <td class="px-4 py-2">{{ volunteerName(log) }}</td>
            <td class="px-4 py-2">
              <span :class="[
                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize ring-1 ring-inset',
                log.placeholder ? 'bg-slate-50 text-slate-600 ring-slate-500/10' :
                (log.check_out ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/10' :
                 (log.check_in ? 'bg-blue-50 text-blue-700 ring-blue-600/10' : 'bg-amber-50 text-amber-700 ring-amber-600/10'))
              ]">{{ log.status }}</span>
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
              <template v-if="log.check_in">{{ formatDate(log.check_in) }}</template>
              <button v-else @click="$emit('checkIn', log)" class="text-xs font-semibold text-emerald-600 hover:underline">Check In</button>
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
