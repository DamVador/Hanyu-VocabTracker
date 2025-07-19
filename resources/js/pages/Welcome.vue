<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const user = usePage().props.auth.user;

defineOptions({ layout: GuestLayout });
</script>

<template>

    <Head title="Welcome to HanyuVocabTracker" />

    <div class="relative min-h-screen bg-white sm:flex sm:justify-center sm:items-center selection:bg-indigo-500 selection:text-white">

        <header class="absolute top-0 left-0 right-0 p-6 z-10 flex justify-between items-center">
            <div class="flex items-center">
                <img src="/logo_HanyuVocabTrackerWhite.png" alt="Hanyu VocabTracker Logo" class="h-16 w-auto mr-3">
            </div>

            <nav class="flex items-center space-x-4">
                <Link v-if="user" :href="route('dashboard')"
                    class="font-semibold text-gray-800 hover:text-gray-600 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">
                Dashboard
                </Link>

                <template v-else>
                    <Link :href="route('login')"
                        class="font-semibold text-gray-800 hover:text-gray-600 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500 text-white lg:text-black">
                    Log in
                    </Link>

                    <Link v-if="canRegister" :href="route('register')"
                        class="ms-4 px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500 transition ease-in-out duration-150">
                    Register
                    </Link>
                </template>
            </nav>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 w-full min-h-screen">
            <div
                class="flex flex-col justify-center items-center p-6 lg:p-12 bg-gradient-to-br from-indigo-600 to-purple-700 text-white text-center lg:text-left shadow-lg pt-30 lg:pt-2">
                <div class="max-w-xl mx-auto lg:mx-0">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight mb-4 leading-tight">
                        Master Your Chinese Vocabulary.
                    </h1>
                    <p class="text-lg md:text-xl opacity-90 mb-8">
                        Easily track and categorize words for smarter learning.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <Link v-if="!user && canRegister" :href="route('register')"
                            class="inline-flex items-center justify-center px-8 py-4 bg-white text-indigo-700 font-bold text-lg rounded-full shadow-lg hover:bg-gray-100 transition duration-300 transform hover:scale-105">
                        Get Started
                        </Link>
                        <Link v-if="user" :href="route('dashboard')"
                            class="inline-flex items-center justify-center px-8 py-4 bg-white text-indigo-700 font-bold text-lg rounded-full shadow-lg hover:bg-gray-100 transition duration-300 transform hover:scale-105">
                        Go to Dashboard
                        </Link>
                        <Link v-if="!user && canLogin" :href="route('login')"
                            class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white font-semibold text-lg rounded-full hover:bg-white hover:text-indigo-700 transition duration-300 transform hover:scale-105">
                        Login
                        </Link>
                    </div>
                </div>
            </div>

            <div class="flex flex-col justify-center p-6 lg:p-12 bg-gray-50 text-gray-800 shadow-inner">
                <div class="max-w-xl mx-auto lg:mx-0 mt-8">
                    <h2 class="text-3xl md:text-4xl font-bold mb-8 text-gray-900">Why Hanyu VocabTracker?</h2>
                    <div class="grid sm:grid-cols-2 gap-8 mb-16">
                        <div class="p-6 bg-white rounded-lg shadow-md border-t-4 border-indigo-500">
                            <div class="text-indigo-600 text-4xl mb-3">📝</div>
                            <h3 class="text-xl font-semibold mb-2">Word integration</h3>
                            <p class="text-gray-700">Quickly save new words with Chinese characters, Pinyin, and
                                translations.
                            </p>
                        </div>
                        <div class="p-6 bg-white rounded-lg shadow-md border-t-4 border-purple-500">
                            <div class="text-purple-600 text-4xl mb-3">🏷️</div>
                            <h3 class="text-xl font-semibold mb-2">Smart Organization</h3>
                            <p class="text-gray-700">Categorize words with custom tags for efficient access.</p>
                        </div>
                        <div class="p-6 bg-white rounded-lg shadow-md border-t-4 border-red-500">
                            <div class="text-red-600 text-4xl mb-3">🧠</div>
                            <h3 class="text-xl font-semibold mb-2">Tailored Study Paths</h3>
                            <p class="text-gray-700">
                                Create study sessions to test and reinforce your vocabulary knowledge.
                            </p>
                        </div>
                        <div class="p-6 bg-white rounded-lg shadow-md border-t-4 border-blue-500">
                            <div class="text-blue-600 text-4xl mb-3">📱</div>
                            <h3 class="text-xl font-semibold mb-2">Accessible Anywhere</h3>
                            <p class="text-gray-700">Access your vocabulary list from any device, anytime.</p>
                        </div>
                    </div>

                    <footer class="mt-8 text-sm text-gray-500 text-center lg:text-left">
                        &copy; {{ new Date().getFullYear() }} HanyuVocabTracker. All rights reserved.
                    </footer>
                </div>
            </div>
        </div>
    </div>
</template>