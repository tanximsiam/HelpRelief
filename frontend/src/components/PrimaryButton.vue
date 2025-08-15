<template>
  <button
    :disabled="disabled"
    @click="handleClick"
    :class="[
      'rounded-lg px-4 py-2 text-2xl font-medium transition-colors',
      disabled && 'opacity-60 cursor-not-allowed',
      variant === 'primary'
        ? 'bg-blue-600 text-white hover:bg-blue-700'
        : 'bg-slate-900 text-white hover:bg-slate-700'
    ]"
  >
    <slot>Button</slot>
  </button>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const props = defineProps<{
  variant?: 'primary' | 'secondary'
  to?: string            // if present, navigate on click
  replace?: boolean      // use router.replace instead of push
  external?: boolean     // open absolute link in new tab
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

