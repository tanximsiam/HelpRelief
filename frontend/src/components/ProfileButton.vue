<template>
  <div class="relative" ref="dropdownRef">
    <button
      class="rounded-full px-3 py-1 text-2xl font-medium flex items-center gap-3"
      @click="open = !open"
    >
      {{ userName }}
      <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/>
      </svg>
    </button>

    <ul
      v-if="open"
      class="absolute right-0 mt-2 w-40 bg-white border rounded shadow z-50 text-base"
    >
      <li>
        <button class="w-full text-left px-4 py-2 hover:bg-gray-100 text-slate-900 font-medium" @click="goProfile">Profile</button>
      </li>
      <li>
        <button class="w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600 font-medium" @click="logout">Logout</button>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'

defineProps<{ userName: string }>()
const emit = defineEmits(['profile', 'logout'])

const open = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

function goProfile() {
  emit('profile')
  open.value = false
}

function logout() {
  emit('logout')
  open.value = false
}

function handleClickOutside(event: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>
