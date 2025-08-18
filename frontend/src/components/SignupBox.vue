<!-- src/components/Signup.vue -->
<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { isAxiosError } from 'axios'
import { useAuth } from '@/stores/auth'
import InputField from '@/components/InputField.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import AppLink from '@/components/AppLink.vue'

const auth = useAuth()
const router = useRouter()
const route = useRoute()

const name = ref('')
const email = ref('')
const phone = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const errorMsg = ref('')

async function onSubmit() {
  errorMsg.value = ''
  try {
    await auth.register({
      name: name.value,
      email: email.value,
      phone: phone.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    const next = typeof route.query.redirect === 'string' ? route.query.redirect : '/'
    await router.replace(next)
  } catch (err: unknown) {
    if (isAxiosError(err)) {
      const data = err.response?.data as { message?: string; errors?: Record<string, string[]> } | undefined
      errorMsg.value =
        data?.message ||
        (data?.errors ? Object.values(data.errors).flat().join(' ') : '') ||
        err.message
    } else if (err instanceof Error) {
      errorMsg.value = err.message
    } else {
      errorMsg.value = 'Signup failed'
    }
  }
}

function onGoogleSignup() {
  const currentPath = route.query.redirect || window.location.pathname
  const encoded = encodeURIComponent(currentPath as string)
  const base = import.meta.env.VITE_API_BASE
  window.location.href = `${base}/auth/redirect?redirect=${encoded}`
}

const emit = defineEmits<{ (e: 'switch', to: 'login' | 'signup'): void }>()

</script>

<template>
  <div class="flex gap-6 bg-white rounded-xl p-6 shadow justify-self-center">
    <img src="https://placehold.co/500x650" alt="Signup Image" class="rounded-xl">
    <div class="flex flex-col justify-center w-full">
      <form @submit.prevent="onSubmit" class="w-full max-w-md">
        <h1 class="text-2xl font-bold mb-2">Create your HelpRelief account</h1>
        <p class="text-lg font-medium text-slate-600 mb-6">Sign up with your details or use Google.</p>

        <div class="space-y-4 mb-4">
          <InputField v-model="name" label="Full Name" type="text" autocomplete="name" required />
          <InputField v-model="email" label="Email" type="email" autocomplete="email" required />
          <InputField v-model="phone" label="Phone" type="tel" autocomplete="tel" required/>
          <InputField v-model="password" label="Password" type="password" autocomplete="new-password" required />
          <InputField v-model="passwordConfirmation" label="Confirm Password" type="password" autocomplete="new-password" required />
        </div>

        <p v-if="errorMsg" class="text-red-600 text-sm mb-3">{{ errorMsg }}</p>

        <PrimaryButton
          class="w-full py-2 text-lg"
          variant="primary"
          type="button"
          :disabled="auth.loading || !name || !email || !password || !passwordConfirmation || password !== passwordConfirmation"
          @click="onSubmit"
        >
          {{ auth.loading ? 'Creating account…' : 'Sign up' }}
        </PrimaryButton>
      </form>

      <div class="flex items-center my-6">
        <div class="flex-grow border-t border-gray-300"></div>
        <span class="px-3 text-gray-500 text-sm">or</span>
        <div class="flex-grow border-t border-gray-300"></div>
      </div>

      <button
        type="button"
        class="w-full flex items-center justify-center gap-2 py-2 px-4 rounded-lg shadow-sm text-white
               bg-slate-400 font-medium
               hover:bg-slate-500 active:bg-slate-600 transition-colors"
        @click="onGoogleSignup"
      >
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="h-5 w-5" />
        <span>Sign up with Google</span>
      </button>
      <div class="text-center mt-4">
        <span class="text-sm text-slate-600">Already have an account? </span>
        <AppLink to="#" variant="secondary"
          @click.prevent="emit('switch', 'login')">
          Login now
        </AppLink>
      </div>
    </div>
  </div>
</template>
