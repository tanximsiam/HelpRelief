<!-- src/components/InputField.vue -->
<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  modelValue: string | number | null
  label: string
  id?: string
  type?: string
  autocomplete?: string
  required?: boolean
  disabled?: boolean
  error?: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', v: string): void
  (e: 'blur'): void
  (e: 'focus'): void
}>()

const uid = computed(() => props.id ?? `in-${Math.random().toString(36).slice(2, 8)}`)
const isErr = computed(() => !!props.error)
function onInput(e: Event) {
  emit('update:modelValue', (e.target as HTMLInputElement).value)
}
</script>

<template>
  <div class="relative w-full">
    <input
      :id="uid"
      :type="type || 'text'"
      :value="modelValue ?? ''"
      :autocomplete="autocomplete"
      :required="required"
      :disabled="disabled"
      :aria-invalid="isErr"
      @input="onInput"
      @blur="$emit('blur')"
      @focus="$emit('focus')"
      placeholder=" "
      class="peer block w-full rounded-md border bg-white px-3 pt-6 pb-2 font-medium text-slate-900
             outline-none transition
             border-slate-300 focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600
             disabled:opacity-60 disabled:cursor-not-allowed
             [ &.error ]:border-red-500"
      :class="isErr ? 'border-red-500 focus:border-red-600 focus:ring-red-600/20' : ''"
    />

    <!-- Floating label -->
    <label
      :for="uid"
      class="pointer-events-none absolute left-3 top-2 text-xs text-slate-500 transition-all
             peer-placeholder-shown:top-4.5 peer-placeholder-shown:text-base
             peer-focus:top-2 peer-focus:text-xs peer-focus:text-blue-600"
    >
      {{ label }} <span v-if="required" class="text-red-600">*</span>
    </label>

    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>
