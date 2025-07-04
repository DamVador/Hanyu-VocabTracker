<script setup lang="ts">
import Navbar from '@/Components/Navbar.vue';
import FlashMessages from '@/Components/FlashMessages.vue';

defineProps({
  title: String,
});
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <Navbar />

    <header v-if="title || $slots.header" class="bg-white shadow">
      <div class="max-w-full mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <h2 v-if="title" class="font-semibold text-xl text-gray-800 leading-tight">
          {{ title }}
        </h2>
        <slot name="header" v-else />
      </div>
    </header>

    <main class="flex-grow py-6 sm:py-8">
      <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <FlashMessages />
      </div>

      <slot />
    </main>

    </div>
</template>