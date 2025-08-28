<template>
  <div class="space-y-2">
    <h3 class="text-lg font-semibold">What help do you want to provide?</h3>

    <ul class="divide-y rounded-xl border">
      <li
        v-for="(opt, idx) in options"
        :key="opt.value"
    >
        <BaseRowButton
          :selected="store.aidType?.value === opt.value"
          :class="[
            store.aidType?.value === opt.value ? 'bg-gray-200 border-gray-300' : 'bg-white border-transparent',
            idx === 0 ? 'rounded-t-xl' : '',
            idx === options.length - 1 ? 'rounded-b-xl' : ''
          ]"
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
import { defineProps } from 'vue'

defineProps<{ selected: string | null }>()
defineEmits<{ (e: 'update:selected', value: string): void }>()

const aidTypes = [
  { label: 'FINANCIAL', description: 'Monetary help', value: 'financial' },
  { label: 'MEDICAL', description: 'First-aid / blood', value: 'medical' },
  { label: 'RESOURCES', description: 'Food / goods / supplies', value: 'resource' },
]
</script>
