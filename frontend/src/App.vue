<script setup lang="ts">
import {  RouterView } from 'vue-router'
import NavBar from './components/NavBar.vue';
</script>

<template>
  <header>

  </header>
  <div class="flex flex-col bg-slate-50 min-h-screen">
    <NavBar />
    <div class="max-w-[1440px] w-full mx-auto">
      <RouterView />
    </div>
  </div>

</template>
