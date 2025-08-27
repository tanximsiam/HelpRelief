<template>
  <div class="space-y-2">
    <h3 class="text-lg font-semibold">Choose the disaster</h3>

    <div v-if="store.loading && !store.disasters.length" class="text-sm text-gray-500">Loading disasters…</div>
    <div v-else-if="store.error && !store.disasters.length" class="text-sm text-red-600">{{ store.error }}</div>

    <ul v-else class="divide-y rounded-xl border">
      <li v-for="(d, idx) in store.disasters" :key="d.id">
        <BaseRowButton
          :selected="store.disaster?.id === d.id"
          :class="[
            store.disaster?.id === d.id ? 'bg-gray-200 border-gray-300' : 'bg-white border-transparent',
            idx === 0 ? 'rounded-t-xl' : '',
            idx === store.disasters.length - 1 ? 'rounded-b-xl' : ''
          ]"
          @click="select(d)"
          chevron
        >
          {{ d.name || (d.location + ' Disaster') }}
          <template #sub>
            <span class="inline-flex items-center gap-2">
              <span class="text-sm text-gray-500">{{ d.location }}</span>
              <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', badgeClass(d.severity)]">
                {{ d.severity === 'high' ? 'High' : 'Medium' }}
              </span>
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
  return sev === 'high'
    ? 'bg-rose-100 text-rose-700'
    : 'bg-amber-100 text-amber-700'
}

function select(d: Disaster) {
  store.setDisaster(d)
  store.fetchCampaignsForSelected()
}
</script>