<template>
  <button
    :disabled="disabled"
    @click="onClick"
    class="w-full flex items-start justify-between gap-3 px-4 py-3 rounded-xl border transition
           hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-black/5"
    :class="[
      selected ? 'bg-gray-50 border-gray-200' : 'border-transparent',
      disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
      dense ? 'py-2' : 'py-3'
    ]"
    type="button"
  >
    <div class="flex items-start gap-3 min-w-0">
      <div v-if="$slots.leading" class="shrink-0">
        <slot name="leading" />
      </div>
      <div class="min-w-0">
        <div class="text-sm font-medium truncate">
          <slot />
        </div>
        <div v-if="$slots.sub" class="text-xs text-gray-500 truncate">
          <slot name="sub" />
        </div>
      </div>
    </div>

    <div class="shrink-0 flex items-center gap-2">
      <slot name="trailing" />
      <span v-if="chevron && !$slots.trailing" aria-hidden="true">›</span>
    </div>
  </button>
</template>

<script setup lang="ts">
const props = defineProps<{
  selected?: boolean
  disabled?: boolean
  dense?: boolean
  chevron?: boolean
}>()
const emit = defineEmits<{ (e: 'click', ev: MouseEvent): void }>()
function onClick(ev: MouseEvent) { if (!props.disabled) emit('click', ev) }
</script>
