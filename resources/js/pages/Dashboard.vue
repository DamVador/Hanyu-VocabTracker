<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue'; // Import ref and computed

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    totalWords: Number,
    recentWords: Array,
    wordsDueForReview: Number,
    wordsAddedThisWeek: Number,    // New prop
    wordsAddedThisMonth: Number,   // New prop
    wordsReviewedToday: Number,    // New prop
    wordsReviewedThisWeek: Number, // New prop
    averageSessionLength: String,  // New prop
    currentStreak: Number,         // New prop
});

// Toggles for "Words Added"
const showWordsAddedThisMonth = ref(false);
const wordsAddedDisplay = computed(() => {
    return showWordsAddedThisMonth.value ? props.wordsAddedThisMonth : props.wordsAddedThisWeek;
});
const wordsAddedPeriod = computed(() => {
    return showWordsAddedThisMonth.value ? 'This Month' : 'This Week';
});

// Toggles for "Words Reviewed"
const showWordsReviewedThisWeek = ref(false);
const wordsReviewedDisplay = computed(() => {
    return showWordsReviewedThisWeek.value ? props.wordsReviewedThisWeek : props.wordsReviewedToday;
});
const wordsReviewedPeriod = computed(() => {
    return showWordsReviewedThisWeek.value ? 'This Week' : 'Today';
});
</script>

<template>
  <Head title="Dashboard" />

  <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 xl:max-w-screen-xl 2xl:max-w-screen-2xl">

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 sm:p-8 md:p-10">
      <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-gray-800">
        Welcome back, {{ $page.props.auth.user.name }}!
      </h2>
      <p class="text-base sm:text-lg text-gray-600 mt-2 mb-10">
        Ready to expand your vocabulary?
      </p>

      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="text-gray-900">
          <div class="flex flex-wrap gap-6">
              <Link :href="route('study.index')" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
                  Start Automatic Study Session
              </Link>

              <Link :href="route('study-sessions.index')" class="inline-flex items-center px-6 py-3 bg-purple-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
                  Manage Study Sessions
              </Link>

              <Link :href="route('words.index')" class="inline-flex items-center px-6 py-3 bg-gray-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
                  My Words Dictionary
              </Link>
              <Link :href="route('words.create')"
                  class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
                Add New Word
              </Link>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
      <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
        <div>
          <p class="text-sm sm:text-base font-medium text-gray-500">Total Words</p>
          <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-indigo-600">{{ totalWords }}</p>
        </div>
        <span class="text-4xl sm:text-5xl text-indigo-400">📚</span>
      </div>

      <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
        <div>
          <p class="text-sm sm:text-base font-medium text-gray-500">Words Due for Review</p>
          <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-red-500">{{ wordsDueForReview ?? 0 }}</p>
        </div>
        <span class="text-4xl sm:text-5xl text-red-400">⏰</span>
      </div>

      <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm sm:text-base font-medium text-gray-500">Words Added {{ wordsAddedPeriod }}</p>
            <button @click="showWordsAddedThisMonth = !showWordsAddedThisMonth"
                    class="text-xs text-blue-500 hover:underline">
                Switch
            </button>
        </div>
        <div class="flex items-center justify-between">
            <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-green-600">{{ wordsAddedDisplay }}</p>
            <span class="text-4xl sm:text-5xl text-green-400">🆕</span>
        </div>
      </div>

      <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm sm:text-base font-medium text-gray-500">Words Reviewed {{ wordsReviewedPeriod }}</p>
            <button @click="showWordsReviewedThisWeek = !showWordsReviewedThisWeek"
                    class="text-xs text-blue-500 hover:underline">
                Switch
            </button>
        </div>
        <div class="flex items-center justify-between">
            <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-yellow-600">{{ wordsReviewedDisplay }}</p>
            <span class="text-4xl sm:text-5xl text-yellow-400">🔄</span>
        </div>
      </div>

      <!-- <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
        <div>
          <p class="text-sm sm:text-base font-medium text-gray-500">Avg. Session Length</p>
          <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-purple-600">{{ averageSessionLength }}</p>
        </div>
        <span class="text-4xl sm:text-5xl text-purple-400">⏱️</span>
      </div> -->

      <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
        <div>
          <p class="text-sm sm:text-base font-medium text-gray-500">Current Study Streak</p>
          <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-orange-600">{{ currentStreak }} <span class="text-base text-gray-500">days</span></p>
        </div>
        <span class="text-4xl sm:text-5xl text-orange-400">🔥</span>
      </div>

      <!-- <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
          <div>
            <p class="text-sm sm:text-base font-medium text-gray-500">Quick Add</p>
            <Link :href="route('words.create')"
                class="mt-2 inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 transition">
              Add New Word
            </Link>
          </div>
          <span class="text-4xl sm:text-5xl text-blue-400">➕</span>
      </div> -->
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 sm:p-8">
        <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4">Recently Added Words</h3>
        <ul v-if="recentWords && recentWords.length">
          <li v-for="word in recentWords" :key="word.id" class="flex flex-col sm:flex-row justify-between items-start sm:items-center py-3 border-b last:border-b-0 border-gray-200">
            <div class="flex-1 mb-2 sm:mb-0">
              <p class="text-lg sm:text-xl font-medium text-gray-900">{{ word.chinese_word }} ({{ word.pinyin }})</p>
              <p class="text-base sm:text-lg text-gray-600">{{ word.translation }}</p>
            </div>
            <div class="text-sm text-gray-500 flex flex-wrap justify-start sm:justify-end gap-1">
              <span v-for="tag in word.tags" :key="tag" class="px-2.5 py-0.5 bg-gray-200 rounded-full text-xs sm:text-sm font-medium text-gray-700">
                {{ tag }}
              </span>
            </div>
          </li>
        </ul>
        <p v-else class="text-gray-500 text-base sm:text-lg">No words added yet. <Link :href="route('words.create')" class="text-indigo-600 hover:underline">Add your first word!</Link></p>

        <div class="mt-6 text-center">
          <Link :href="route('words.index')" class="text-indigo-600 hover:text-indigo-800 font-medium text-base sm:text-lg">
            View All Your Words &rarr;
          </Link>
        </div>
      </div>
    </div>

  </div>
</template>