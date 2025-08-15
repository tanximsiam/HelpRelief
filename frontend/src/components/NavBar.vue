<!-- src/components/NavBar.vue -->
<template>
  <header class="bg-blue-600 text-white shadow rounded-bl-xl rounded-br-xl">
    <nav class="mx-auto px-4 sm:px-6 lg:px-8 w-[1440px]">
      <div class="flex h-36 items-center justify-between">
        <!-- Mobile menu button -->
        <button
          class="md:hidden inline-flex items-center justify-center rounded-md p-2 hover:bg-white/10 focus:outline-none"
          @click="open = !open" aria-label="Toggle navigation">
          <svg v-if="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>



        <!-- Left: Logo -->
        <div class="flex items-center gap-2">
          <button @click="$router.push('/')" class="flex items-center gap-2">
            <span class="text-6xl font-bold tracking-tight">HelpRelief</span>
          </button>
        </div>

        <div class="flex items-center gap-8">
          <!-- Center: Links (desktop) -->
          <NavBarButtons
            v-for="l in links"
            :key="l.to"
            :label="l.label"
            :to="l.to"
          />

          <!-- Right: Login pill OR Username -->
          <LoginButton v-if="!auth.isAuthenticated" variant="primary"/>
          <ProfileButton v-else :userName="auth.user?.name || 'Account'" />
        </div>
      </div>
    </nav>

    <!-- Mobile panel -->
    <!-- <div v-show="open" class="md:hidden border-t border-white/15">
      <ul class="space-y-1 px-4 py-3 text-sm">
        <li v-for="l in links" :key="l.to">
          <RouterLink
            :to="l.to"
            class="block rounded px-2 py-2 hover:bg-white/10"
            @click="open = false"
            >{{ l.label }}</RouterLink>
        </li>
        <li>
          <div class="mt-2 rounded bg-white/10 px-3 py-2 font-semibold flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/>
            </svg>
            {{ orgName }}
          </div>
        </li>
      </ul>
    </div> -->
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
// import { RouterLink } from 'vue-router'
import NavBarButtons from './NavBarButtons.vue'
import LoginButton from './LoginButton.vue';
import ProfileButton from './ProfileButton.vue';
import { useAuth } from '@/stores/auth';



type Link = { label: string; to: string }

const props = defineProps<{
  isAuthed?: boolean
  userName?: string
  links?: Link[]
}>()

const auth = useAuth()

const open = ref(false)

const links = props.links ?? [
  { label: 'For Users', to: '/for-users' },
  { label: 'About Us', to: '/about' },
  { label: 'Our partners', to: '/partners' },
]

onMounted(() => { if (auth.token && !auth.user) auth.fetchUser() })

defineEmits<{ (e: 'login'): void }>()
</script>
