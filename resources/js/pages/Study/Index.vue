<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import { PenTool } from 'lucide-vue-next';
import CharacterDrawingCanvas from '@/components/CharacterDrawingCanvas.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    wordsForSession: Array,
    allTags: Array,
});

const currentWordIndex = ref(0);
const showAnswer = ref(false);
const sessionComplete = ref(false);
const initialDisplayMode = ref('pinyin');

const hasAnswerBeenShown = ref(false);
const showDrawingModal = ref(false);

const wordDrawings = ref<Record<number, string>>({});

const currentWord = computed(() => {
    return props.wordsForSession[currentWordIndex.value];
});

const currentWordDrawing = computed(() => {
    return wordDrawings.value[currentWord.value?.id] || null;
});

const progress = computed(() => {
    if (props.wordsForSession.length === 0) return 0;
    return (currentWordIndex.value / props.wordsForSession.length) * 100;
});

const recordWordStudy = async (wordId, correct) => {
    if (sessionComplete.value) return;
    if (!hasAnswerBeenShown.value) {
        alert('Please reveal the answer before marking as Correct or Incorrect.');
        return;
    }

    try {
        const response = await axios.post(route('words.recordStudy', wordId), { correct: correct });
        if (currentWord.value) {
            currentWord.value.history = response.data.history;
        }

        goToNextWord();
    } catch (error) {
        console.error('Error recording study:', error);
        alert('Failed to record study progress.');
        goToNextWord();
    }
};

const goToNextWord = () => {
    showAnswer.value = false;
    hasAnswerBeenShown.value = false;
    showDrawingModal.value = false;
    if (currentWordIndex.value < props.wordsForSession.length - 1) {
        currentWordIndex.value++;
    } else {
        sessionComplete.value = true;
    }
};

const resetSession = () => {
    currentWordIndex.value = 0;
    showAnswer.value = false;
    hasAnswerBeenShown.value = false;
    sessionComplete.value = false;
    showDrawingModal.value = false;
    wordDrawings.value = {};
    router.reload();
};

const toggleShowAnswer = () => {
    showAnswer.value = !showAnswer.value;
    if (showAnswer.value) {
        hasAnswerBeenShown.value = true;
    }
};

const openDrawingModal = () => {
    showDrawingModal.value = true;
};

const closeDrawingModal = () => {
    showDrawingModal.value = false;
};

const saveDrawingForWord = (dataUrl: string) => {
    if (currentWord.value) {
        wordDrawings.value[currentWord.value.id] = dataUrl;
    }
};
</script>

