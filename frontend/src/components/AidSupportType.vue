<template>
  <div class="space-y-2">
    <h3 class="text-lg font-semibold">What help do you want to provide?</h3>

    <ul class="divide-y rounded-xl border">
      <li v-for="opt in options" :key="opt.value">
        <BaseRowButton
          :selected="store.aidType?.value === opt.value"
          chevron
          @click="select(opt)"
        >
          {{ opt.label }}
          <template #sub>{{ opt.hint }}</template>
        </BaseRowButton>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseRowButton from './BaseRowButton.vue'
import { useAidSupportStore, type AidOption } from '@/stores/AidSupport'

const store = useAidSupportStore()
const options = computed<AidOption[]>(() => store.defaultAidOptions)

function select(opt: AidOption) {
  store.setAidType(opt)
}
</script>
