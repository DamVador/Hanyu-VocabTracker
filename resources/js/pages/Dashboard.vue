<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
  totalWords: Number,
  recentWords: Array,
  wordsDueForReview: Number,
  wordsAddedThisWeek: Number,
  wordsAddedThisMonth: Number,
  wordsReviewedToday: Number,
  wordsReviewedThisWeek: Number,
  averageSessionLength: String,
  currentStreak: Number,
});

const showWordsAddedThisMonth = ref(false);
const wordsAddedDisplay = computed(() => {
  return showWordsAddedThisMonth.value ? props.wordsAddedThisMonth : props.wordsAddedThisWeek;
});
const wordsAddedPeriod = computed(() => {
  return showWordsAddedThisMonth.value ? 'This Month' : 'This Week';
});

const showWordsReviewedThisWeek = ref(false);
const wordsReviewedDisplay = computed(() => {
  return showWordsReviewedThisWeek.value ? props.wordsReviewedThisWeek : props.wordsReviewedToday;
});
const wordsReviewedPeriod = computed(() => {
  return showWordsReviewedThisWeek.value ? 'This Week' : 'Today';
});

const importForm = useForm({
  csv_file: null as File | null,
});

const importStatus = ref('');
const importError = ref('');

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    importForm.csv_file = target.files[0];
  } else {
    importForm.csv_file = null;
  }
  importStatus.value = '';
  importError.value = '';
};

const submitCsvImport = () => {
  if (!importForm.csv_file) {
    importError.value = 'Please select a CSV file to import.';
    return;
  }

  importStatus.value = 'Importing...';
  importError.value = '';

  importForm.post(route('words.importCsv'), {
    onSuccess: () => {
      importStatus.value = 'CSV imported successfully! Words and sessions created/updated.';
      importError.value = '';
      importForm.reset('csv_file');
    },
    onError: (errors) => {
      importStatus.value = '';
      if (errors.csv_file) {
        importError.value = errors.csv_file;
      } else if (errors.general) {
        importError.value = errors.general;
      } else {
        importError.value = 'An unknown error occurred during import.';
        console.error('Import errors:', errors);
      }
    },
  });
};
</script>