<template>
    <div>

        <Head title="Study Session" />

        <div class="py-12">
            <div class="max-w-md mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <h2 class="text-3xl font-semibold mb-6">Study Session</h2>

                        <div v-if="props.wordsForSession.length === 0 && !sessionComplete">
                            <p class="text-lg text-gray-600 mb-4">No words due for review right now!</p>
                            <p class="text-sm text-gray-500 mb-6">Check back later or add new words.</p>
                            <Link :href="route('words.create')"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Add New Word
                            </Link>
                        </div>

                        <div v-else-if="sessionComplete">
                            <h3 class="text-xl font-bold text-green-700 mb-4">Session Complete! 🎉</h3>
                            <p class="text-gray-700 mb-4">You've reviewed all {{ props.wordsForSession.length }} words
                                for this session.</p>
                            <button @click="resetSession"
                                class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Restart Session
                            </button>
                            <Link :href="route('study-sessions.index')"
                                class="inline-flex items-center px-3 py-2 ml-4 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Back to Sessions
                            </Link>
                        </div>

                        <div v-else>
                            <div class="mb-6 bg-gray-100 p-4 rounded-lg shadow-inner text-left">
                                <p class="font-medium text-gray-700 mb-2">Show first:</p>
                                <div class="flex flex-wrap gap-4 justify-center">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" v-model="initialDisplayMode" value="chinese"
                                            class="form-radio text-indigo-600 h-4 w-4">
                                        <span class="ml-2 text-gray-700 text-sm font-medium">Chinese</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" v-model="initialDisplayMode" value="pinyin"
                                            class="form-radio text-indigo-600 h-4 w-4">
                                        <span class="ml-2 text-gray-700 text-sm font-medium">Pinyin</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" v-model="initialDisplayMode" value="translation"
                                            class="form-radio text-indigo-600 h-4 w-4">
                                        <span class="ml-2 text-gray-700 text-sm font-medium">Translation</span>
                                    </label>
                                </div>
                            </div>

                            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-6">
                                <div class="bg-indigo-600 h-2.5 rounded-full" :style="{ width: progress + '%' }"></div>
                            </div>

                            <div class="bg-gray-50 p-6 rounded-lg shadow-inner mb-6 relative">
                                <p class="text-sm text-gray-500 mb-2">Word {{ currentWordIndex + 1 }} / {{
                                    props.wordsForSession.length }}</p>

                                <div class="absolute top-4 right-4 flex items-center gap-2">
                                    <img v-if="currentWordDrawing" :src="currentWordDrawing"
                                        alt="Character Drawing Preview"
                                        class="w-10 h-10 border border-gray-300 rounded-md object-contain cursor-pointer"
                                        @click="openDrawingModal" title="View/Edit Drawing" />

                                    <button @click="openDrawingModal"
                                        class="p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        aria-label="Draw character" title="Draw Character">
                                        <PenTool class="h-5 w-5" />
                                    </button>
                                </div>

                                <div class="min-h-[120px] flex flex-col justify-center items-center">
                                    <div v-if="!showAnswer">
                                        <div v-if="initialDisplayMode === 'chinese'"
                                            class="text-5xl font-bold text-gray-800 mb-4">
                                            {{ currentWord.chinese_word }}
                                        </div>
                                        <div v-else-if="initialDisplayMode === 'pinyin'"
                                            class="text-5xl font-bold text-gray-800 mb-4">
                                            {{ currentWord.pinyin }}
                                        </div>
                                        <div v-else-if="initialDisplayMode === 'translation'"
                                            class="text-5xl font-bold text-gray-800 mb-4">
                                            {{ currentWord.translation }}
                                        </div>
                                        <div v-else class="text-xl text-gray-600 mb-4">
                                            Please select an initial display mode.
                                        </div>
                                    </div>

                                    <div v-else class="w-full">
                                        <div class="text-5xl font-bold text-gray-800 mb-2">{{ currentWord.chinese_word
                                            }}</div>
                                        <div class="text-xl text-gray-600 mb-2">{{ currentWord.pinyin }}</div>
                                        <div class="mt-4 text-2xl font-semibold text-indigo-700">{{
                                            currentWord.translation }}</div>
                                    </div>
                                </div>

                                <button @click="toggleShowAnswer()"
                                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150 mb-4">
                                    {{ showAnswer ? 'Hide Answer' : 'Show Answer' }}
                                </button>

                                <div class="mt-6">
                                    <p class="text-sm text-gray-500 mb-2">Current Status:</p>
                                    <p class="text-lg font-medium text-gray-700">
                                        Status: <span :class="{
                                            'text-green-600': currentWord.history?.learning_status === 'Mastered',
                                            'text-red-600': currentWord.history?.learning_status === 'Forgot',
                                            'text-blue-600': currentWord.history?.learning_status === 'Revise' || !currentWord.history,
                                        }">{{ currentWord.history?.learning_status || 'New' }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-center gap-4">
                                <button @click="recordWordStudy(currentWord.id, true)" :disabled="!hasAnswerBeenShown"
                                    :class="{ 'opacity-50 cursor-not-allowed': !hasAnswerBeenShown }"
                                    class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-base text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Correct
                                </button>
                                <button @click="recordWordStudy(currentWord.id, false)" :disabled="!hasAnswerBeenShown"
                                    :class="{ 'opacity-50 cursor-not-allowed': !hasAnswerBeenShown }"
                                    class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-md font-base text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Incorrect
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <CharacterDrawingCanvas v-show="showDrawingModal" :initial-drawing-data-url="currentWordDrawing"
            @close="closeDrawingModal" @save-drawing="saveDrawingForWord" />
    </div>
</template>