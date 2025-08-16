<template>
  <button
    @click="goToLogin"
    :class="[
      'rounded-lg px-3 py-1 text-2xl font-semibold transition-colors',
      variant === 'primary'
        ? 'bg-white text-blue-600 hover:bg-white/90'
        : 'bg-white text-blue-600 hover:bg-white/80'
    ]"
  >
    Login
  </button>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

function goToLogin() {
  router.push({ name: 'login', query: { redirect: '/dashboard' } })
}


defineProps<{
  variant?: 'primary' | 'secondary'
}>()

defineEmits<{ (e: 'login'): void }>()
</script>
