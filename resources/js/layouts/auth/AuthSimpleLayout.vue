<script setup lang="ts">
// We still need Head for page title and Link if the logo is clickable
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-700 selection:bg-indigo-500 selection:text-white p-6 md:p-10">

        <Link :href="route('home')" class="mb-8 block"> <img src="/logo_HanyuVocabTrackerWhite.png" alt="Hanyu VocabTracker Logo" class="h-24 w-auto mx-auto"> <span class="sr-only">Hanyu VocabTracker</span> </Link>

        <div class="w-full max-w-sm bg-white p-6 rounded-lg shadow-lg">
            <div class="flex flex-col gap-8">
                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-medium text-gray-900">{{ title }}</h1>
                    <p class="text-center text-sm text-gray-600">{{ description }}</p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
