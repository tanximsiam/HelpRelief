<template>
  <button
    :disabled="disabled"
    @click="onClick"
    type="button"
    class="w-full flex justify-between items-center gap-4 px-4 border transition 
           hover:bg-gray-100 hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-black/5"

    :class="[
      selected ? 'bg-gray-200 border-gray-300' : 'bg-white border-transparent',
      disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
      dense ? 'py-2' : 'py-3'
    ]"
  >
    <!-- Leading + Label -->
    <div class="flex flex-col items-start text-left min-w-0 flex-1">
      <span class="text-sm font-medium truncate">
        <slot />
      </span>
      <span v-if="$slots.sub" class="text-xs text-gray-500 truncate mt-0.5 leading-snug">
        <slot name="sub" />
      </span>
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

function onClick(ev: MouseEvent) {
  if (!props.disabled) emit('click', ev)
}
</script>
