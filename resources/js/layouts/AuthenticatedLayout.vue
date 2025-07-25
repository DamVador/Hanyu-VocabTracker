<script setup lang="ts">
import Navbar from '@/components/Navbar.vue';
import FlashMessages from '@/components/FlashMessages.vue';

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

        <footer class="bg-white mt-8 py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-500 text-sm">
                &copy; {{ new Date().getFullYear() }} HanyuVocabTracker
                <span class="mx-2">|</span> Feedbacks welcome: <a href="mailto:votre.email@example.com" class="text-blue-600 hover:text-blue-800">contact@hanyu-vocabtracker.com</a>
            </div>
        </footer>
    </div>
</template>