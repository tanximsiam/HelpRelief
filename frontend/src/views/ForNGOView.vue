<script setup lang="ts">
import PrimaryButton from '@/components/PrimaryButton.vue'
import NgoApplicationModal from '@/components/NgoApplicationModal.vue'
import { ref } from 'vue'

const showModal = ref(false)
</script>

<template>
  <main class="flex flex-col w-full items-center min-h-screen gap-6 p-8">
    <div class="flex gap-4 items-start">
      <div class="flex flex-col items-start gap-6">
        <h1 class="text-5xl font-bold text-blue-600">Empower Your NGO to Make a Difference.</h1>
        <p class="text-2xl/10 font-medium text-slate-900">
          HelpRelief enables NGOs to join a nationwide network for disaster response, resource coordination, and volunteer management. Register your organization to start helping communities in need more effectively.
        </p>
        <div class="flex gap-6">
          <PrimaryButton variant="primary" @click="showModal = true">Apply NGO Registration</PrimaryButton>
        </div>
      </div>
      <img src="https://placehold.co/600x300" alt="NGO Hero Image" class="w-600 rounded-lg shadow-lg">
    </div>
    <NgoApplicationModal v-model:show="showModal" hideButton />
  </main>
</template>
