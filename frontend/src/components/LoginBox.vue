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
    const next = typeof route.query.redirect === 'string' ? route.query.redirect : '/'
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

function onGoogleLogin() {
  const currentPath = route.query.redirect || window.location.pathname
  const encoded = encodeURIComponent(currentPath as string)
  const base = import.meta.env.VITE_API_BASE

  window.location.href = `${base}/auth/google?redirect=${encoded}`
}
</script>

<template>
  <div class="flex gap-6 bg-white rounded-xl p-6 shadow justify-self-center">
    <img src="https://placehold.co/300x400" alt="Login Image" class="rounded-xl">
    <div class="flex flex-col justify-center w-full">
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
      <div class="flex items-center my-6">
        <div class="flex-grow border-t border-gray-300"></div>
        <span class="px-3 text-gray-500 text-sm">or</span>
        <div class="flex-grow border-t border-gray-300"></div>
      </div>

      <button
        type="button"
        class="w-full flex items-center justify-center gap-2 py-2 px-4 rounded-lg shadow-sm  text-white
              bg-slate-400 font-medium
              hover:bg-slate-500 active:bg-slate-600 transition-colors"
        @click="onGoogleLogin"
      >
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="h-5 w-5" />
        <span>Login with Google</span>
      </button>
    </div>




  </div>
</template>
