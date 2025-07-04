<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const page = usePage();

const successMessage = ref(page.props.flash.success || null);
const errorMessage = ref(page.props.flash.error || null);

// Watch for changes in flash messages and clear after a few seconds
watch(() => page.props.flash.success, (newValue) => {
    successMessage.value = newValue;
    if (newValue) {
        setTimeout(() => successMessage.value = null, 3000); // Clear after 3 seconds
    }
});

watch(() => page.props.flash.error, (newValue) => {
    errorMessage.value = newValue;
    if (newValue) {
        setTimeout(() => errorMessage.value = null, 3000); // Clear after 3 seconds
    }
});
</script>

<template>
  <div class="relative z-50">
    <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 shadow-md" role="alert">
      <strong class="font-bold">Success!</strong>
      <span class="block sm:inline ml-2">{{ successMessage }}</span>
      <span @click="successMessage = null" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
        <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.103l-2.651 3.746a1.2 1.2 0 1 1-1.697-1.697l3.746-2.651-3.746-2.651a1.2 1.2 0 1 1 1.697-1.697L10 8.897l2.651-3.746a1.2 1.2 0 1 1 1.697 1.697L11.103 10l3.746 2.651a1.2 1.2 0 0 1 0 1.698z"/></svg>
      </span>
    </div>

    <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 shadow-md" role="alert">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline ml-2">{{ errorMessage }}</span>
      <span @click="errorMessage = null" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.103l-2.651 3.746a1.2 1.2 0 1 1-1.697-1.697l3.746-2.651-3.746-2.651a1.2 1.2 0 1 1 1.697-1.697L10 8.897l2.651-3.746a1.2 1.2 0 1 1 1.697 1.697L11.103 10l3.746 2.651a1.2 1.2 0 0 1 0 1.698z"/></svg>
      </span>
    </div>
  </div>
</template>