<template>
  <section class="max-w-3xl mx-auto p-4 md:p-8 space-y-6">
    <AidTypePicker />
    <DisasterPicker />
    <AidQuantity />
    <NgoPicker />
  </section>
</template>

<script setup lang="ts">
import AidTypePicker from '@/components/AidSupportType.vue'
import DisasterPicker from '@/components/AidSupportDisasterType.vue'
import AidQuantity from '@/components/AidQuantity.vue'
import NgoPicker from '@/components/AidSupportNgotype.vue'

</script>