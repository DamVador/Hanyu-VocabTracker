<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    wordsForSession: Array,
    allTags: Array,
});

const currentWordIndex = ref(0);
const showTranslation = ref(false);
const sessionComplete = ref(false);

const currentWord = computed(() => {
    return props.wordsForSession[currentWordIndex.value];
});

const progress = computed(() => {
    if (props.wordsForSession.length === 0) return 0;
    return (currentWordIndex.value / props.wordsForSession.length) * 100;
});

// Method to record study progress
const recordWordStudy = async (wordId, correct) => { // <-- Made function async
    if (sessionComplete.value) return;

    try {
        const response = await axios.post(route('words.recordStudy', wordId), { correct: correct }); // <-- Use axios.post
        console.log(`Word ${wordId} marked as ${correct ? 'correct' : 'incorrect'}.`);
        console.log('History updated:', response.data.history);

        // Optional: Update the current word's history data in the local state
        // This makes the 'Current Status' section update immediately
        if (currentWord.value) {
            currentWord.value.history = response.data.history;
        }

        goToNextWord();
    } catch (error) {
        console.error('Error recording study:', error);
        alert('Failed to record study progress.');
        goToNextWord(); // Still move to next word to not block session
    }
};

const goToNextWord = () => {
    showTranslation.value = false;
    if (currentWordIndex.value < props.wordsForSession.length - 1) {
        currentWordIndex.value++;
    } else {
        sessionComplete.value = true;
    }
};

const resetSession = () => {
    currentWordIndex.value = 0;
    showTranslation.value = false;
    sessionComplete.value = false;
    router.reload();
};
</script>

<template>
    <Head title="Study Session" />

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 text-center">
                    <h2 class="text-3xl font-semibold mb-6">Study Session</h2>

                    <div v-if="props.wordsForSession.length === 0 && !sessionComplete">
                        <p class="text-lg text-gray-600 mb-4">No words due for review right now!</p>
                        <p class="text-sm text-gray-500 mb-6">Check back later or add new words.</p>
                        <Link :href="route('words.create')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Add New Word
                        </Link>
                    </div>

                    <div v-else-if="sessionComplete">
                        <h3 class="text-xl font-bold text-green-700 mb-4">Session Complete! 🎉</h3>
                        <p class="text-gray-700 mb-4">You've reviewed all {{ props.wordsForSession.length }} words for this session.</p>
                        <button @click="resetSession" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Restart Session
                        </button>
                        <Link :href="route('study-sessions.index')" class="inline-flex items-center px-4 py-2 ml-4 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Back to Sessions
                        </Link>
                    </div>

                    <div v-else>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-6">
                            <div class="bg-indigo-600 h-2.5 rounded-full" :style="{ width: progress + '%' }"></div>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg shadow-inner mb-6">
                            <p class="text-sm text-gray-500 mb-2">Word {{ currentWordIndex + 1 }} / {{ props.wordsForSession.length }}</p>
                            <div class="text-5xl font-bold text-gray-800 mb-4">{{ currentWord.chinese_word }}</div>
                            <div class="text-xl text-gray-600 mb-6">{{ currentWord.pinyin }}</div>

                            <button @click="showTranslation = !showTranslation"
                                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150 mb-4">
                                {{ showTranslation ? 'Hide Translation' : 'Show Translation' }}
                            </button>

                            <div v-if="showTranslation" class="mt-4 text-2xl font-semibold text-indigo-700">{{ currentWord.translation }}</div>

                            <div class="mt-6">
                                <p class="text-sm text-gray-500 mb-2">Current Status:</p>
                                <p class="text-lg font-medium text-gray-700">
                                    Status: <span :class="{
                                        'text-green-600': currentWord.history?.learning_status === 'Mastered',
                                        'text-red-600': currentWord.history?.learning_status === 'Forgot',
                                        'text-blue-600': currentWord.history?.learning_status === 'Revise' || !currentWord.history,
                                    }">{{ currentWord.history?.learning_status || 'New' }}</span>
                                    <span v-if="currentWord.history?.next_revision"> | Next Review: {{ currentWord.history.next_revision }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-center gap-4">
                            <button @click="recordWordStudy(currentWord.id, true)"
                                    class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-base text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Correct
                            </button>
                            <button @click="recordWordStudy(currentWord.id, false)"
                                    class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-md font-semibold text-base text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Incorrect
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>