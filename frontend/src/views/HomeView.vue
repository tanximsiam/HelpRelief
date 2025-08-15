<script setup lang="ts">
import AppLink from '@/components/AppLink.vue';
import PrimaryButton from '@/components/PrimaryButton.vue'

</script>

<template>
  <main class="flex flex-col w-full items-center min-h-screen gap-6 p-8">
    <div class="flex gap-4 items-start">
      <div class="flex flex-col items-start gap-6">
        <h1 class="text-5xl font-bold text-blue-600">Connecting Aid with Urgency.</h1>
        <p class="text-2xl/10 font-medium text-slate-900">In times of crisis, every second counts. HelpRelief bridges NGOs, volunteers, and those in need— ensuring faster, smarter, and more coordinated disaster response across Bangladesh.</p>
        <div class="flex gap-6">
          <PrimaryButton variant="primary" to="/donate">Offer Help</PrimaryButton>
          <AppLink variant="primary" to="/about">or report a disaster</AppLink>
        </div>
      </div>

      <img src="https://placehold.co/600x300" alt="Hero Image" class="w-600 rounded-lg shadow-lg">
    </div>
  </main>
</template>
