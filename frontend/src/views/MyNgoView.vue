<template>
  <div>
    <!-- empty shell; modal opens on mount -->
    <NgoReportModal :show="show" @close="handleClose" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import NgoReportModal from '@/components/NgoReportModal.vue'

const show = ref(true)
const router = useRouter()

function handleClose() {
  // navigate back to previous page when modal closes
  router.back()
}

onMounted(() => {
  // ensure modal is shown
  show.value = true
})
</script>

<style scoped>
/* no styles */
</style>

<!-- This view was used temporarily to open the NGO Report modal.
The modal is now opened over the dashboard via query param `openNgoReport`.
File kept as a placeholder to avoid broken imports; remove if you prefer a hard delete. -->