<template>

  <Head title="Dashboard" />

  <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 xl:max-w-screen-xl 2xl:max-w-screen-2xl">

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 sm:p-8 md:p-10">
      <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-gray-800">
        Welcome back, {{ $page.props.auth.user.name }}!
      </h2>
      <p class="text-base sm:text-lg text-gray-600 mt-2">
        Ready to expand your vocabulary?
      </p>
    </div>

    <div v-if="totalWords === 0" class="bg-indigo-50 border-l-4 border-indigo-500 text-indigo-700 p-4 mb-6"
      role="alert">
      <div class="flex">
        <div class="py-1"><svg class="fill-current h-6 w-6 text-indigo-500 mr-4" xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20">
            <path
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-11a1 1 0 112 0v4a1 1 0 11-2 0V7zm0 8a1 1 0 112 0 1 1 0 01-2 0z" />
          </svg></div>
        <div>
          <p class="font-bold mb-4">Welcome to Hanyu VocabTracker!</p>
          <p class="text-sm">It looks like you're new here. To start learning, you need to add some words to your
            dictionary and create your first study session. <br>
            You can also download some words from the available lists and import it using the import CSV option on the dashaboard.
          </p>
          <div class="mt-4 flex gap-4">
            <Link :href="route('words.create')"
              class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 text-xs">
            Add Your First Word
            </Link>
            <Link :href="route('study-sessions.index')"
              class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150 text-xs">
            Create Study Sessions
            </Link>
            <Link :href="route('resources.lists')"
              class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 transition ease-in-out duration-150 text-xs">
            Download Word Lists
            </Link>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

      <div class="flex flex-col gap-6 lg:order-1">
        <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm h-full">
          <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">Study Progress</h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-md flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Words Due for Review</p>
                <p class="text-2xl font-bold text-red-500">{{ wordsDueForReview ?? 0 }}</p>
              </div>
              <span class="text-3xl text-red-400">⏰</span>
            </div>

            <div class="bg-gray-50 p-4 rounded-md">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-gray-500">Words Reviewed {{ wordsReviewedPeriod }}</p>
                <button @click="showWordsReviewedThisWeek = !showWordsReviewedThisWeek"
                  class="text-xs text-blue-500 hover:underline">
                  Switch
                </button>
              </div>
              <div class="flex items-center justify-between">
                <p class="text-2xl font-bold text-yellow-600">{{ wordsReviewedDisplay }}</p>
                <span class="text-3xl text-yellow-400">🔄</span>
              </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-md flex items-center justify-between col-span-1 sm:col-span-2">
              <div>
                <p class="text-sm font-medium text-gray-500">Current Study Streak</p>
                <p class="text-2xl font-bold text-orange-600">{{ currentStreak }} <span
                    class="text-base text-gray-500">days</span></p>
              </div>
              <span class="text-3xl text-orange-400">🔥</span>
            </div>
          </div>

          <div class="flex flex-wrap gap-4">
            <Link :href="route('study.index')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
            Start Automatic Study Session
            </Link>
            <Link :href="route('study-sessions.index')"
              class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
            Manage Study Sessions
            </Link>
          </div>
        </div>
      </div>

      <div class="flex flex-col gap-6 lg:order-2">
        <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm h-full">
          <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">Your Vocabulary</h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-md flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Total Words</p>
                <p class="text-2xl font-bold text-indigo-600">{{ totalWords }}</p>
              </div>
              <span class="text-3xl text-indigo-400">📚</span>
            </div>

            <div class="bg-gray-50 p-4 rounded-md">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-gray-500">Words Added {{ wordsAddedPeriod }}</p>
                <button @click="showWordsAddedThisMonth = !showWordsAddedThisMonth"
                  class="text-xs text-blue-500 hover:underline">
                  Switch
                </button>
              </div>
              <div class="flex items-center justify-between">
                <p class="text-2xl font-bold text-green-600">{{ wordsAddedDisplay }}</p>
                <span class="text-3xl text-green-400">🆕</span>
              </div>
            </div>
          </div>

          <div class="flex flex-wrap gap-4 mb-6">
            <Link :href="route('words.index')"
              class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
            My Words Dictionary
            </Link>
            <Link :href="route('words.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
            Add New Word
            </Link>
          </div>

          <div class="border-t pt-6 mt-6 border-gray-200">
            <h4 class="text-lg font-medium text-gray-800 mb-3">Import Words from CSV</h4>
            <p class="text-sm text-gray-600 mb-4">
              Upload a CSV file with columns: `chinese_character`, `pinyin`, `translation`, `study_session_name`,
              `tags`.<br>
              'study_session_name' is optional. <br>
              'tags' is optional and can have several values separated by a comma.
            </p>

            <form @submit.prevent="submitCsvImport" class="flex flex-col sm:flex-row items-center gap-4">
              <input type="file" @change="handleFileChange" accept=".csv" class="block w-full text-sm text-gray-500
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-md file:border-0
                          file:text-sm file:font-semibold
                          file:bg-indigo-50 file:text-indigo-700
                          hover:file:bg-indigo-100" />
              <button type="submit" :disabled="importForm.processing || !importForm.csv_file" class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm
                          sm:w-auto w-full"
                :class="{ 'opacity-50 cursor-not-allowed': importForm.processing || !importForm.csv_file }">
                {{ importForm.processing ? 'Importing...' : 'Upload CSV' }}
              </button>
            </form>

            <div v-if="importStatus" class="mt-4 text-green-600 text-sm">{{ importStatus }}</div>
            <div v-if="importError" class="mt-4 text-red-600 text-sm">{{ importError }}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 sm:p-8 md:p-10">
      <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">Vocabulary Lists</h3>
      <p class="text-sm text-gray-600 mb-4">
        Download various vocabulary lists to easily import them into your dictionary (e.g., HSK, TOCFL, etc.).
      </p>
      <Link :href="route('resources.lists')"
        class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
      Browse All Lists &rarr;
      </Link>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 sm:p-8">
        <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4">Recently Added Words</h3>
        <ul v-if="recentWords && recentWords.length">
          <li v-for="word in recentWords" :key="word.id"
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center py-3 border-b last:border-b-0 border-gray-200">
            <div class="flex-1 mb-2 sm:mb-0">
              <p class="text-lg sm:text-xl font-medium text-gray-900">{{ word.chinese_word }} ({{ word.pinyin }})</p>
              <p class="text-base sm:text-lg text-gray-600">{{ word.translation }}</p>
            </div>
            <div class="text-sm text-gray-500 flex flex-wrap justify-start sm:justify-end gap-1">
              <span v-for="tag in word.tags" :key="tag"
                class="px-2.5 py-0.5 bg-gray-200 rounded-full text-xs sm:text-sm font-medium text-gray-700">
                {{ tag }}
              </span>
            </div>
          </li>
        </ul>
        <p v-else class="text-gray-500 text-base sm:text-lg">No words added yet.
          <Link :href="route('words.create')" class="text-indigo-600 hover:underline">Add your first word!</Link>
        </p>

        <div class="mt-6 text-center">
          <Link :href="route('words.index')"
            class="text-indigo-600 hover:text-indigo-800 font-medium text-base sm:text-lg">
          View All Your Words &rarr;
          </Link>
        </div>
      </div>
    </div>

  </div>
</template>