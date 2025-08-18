<template>
  <div class="mx-auto max-w-3xl space-y-8">
    <TaskCreatorPanel @created="onCreated" />

    <!-- <AssignTaskPanel :ngoId="ngoId" :disasterId="disasterId" @assigned="onAssigned" />

    <RejectTaskPanel :disasterId="disasterId" @rejected="onRejected" />

    <StandaloneTaskForm :ngoId="ngoId" :disasterId="disasterId" /> -->
  </div>
</template>

<script setup lang="ts">
// import AssignTaskPanel from '@/components/AssignTaskPanel.vue';
// import RejectTaskPanel from '@/components/RejectTaskPanel.vue';
// import StandaloneTaskForm from '@/components/StandAloneTaskForm.vue';
import TaskCreatorPanel from '@/components/TaskCreatorPanel.vue';
import TaskController from '@/components/TaskController.vue';

const ngoId = 3            // pass from your page/store
const disasterId = 12
function onAssigned(e:any){ /* toast or refresh */ }
function onRejected(e:any){ /* toast or refresh */ }
function onCreated(e:any){ /* toast or refresh */ }
</script>
