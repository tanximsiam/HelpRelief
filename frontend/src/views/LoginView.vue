<script setup lang="ts">
import { ref } from 'vue'
import LoginBox from '@/components/LoginBox.vue';
import SignupBox from '@/components/SignupBox.vue';

const current = ref<'login'|'signup'>('login')


</script>

<template>
  <main class="flex flex-col w-full items-center min-h-screen gap-6 p-8">
    <LoginBox v-if="current === 'login'" @switch="current = $event" />
    <SignupBox v-else @switch="current = $event" />
  </main>
</template>
