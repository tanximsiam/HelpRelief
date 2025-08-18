<template>
  <div class="space-y-2">
    <h3 class="text-lg font-semibold">Choose the campaign / NGO</h3>

    <div v-if="!store.disaster" class="text-sm text-gray-500">
      Select a disaster first.
    </div>

    <div v-else>
      <div v-if="store.loading && !store.ngos.length" class="text-sm text-gray-500">Loading NGOs…</div>
      <div v-else-if="store.error && !store.ngos.length" class="text-sm text-red-600">{{ store.error }}</div>

      <ul v-else class="divide-y rounded-xl border">
        <li v-for="ngo in store.ngos" :key="ngo.id">
          <BaseRowButton
            :selected="store.ngo?.id === ngo.id"
            @click="select(ngo)"
            chevron
          >
            <template #leading v-if="ngo.logoUrl">
              <img :src="ngo.logoUrl" alt="" class="h-8 w-8 rounded-md object-cover" />
            </template>

            {{ ngo.name }}
            <template #sub>
              {{ ngo.campaign_title || ngo.description || 'Relief mission' }}
            </template>
          </BaseRowButton>

        </li>
      </ul>

      <div class="pt-3 flex justify-end gap-2">
        <button
          type="button"

          class="rounded-lg border px-4 py-2 text-sm transition hover:opacity-80 disabled:opacity-50"

          :disabled="!store.canSubmit || store.loading"
          @click="confirm"
        >
          {{ store.loading ? 'Submitting…' : 'Confirm selection' }}
        </button>
      </div>
    </div>

    <p
      v-if="store.message"
      class="rounded-lg border px-3 py-2 text-sm"
      :class="store.ok ? 'border-emerald-200 text-emerald-700 bg-emerald-50' : 'border-rose-200 text-rose-700 bg-rose-50'"
    >
      {{ store.message }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { useAidSupportStore, type Ngo } from '@/stores/AidSupport'

import BaseRowButton from './BaseRowButton.vue'

const store = useAidSupportStore()

function select(n: Ngo) {
  store.setNgo(n)
}



async function confirm() {
  if (!store.canSubmit) {
    store.ok = false
    store.message = store.aidType?.value === 'financial'
      ? 'Please enter the amount (BDT) before confirming.'
      : 'Please enter the quantity before confirming.'
    return
  }


  try {
    await store.submitAidSupport()
  } catch {}

}
</script>
