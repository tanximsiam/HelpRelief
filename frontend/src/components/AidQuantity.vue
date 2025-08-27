<template>
  <div class="space-y-2">
    <h3 class="text-lg font-semibold">
      {{ isFinancial ? 'Enter amount (BDT)' : 'Enter quantity' }}
    </h3>

    <label class="block">
      <span class="text-sm font-medium">
        {{ isFinancial ? 'Amount (BDT)' : 'Quantity' }} <span class="text-rose-600">*</span>
      </span>
      <div class="mt-1 flex items-stretch gap-2">
        <span v-if="isFinancial" class="inline-flex items-center rounded-lg border px-3 text-sm">৳</span>
        <input
          v-model="qty"
          :inputmode="isFinancial ? 'numeric' : 'decimal'"
          type="number"
          min="0"
          step="any"
          class="w-full rounded-lg border px-3 py-2"
          :placeholder="isFinancial ? 'e.g., 10000' : 'e.g., 20'"
          @blur="normalize"
          required
        />
      </div>
      <p v-if="showError" class="mt-1 text-xs text-rose-600">This field is required.</p>
    </label>

    <p class="text-xs text-gray-500">
      {{
        isFinancial
          ? 'Enter the amount you wish to contribute in BDT.'
          : 'Provide a numeric quantity.'
      }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useAidSupportStore } from '@/stores/AidSupport'

const store = useAidSupportStore()
const isFinancial = computed(() => store.aidType?.value === 'financial')

const qty = ref<string>(store.quantity)

watch(() => store.quantity, v => { if (v !== qty.value) qty.value = v })
watch(qty, v => store.setQuantity(v ?? ''))

function normalize() {
  qty.value = (qty.value ?? '').toString().trim()
}

const showError = computed(() => !store.quantity.value?.toString().trim().length)
</script>

<style scoped>
input { outline: none; }
input:focus { box-shadow: 0 0 0 2px rgba(0,0,0,.05); border-color: #d1d5db; }
</style>
