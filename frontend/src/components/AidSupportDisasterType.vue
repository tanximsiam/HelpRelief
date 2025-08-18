<template>
  <div class="space-y-2">
    <h3 class="text-lg font-semibold">Choose the disaster</h3>

    <div v-if="store.loading && !store.disasters.length" class="text-sm text-gray-500">Loading disasters…</div>
    <div v-else-if="store.error && !store.disasters.length" class="text-sm text-red-600">{{ store.error }}</div>

    <ul v-else class="divide-y rounded-xl border">
      <li v-for="d in store.disasters" :key="d.id">
        <BaseRowButton
          :selected="store.disaster?.id === d.id"
          @click="select(d)"
        >
          {{ d.name }}
          <template #sub>
            {{ d.location }}<span v-if="d.location && (d.meta?.since || d.since)"> • </span>{{ d.meta?.since || d.since || '' }}
          </template>
          <template #trailing>
            <span
              class="rounded-full px-2 py-1 text-xs"
              :class="badgeClass(d.severity)"
            >
              {{ d.severityLabel || (d.severity === 'major' ? 'Major help required' : 'Moderate help required') }}
            </span>
          </template>
        </BaseRowButton>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import BaseRowButton from './BaseRowButton.vue'
import { useAidSupportStore, type Disaster } from '@/stores/AidSupport'

const store = useAidSupportStore()
onMounted(() => store.fetchDisasters())

function badgeClass(sev?: Disaster['severity']) {
  return sev === 'major'
    ? 'bg-rose-100 text-rose-700'
    : 'bg-amber-100 text-amber-700'
}

function select(d: Disaster) {
  store.setDisaster(d)
  store.fetchNgosForSelected()
}
</script>
