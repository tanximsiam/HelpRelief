<template>
  <button
    :disabled="disabled"
    @click="handleClick"
    :class="[
      'rounded-lg px-4 py-2 text-lg font-medium transition-colors flex items-center',
      disabled && 'opacity-60 cursor-not-allowed',
      'bg-indigo-600 hover:bg-indigo-700 text-white border-none shadow-md'
    ]"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const props = defineProps<{
  to?: string
  replace?: boolean
  external?: boolean
  disabled?: boolean
}>()

const emit = defineEmits<{ (e: 'click', ev: MouseEvent): void }>()

async function handleClick(ev: MouseEvent) {
  if (props.disabled) return

  if (props.to) {
    if (props.external) {
      window.open(props.to, '_blank')
      return
    }
    if (props.replace) {
      await router.replace(props.to)
    } else {
      await router.push(props.to)
    }
    return
  }

  emit('click', ev)
}
</script>
