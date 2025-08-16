<script setup lang="ts">
import { computed } from 'vue'

interface Option { label: string; value: string }
const props = defineProps<{ modelValue: string | null; options: Option[]; name?: string; inline?: boolean }>()
const emit = defineEmits<{ (e: 'update:modelValue', v: string): void }>()
const groupName = computed(() => props.name || `rg-${Math.random().toString(36).slice(2,8)}`)
function onChange(e: Event) { emit('update:modelValue', (e.target as HTMLInputElement).value) }
</script>
<template>
  <div :class="inline ? 'flex items-center gap-6' : 'flex flex-col gap-3'">
    <label v-for="opt in options" :key="opt.value" class="flex cursor-pointer select-none items-center gap-2 text-base font-medium text-slate-700">
      <input type="radio" :name="groupName" :value="opt.value" :checked="opt.value===modelValue" class="h-4 w-4 border-slate-400 text-blue-600 accent-blue-600 focus:ring-blue-500" @change="onChange" />
      <span>{{ opt.label }}</span>
    </label>
  </div>
</template>
