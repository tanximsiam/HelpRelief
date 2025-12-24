<!-- src/components/NavBar.vue -->
<template>
  <header class="bg-blue-600 text-white shadow rounded-b-xl">
    <nav class="mx-auto max-w-screen-xl w-full px-4 sm:px-6 lg:px-8">
      <div class="flex h-24 items-center justify-between">
        <!-- Left: Logo -->
        <button @click="$router.push('/')" class="flex items-center gap-2 shrink-0">
          <span class="text-3xl sm:text-4xl font-bold tracking-tight">HelpRelief</span>
        </button>

        <!-- Desktop navigation -->
        <div class="hidden md:flex items-center gap-6">
          <NavBarButtons
            v-for="l in desktopLinks"
            :key="l.to"
            :label="l.label"
            :to="l.to"
          />
          <LoginButton v-if="!auth.isAuthenticated" variant="primary" />
          <ProfileButton
            v-else
            :userName="auth.user?.name || 'User'"
            @profile="router.push('/profile')"
            @logout="auth.logout()"
          />
        </div>

        <!-- Mobile buttons -->
        <div class="md:hidden flex items-center gap-3">
          <LoginButton v-if="!auth.isAuthenticated" variant="primary" />
          <ProfileButton
            v-else
            :userName="auth.user?.name || 'User'"
            @profile="router.push('/profile')"
            @logout="auth.logout()"
          />
          <button
            class="inline-flex items-center justify-center rounded-md p-2 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white"
            @click="open = !open" aria-label="Toggle navigation" :aria-expanded="open.toString()"
          >
            <svg v-if="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </nav>

    <!-- Mobile panel -->
    <transition name="fade">
      <div v-show="open" class="md:hidden border-t border-white/15">
        <ul class="px-4 py-4 space-y-1 text-sm">
          <li v-for="l in desktopLinks" :key="l.to">
            <RouterLink
              :to="l.to"
              class="block rounded px-3 py-2 hover:bg-white/10"
              @click="open = false"
            >{{ l.label }}</RouterLink>
          </li>
        </ul>
      </div>
    </transition>
  </header>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import NavBarButtons from './NavBarButtons.vue'
import LoginButton from './LoginButton.vue'
import ProfileButton from './ProfileButton.vue'
import { useAuth } from '@/stores/auth'

type Link = { label: string; to: string }

// Props kept for backwards compatibility (not currently used internally)
defineProps<{ isAuthed?: boolean; userName?: string; links?: Link[] }>()

const router = useRouter()
const auth = useAuth()
const open = ref(false)

const guestLinks: Link[] = [
  { label: 'Home', to: '/' },
  { label: 'For NGOs', to: '/ngo-apply' }
]

const authedLinks: Link[] = [
  { label: 'Home', to: '/' },
  { label: 'Dashboard', to: '/dashboard' }
]

const generalLinks: Link[] = [
  { label: 'Donation Reports', to: '/donation-reports' }
]

const adminLinks: Link[] = [
  { label: 'Onboarding', to: '/onboarding' }
]

// Merge links based on role
const desktopLinks = computed<Link[]>(() => {
  const base = auth.isAuthenticated ? [...authedLinks] : [...guestLinks]
  if (auth.isAuthenticated && auth.isGeneral) base.push(...generalLinks)
  if (auth.isAuthenticated && auth.ngoPrivilegeRole === 'ngo_admin') base.push(...adminLinks)
  return base
})

onMounted(() => { if (auth.token && !auth.user) auth.fetchUser() })
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
