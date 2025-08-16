<!-- src/components/LoginBox.vue -->
<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { isAxiosError } from 'axios'
import { useAuth } from '@/stores/auth'
import InputField from '@/components/InputField.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'

const auth = useAuth()
const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')
const errorMsg = ref('')

async function onSubmit() {
  errorMsg.value = ''
  try {
    await auth.login({ email: email.value, password: password.value })
    const next = typeof route.query.redirect === 'string' ? route.query.redirect : '/dashboard'
    await router.replace(next)
  } catch (err: unknown) {
    if (isAxiosError(err)) {
      errorMsg.value = (err.response?.data as { message?: string })?.message ?? err.message
    } else if (err instanceof Error) {
      errorMsg.value = err.message
    } else {
      errorMsg.value = 'Login failed'
    }
  }
}
</script>

<template>
  <div class="flex gap-6 bg-white rounded-xl p-6 shadow justify-self-center">
    <img src="https://placehold.co/300x400" alt="Login Image" class="rounded-xl">

    <form @submit.prevent="onSubmit" class="w-full max-w-md">
      <h1 class="text-2xl font-bold mb-2">Welcome to HelpRelief</h1>
      <p class="text-lg font-medium text-slate-600 mb-6">Please enter your email and password to login.</p>

      <div class="space-y-4 mb-4">
        <InputField
          v-model="email"
          label="Email"
          type="email"
          autocomplete="email"
          required
        />
        <InputField
          v-model="password"
          label="Password"
          type="password"
          autocomplete="current-password"
          required
        />
      </div>

      <p v-if="errorMsg" class="text-red-600 text-sm mb-3">{{ errorMsg }}</p>

      <PrimaryButton
        class="w-full py-2 text-lg"
        variant="primary"
        type="button"
        :disabled="auth.loading || !email || !password"
        @click="onSubmit"
      >
        {{ auth.loading ? 'Signing in…' : 'Login' }}
      </PrimaryButton>
    </form>

  </div>
</template>
