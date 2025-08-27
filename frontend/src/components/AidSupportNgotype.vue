<template>
  <div class="space-y-2">
    <h3 class="text-lg font-semibold">Choose the campaign</h3>

    <div v-if="!store.disaster" class="text-sm text-gray-500">
      Select a disaster first.
    </div>

    <div v-else>
      <div v-if="store.loading && !store.campaigns.length" class="text-sm text-gray-500">Loading campaigns…</div>
      <div v-else-if="store.error && !store.campaigns.length" class="text-sm text-red-600">{{ store.error }}</div>

      <ul v-else class="divide-y rounded-xl border">
        <li v-for="(campaign, idx) in store.campaigns" :key="campaign.id">
          <BaseRowButton
            :selected="store.campaign?.id === campaign.id"
            :class="[
              store.campaign?.id === campaign.id ? 'bg-gray-200 border-gray-300' : 'bg-white border-transparent',
              idx === 0 ? 'rounded-t-xl' : '',
              idx === store.campaigns.length - 1 ? 'rounded-b-xl' : ''
            ]"
            @click="select(campaign)"
            chevron
          >
            {{ campaign.name || (campaign.disaster_name + ' Campaign') }}
            <template #sub>
              {{ campaign.ngo_name || 'Relief mission' }}
            </template>
          </BaseRowButton>
        </li>
      </ul>

      <div class="pt-3">
        <label class="block text-sm font-medium mb-1" for="aid-desc">Additional details </label>
        <textarea
          id="aid-desc"
          rows="3"
          class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
          :placeholder="store.aidType?.value === 'financial' ? 'Ex: Preferred payment channel, reference' : 'Ex: Item type, packaging, pickup info'"
          :disabled="store.loading"
          v-model="store.description"
        />
      </div>

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
import { useAidSupportStore, type Campaign } from '@/stores/AidSupport'

import BaseRowButton from './BaseRowButton.vue'

const store = useAidSupportStore()

function select(c: Campaign) {
  store.setCampaign(c)
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