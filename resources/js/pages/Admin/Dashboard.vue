<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: AuthenticatedLayout });

defineProps({
    activeUsersCount: Number,
    totalUsers: Number,
    newUsersLastTwoWeeks: Number, // New prop
    totalStudySessions: Number,   // New prop
    mostPopularTags: Array,       // New prop
    averageWordsPerUser: Number,  // New prop
    retention1Week: Number,       // New prop
    retention1Month: Number,      // New prop
    topFailedWords: Array,        // New prop
});
</script>

<template>

    <Head title="Admin Dashboard" />

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 xl:max-w-screen-xl 2xl:max-w-screen-2xl">

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 sm:p-8 md:p-10">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-gray-800">
                Admin Overview
            </h2>
            <p class="text-base sm:text-lg text-gray-600 mt-2 mb-10">
                Monitor your application's health and user activity.
            </p>

            <!-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="text-gray-900">
                    <div class="flex flex-wrap gap-4">
                        <Link :href="route('admin.users.index')"
                            class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
                        Manage Users
                        </Link> -->
                        <!-- TODO WORDS FOR ADMIN -->
                        <!-- <Link :href="route('admin.words.index')" class="inline-flex items-center px-6 py-3 bg-teal-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-teal-700 focus:bg-teal-700 active:bg-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
                            Manage Words
                        </Link> -->
                    <!-- </div>
                </div>
            </div> -->
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm sm:text-base font-medium text-gray-500">Total Registered Users</p>
                    <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-blue-600 mb-2">
                        {{ totalUsers ?? 0 }}
                    </p>
                    <Link :href="route('admin.users.index')"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 text-xs">
                    Manage Users
                    </Link>
                </div>
                <span class="text-4xl sm:text-5xl text-blue-400">👤</span>
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm sm:text-base font-medium text-gray-500">Active Users (Last 2 Weeks)</p>
                    <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-green-600">{{ activeUsersCount ?? 0 }}</p>
                </div>
                <span class="text-4xl sm:text-5xl text-green-400">🟢</span>
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm sm:text-base font-medium text-gray-500">New Users (Last 2 Weeks)</p>
                    <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-teal-600">{{ newUsersLastTwoWeeks ?? 0 }}
                    </p>
                </div>
                <span class="text-4xl sm:text-5xl text-teal-400">🆕</span>
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm sm:text-base font-medium text-gray-500">Total Study Sessions</p>
                    <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-purple-600">{{ totalStudySessions ?? 0 }}
                    </p>
                </div>
                <span class="text-4xl sm:text-5xl text-purple-400">📚</span>
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm sm:text-base font-medium text-gray-500">Avg. Words Per User</p>
                    <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-red-600">{{ averageWordsPerUser ?? 0 }}
                    </p>
                </div>
                <span class="text-4xl sm:text-5xl text-red-400">📊</span>
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm">
                <p class="text-sm sm:text-base font-medium text-gray-500 mb-2">User Retention (Cohort: Last Month)</p>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl sm:text-3xl font-bold text-indigo-600">{{ retention1Week }}%</p>
                        <p class="text-xs text-gray-500">After 1 Week</p>
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-bold text-indigo-600">{{ retention1Month }}%</p>
                        <p class="text-xs text-gray-500">After 1 Month</p>
                    </div>
                    <span class="text-4xl sm:text-5xl text-indigo-400">📈</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4">Most Popular Tags</h3>
                    <ul v-if="mostPopularTags && mostPopularTags.length" class="divide-y divide-gray-200">
                        <li v-for="tag in mostPopularTags" :key="tag.name"
                            class="py-2 flex justify-between items-center">
                            <span class="text-lg font-medium text-gray-900">{{ tag.name }}</span>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">{{
                                tag.word_count }} words</span>
                        </li>
                    </ul>
                    <p v-else class="text-gray-500">No tags found or words associated with tags.</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4">Top 15 Most Failed Words</h3>
                    <ul v-if="topFailedWords && topFailedWords.length" class="divide-y divide-gray-200">
                        <li v-for="(word, index) in topFailedWords" :key="word.chinese_word"
                            class="py-2 flex justify-between items-center">
                            <span class="text-lg font-medium text-gray-900">
                                {{ index + 1 }}. {{ word.chinese_word }} <span class="text-gray-500 text-base">({{
                                    word.pinyin }})</span>
                            </span>
                            <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-semibold rounded-full">{{
                                word.incorrect_attempts_count }} incorrect</span>
                        </li>
                    </ul>
                    <p v-else class="text-gray-500">No failed word data available yet.</p>
                </div>
            </div>
        </div>

        <!-- TODO ADMIN LOGS ACTIVITY -->
        <!-- <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 sm:p-8">
                <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4">Admin Activity Log (Example)</h3>
                <p class="text-gray-500 text-base sm:text-lg">This is where you could display recent admin actions or
                    other relevant data.</p>
            </div>
        </div> -->

    </div>
</template>