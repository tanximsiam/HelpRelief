<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/stores/auth' // adjust path

const route = useRoute()
const router = useRouter()
const auth = useAuth()

onMounted(async () => {
  const token = route.query.token as string
  const redirect = (route.query.redirect as string) || '/'

  if (token) {
    await auth.setToken(token)
    router.replace(redirect)
  } else {
    router.replace('/login')
  }
})
</script>

<template>
  <div class="flex justify-center items-center h-screen">
    <p class="text-lg text-gray-600">Logging you in...</p>
  </div>
</template>
