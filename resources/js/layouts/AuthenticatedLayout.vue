<script setup>
import Navbar from '@/Components/Navbar.vue'; // Make sure your Navbar component path is correct
import FlashMessages from '@/Components/FlashMessages.vue'; // We'll create this next
import { Head } from '@inertiajs/vue3'; // For setting the document title

// You can define common props here for the layout if needed,
// e.g., if you want to pass user info directly to the layout, though often Navbar handles this.
defineProps({
  title: String, // Optional: for dynamic page titles in the header slot
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