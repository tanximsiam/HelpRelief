<template>
  <component
    :is="isExternal ? 'a' : RouterLink"
    v-bind="linkAttrs"
    :class="[
      'text-2xl font-medium inline-flex items-center gap-1 transition-colors',
      variant === 'primary'
        ? 'text-blue-600 hover:text-blue-700 underline underline-offset-4'
        : 'text-slate-700 hover:text-slate-900 hover:underline underline-offset-4',
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
  to: RouteLocationRaw | string
  variant?: 'primary' | 'secondary'
  replace?: boolean
  external?: boolean
  disabled?: boolean
}>()

const isExternal = computed(() =>
  props.external ?? (typeof props.to === 'string' && /^(https?:)?\/\//.test(props.to))
)

const linkAttrs = computed(() =>
  isExternal.value
    ? { href: props.to as string, target: '_blank', rel: 'noopener noreferrer' }
    : { to: props.to as RouteLocationRaw, replace: props.replace }
)
</script>
