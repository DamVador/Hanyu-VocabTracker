<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-200 text-gray-800 flex flex-col">
        <nav class="bg-gray-100 border-b border-gray-300 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('blog.index')" class="text-2xl font-bold text-indigo-600">
                                Blog
                            </Link>
                        </div>
                    </div>
                    
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <!-- Lien vers l'application principale -->
                        <Link :href="route('dashboard')"
                              class="text-gray-600 hover:text-gray-900 font-semibold px-3 py-2 rounded-md transition-colors duration-200">
                            HanyuVocabTracker
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <main class="flex-grow">
            <slot />
        </main>

        <footer class="bg-gray-100 border-t border-gray-300 py-8 text-center text-sm text-gray-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                &copy; {{ new Date().getFullYear() }} Hanyu-VocabTracker. All rights reserved.
            </div>
        </footer>
    </div>
</template>
