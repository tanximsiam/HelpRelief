<script setup lang="ts">
const props = defineProps<{ show: boolean; title?: string; maxWidth?: string }>()
const emit = defineEmits<{ (e: 'close'): void }>()

function onBackdrop(e: MouseEvent) {
  if (e.target === e.currentTarget) emit('close')
}
</script>

<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 z-40 flex items-start justify-center overflow-y-auto bg-black/40 px-4 py-12" @click="onBackdrop">
      <div
        class="relative w-full rounded-xl bg-white shadow-xl ring-1 ring-black/5"
        :class="maxWidth || 'max-w-4xl'"
      >
        <button
          type="button"
          class="absolute right-3.5 top-3.5 inline-flex h-5 w-5 items-center justify-center rounded-full border border-blue-600 text-blue-600 text-[15px] leading-none font-semibold transition
                 hover:bg-blue-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white"
          @click="emit('close')"
          aria-label="Close"
        >
          <span class="-mt-[1px]">×</span>
        </button>
        <div class="p-8">
          <h2 v-if="title" class="mb-6 text-3xl font-semibold text-slate-900">{{ title }}</h2>
          <slot />
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
.fade-enter-active,.fade-leave-active { transition: opacity .15s ease; }
.fade-enter-from,.fade-leave-to { opacity:0; }
</style>
