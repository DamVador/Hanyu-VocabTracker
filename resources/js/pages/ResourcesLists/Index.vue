<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    resourcesList: Array,
});
</script>

<template>
    <Head title="Vocabulary Lists" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight mb-6">
                        Download HSK Vocabulary Lists
                    </h2>

                    <p class="mb-8 text-gray-700">
                        Enhance your Chinese vocabulary by downloading various resources such as official HSK word lists. These CSV files can be easily
                        imported into your dictionary using the "Import Words from CSV" feature on your Dashboard.
                    </p>

                    <div v-if="resourcesList && resourcesList.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="list in resourcesList" :key="list.name" class="bg-gray-50 p-6 rounded-lg shadow-sm">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ list.name }}</h3>
                            <p class="text-gray-600 mb-4">{{ list.description }}</p>
                            <a :href="list.file_url" download
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
                                Download CSV
                                <svg class="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </a>
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-500 py-8">
                        No resources available at the moment.
                    </div>

                    <div class="mt-8 text-center">
                        <Link :href="route('dashboard')" class="text-indigo-600 hover:text-indigo-800 font-medium text-base sm:text-lg">
                            &larr; Back to Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>