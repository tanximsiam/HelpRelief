<script setup lang="ts">
interface Props {
  disabled?: boolean;
  type?: 'button' | 'submit' | 'reset';
}

defineProps<Props>();
defineEmits<{
  click: [];
}>();
</script>

<template>
  <button
    :type="type || 'button'"
    :disabled="disabled"
    @click="$emit('click')"
    class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
  >
    <slot />
  </button>
</template>
