<script setup lang="ts">
const props = defineProps<{ show: boolean }>()
const emit = defineEmits<{ (e: 'close'): void }>()


import AidTypePicker from '@/components/AidSupportType.vue'
import DisasterPicker from '@/components/AidSupportDisasterType.vue'
import AidQuantity from '@/components/AidQuantity.vue'
import NgoPicker from '@/components/AidSupportNgotype.vue'
</script>

<template>
  <transition name="fade-scale">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center px-4">
  <!-- backdrop -->
  <div class="absolute inset-0 bg-black/40" @click="emit('close')" aria-hidden="true"></div>

      <!-- modal -->
      <div
        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden"
        role="dialog"
        aria-modal="true"
        aria-label="Aid Support"
        @click.stop
        tabindex="0"
      >
        <header class="flex items-center gap-4 px-6 py-4 border-b">
          <div class="flex-1">
            <h3 class="text-xl font-semibold text-gray-900">Offer Aid</h3>
          </div>

          <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-200" aria-label="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </header>

        <main class="p-6 space-y-6 max-h-[70vh] overflow-auto">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <AidTypePicker />
            </div>

            <div>
              <DisasterPicker />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <AidQuantity />
            </div>
            <div>
              <NgoPicker />
            </div>
          </div>
        </main>

  <!-- footer removed as requested -->
      </div>
    </div>
  </transition>
</template>

<style scoped>
.fade-scale-enter-active,.fade-scale-leave-active { transition: all .18s cubic-bezier(.2,.8,.2,1); }
.fade-scale-enter-from,.fade-scale-leave-to { opacity:0; transform: translateY(6px) scale(.995); }
.fade-scale-enter-to,.fade-scale-leave-from { opacity:1; transform: translateY(0) scale(1); }

/* small adjustments for scrollbar on modal content */
main::-webkit-scrollbar { width: 8px; }
main::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 999px; }
</style>



