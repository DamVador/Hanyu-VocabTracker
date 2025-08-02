<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineOptions({
    layout: AuthenticatedLayout,
    inheritAttrs: false,
});
</script>

<template>
    <Head title="Subscription Successful" />

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 xl:max-w-screen-xl 2xl:max-w-screen-2xl">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 sm:p-8 md:p-10 text-center">
            <div class="flex flex-col items-center justify-center space-y-4">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-24 w-24 text-green-500 animate-pulse"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-gray-800">
                    Subscription Successful!
                </h2>

                <p class="text-base sm:text-lg text-gray-600 mt-2 max-w-lg">
                    Congratulations! Your premium subscription is now active. You have full access to all advanced features.
                </p>

                <Link :href="route('dashboard')"
                      class="mt-6 inline-flex items-center px-6 py-3 border border-transparent text-base font-semibold rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    Return to Dashboard
                </Link>
            </div>
        </div>
    </div>
</template>