<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onUnmounted } from 'vue';
import TextInput from '@/components/Input.vue';
import Select from '@/components/Select.vue';
import Pagination from '@/components/Pagination.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    studySession: Object,
    sessionWords: Object,
    filters: Object,
    allStatuses: Array,
});

const pinyinFilter = ref(props.filters.pinyin || '');
const translationFilter = ref(props.filters.translation || '');
const sortBy = ref(props.filters.sort_by || 'failure_count');
const sortDirection = ref(props.filters.sort_direction || 'desc');
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    const currentSessionId = props.studySession?.id;
    const currentPinyin = pinyinFilter.value;
    const currentTranslation = translationFilter.value;
    const currentSortBy = sortBy.value;
    const currentSortDirection = sortDirection.value;

    if (!currentSessionId) {
        console.error("Session ID is missing for filter application. Cannot apply filters.");
        return;
    }

    debounceTimer = setTimeout(() => {
        const targetUrl = window.route('session-studies.words.index', { study_session: currentSessionId });

        if (!targetUrl) {
            console.error("Route URL could not be generated and is null/undefined.");
            return;
        }

        router.get(
            targetUrl,
            {
                pinyin: currentPinyin,
                translation: currentTranslation,
                sort_by: currentSortBy,
                sort_direction: currentSortDirection,
                page: 1,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
        debounceTimer = null;
    }, 300);
};

onUnmounted(() => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
});

watch([pinyinFilter, translationFilter, sortBy, sortDirection], applyFilters);

const clearFilters = () => {
    pinyinFilter.value = '';
    translationFilter.value = '';
    // statusFilter.value = ''; // TODO - filter by status
    sortBy.value = 'failure_count';
    sortDirection.value = 'desc';
};

const handleDeleteWord = (wordId: number) => {
    if (confirm('Are you sure you want to delete this word from the session? It will not be removed from your dictionary.')) {
        const currentSessionId = props.studySession?.id;

        if (!currentSessionId) {
            console.error("Session ID is missing. Cannot delete word from session.");
            return;
        }

        router.delete(
            route('session-studies.words.detach', { study_session: currentSessionId, word: wordId }),
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    console.log(`Word ${wordId} successfully detached from session ${currentSessionId}`);
                },
                onError: (errors) => {
                    console.error("Error detaching word:", errors);
                    alert("Failed to remove word from session. Please try again.");
                }
            }
        );
    }
};

</script>

<template>
    <Head :title="`Words from ${props.studySession?.name || 'Loading...'}`" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6">
                        <h2 class="font-semibold text-2xl text-gray-800 leading-tight mb-4 sm:mb-0">
                            Words from "{{ props.studySession?.name || 'Loading...' }}"
                        </h2>
                        <div class="flex flex-col sm:flex-row sm:gap-4 gap-2">
                            <Link :href="route('words.create')">
                                <PrimaryButton class="w-full sm:w-auto">Add New Word</PrimaryButton>
                            </Link>
                            <Link :href="route('study-sessions.index')">
                                <PrimaryButton class="w-full sm:w-auto">Back to Sessions</PrimaryButton>
                            </Link>
                        </div>
                    </div>

                    <div class="mb-6 flex flex-wrap gap-4 items-end">
                        <div class="flex-grow">
                            <label for="filter-pinyin" class="block text-sm font-medium text-gray-700">Filter by
                                Pinyin</label>
                            <TextInput id="filter-pinyin" type="text" v-model="pinyinFilter" placeholder="e.g., nǐ hǎo"
                                class="w-full" />
                        </div>

                        <div class="flex-grow">
                            <label for="filter-translation" class="block text-sm font-medium text-gray-700">Filter by
                                Translation</label>
                            <TextInput id="filter-translation" type="text" v-model="translationFilter"
                                placeholder="e.g., hello" class="w-full" />
                        </div>

                        <div>
                            <label for="sort-by" class="block text-sm font-medium text-gray-700">Sort By</label>
                            <Select id="sort-by" v-model="sortBy" :options="[
                                { value: 'pinyin', label: 'Pinyin' },
                                { value: 'translation', label: 'Translation' },
                                { value: 'failure_count', label: 'Failures' },
                                { value: 'created_at', label: 'Date Added' },
                                { value: 'learning_status', label: 'Status' }
                            ]" placeholder="Sort By" class="w-full" />
                        </div>

                        <div>
                            <label for="sort-direction"
                                class="block text-sm font-medium text-gray-700">Direction</label>
                            <Select id="sort-direction" v-model="sortDirection" :options="[
                                { value: 'asc', label: 'Ascending' },
                                { value: 'desc', label: 'Descending' }
                            ]" placeholder="Direction" class="w-full" />
                        </div>

                        <button
                            v-if="pinyinFilter || translationFilter || sortBy !== 'failure_count' || sortDirection !== 'desc'"
                            @click="clearFilters"
                            class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-md bg-gray-100 hover:bg-gray-200">
                            Clear Filters
                        </button>
                    </div>

                    <div v-if="sessionWords.data.length === 0" class="text-gray-600 italic mt-4">
                        No words found matching your criteria.
                        <span v-if="pinyinFilter || translationFilter">Try adjusting your
                            filters.</span>
                    </div>

                    <div v-else>
                        <div class="overflow-x-auto mt-4 shadow-md sm:rounded-lg hidden sm:block">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Character</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Pinyin</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Translation</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Failures</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="sWord in sessionWords.data" :key="sWord.id"
                                        class="bg-white hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                            sWord.chinese_word }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sWord.pinyin }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sWord.translation
                                            }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sWord.failure_count
                                            }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{
                                            sWord.learning_status }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('words.edit', { word: sWord.id })"
                                                class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</Link>
                                            <button @click="handleDeleteWord(sWord.id)"
                                                class="text-red-600 hover:text-red-900">Remove</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <ul class="block sm:hidden mt-4 bg-white shadow-sm rounded-lg divide-y divide-gray-200">
                            <li v-for="sWord in sessionWords.data" :key="sWord.id" class="p-4 flex flex-col">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <p class="text-lg font-medium text-gray-900">{{ sWord.chinese_word }} ({{
                                            sWord.pinyin }})</p>
                                        <p class="text-base text-gray-600">{{ sWord.translation }}</p>
                                    </div>
                                    <div class="flex flex-col items-end gap-1 ml-4">
                                        <span
                                            class="px-2.5 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                            Status: {{ sWord.learning_status }}
                                        </span>
                                        <span
                                            class="px-2.5 py-0.5 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                            Failures: {{ sWord.failure_count }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-1 mb-2">
                                    <span v-for="tag in sWord.tags" :key="tag"
                                        class="px-2 py-0.5 bg-gray-200 rounded-full text-xs font-medium text-gray-700">
                                        {{ tag }}
                                    </span>
                                    <span v-if="(sWord.tags ?? []).length === 0" class="text-gray-400 text-xs italic">No tags</span>
                                </div>
                                <div class="flex justify-end gap-3 mt-2">
                                    <Link :href="route('words.edit', { word: sWord.id })"
                                        class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</Link>
                                    <button @click="handleDeleteWord(sWord.id)"
                                        class="text-red-600 hover:text-red-900 text-sm font-medium">Remove</button>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <Pagination :pagination="sessionWords" :current-filters="props.filters" />

                </div>
            </div>
        </div>
    </div>
</template>