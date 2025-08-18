<template>
  <component
    :is="tag"
    v-bind="linkAttrs"
    v-on="$attrs"
    :class="[
      'font-medium inline-flex items-center gap-1 transition-colors',
      variant === 'primary'
        ? 'text-blue-600 text-2xl hover:text-blue-700 underline underline-offset-4'
        : 'text-slate-700 text-sm hover:text-slate-900 hover:underline underline-offset-4',
      disabled && 'pointer-events-none opacity-60'
    ]"
  >
    <slot></slot>
  </component>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, type RouteLocationRaw } from 'vue-router'

const props = defineProps<{
  to?: RouteLocationRaw | string | null
  variant?: 'primary' | 'secondary'
  replace?: boolean
  external?: boolean
  disabled?: boolean
}>()

const isAction = computed(() => props.to === '#' || props.to == null || props.to === '')
const isExternal = computed(() =>
  props.external ?? (typeof props.to === 'string' && /^(https?:)?\/\//.test(props.to))
)

const tag = computed(() => (isAction.value ? 'button' : (isExternal.value ? 'a' : RouterLink)))

const linkAttrs = computed(() => {
  if (isAction.value) return { type: 'button' }
  if (isExternal.value) return { href: props.to as string, target: '_blank', rel: 'noopener noreferrer' }
  return { to: props.to as RouteLocationRaw, replace: props.replace }
})
</script>
