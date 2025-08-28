<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-end h-16">
          <div class="flex items-center">
            <SecondaryButton
              @click="$router.back()"
              class="px-4 py-2"
            >
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
              Back to Dashboard
            </SecondaryButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <UserProfileView />
    </div>
  </div>
</template>

<script setup lang="ts">
import UserProfileView from '@/components/UserProfileView.vue'
import SecondaryButton from '@/components/SecondaryButton.vue'
</script>
