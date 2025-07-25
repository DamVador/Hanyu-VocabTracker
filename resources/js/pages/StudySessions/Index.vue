<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import Pagination from '@/components/Pagination.vue';
import Dropdown from '@/components/Dropdown.vue';
import DropdownLink from '@/components/DropdownLink.vue';
import { MoreVertical } from 'lucide-vue-next';

defineOptions({ layout: AuthenticatedLayout, inheritAttrs: false });

const props = defineProps({
    studySessions: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

const applySearch = () => {
    router.get(route('study-sessions.index'), { search: search.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(search, (value) => {
    if (applySearch.timeout) {
        clearTimeout(applySearch.timeout);
    }
    applySearch.timeout = setTimeout(applySearch, 300);
});

const clearSearch = () => {
    search.value = '';
};

const deleteSession = (sessionId: number) => {
    if (confirm('Are you sure you want to delete this study session? All associated words will remain in your dictionary.')) {
        router.delete(route('study-sessions.destroy', { study_session: sessionId }), {
            onSuccess: () => {
                // Success message will be shown via flash.success
            },
            onError: (errors) => {
                alert('Failed to delete session: ' + Object.values(errors).join('\n'));
            }
        });
    }
};

const startSessionReview = (sessionId: number, mode: 'all' | 'failed') => {
    router.get(route('study.sessionReview', { study_session: sessionId, mode: mode }));
};

const exportSingleSessionToCsv = (sessionId: number) => {
    window.location.href = route('study-sessions.exportSingleCsv', { study_session: sessionId });
};

</script>

<template>

    <Head title="My Study Sessions" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
                        <h2 class="font-semibold text-2xl text-gray-800 leading-tight mb-4 sm:mb-0">
                            My Study Sessions
                        </h2>
                        <div class="flex gap-4">
                            <Link :href="route('study-sessions.create')">
                            <PrimaryButton class="w-full sm:w-auto">Create New Study Session</PrimaryButton>
                            </Link>
                        </div>
                    </div>

                    <div class="mb-6 flex items-center space-x-2">
                        <TextInput type="text" v-model="search" placeholder="Search by session name..."
                            class="flex-grow" />
                        <button v-if="search" @click="clearSearch"
                            class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-md bg-gray-100 hover:bg-gray-200">
                            Clear
                        </button>
                    </div>

                    <div v-if="studySessions && studySessions.data.length === 0" class="text-gray-600 italic mt-4">
                        No study sessions created yet. Click "Create New Study Session" to get started!
                        <span v-if="search"> or no sessions match your search.</span>
                    </div>

                    <div v-else-if="studySessions && studySessions.data.length > 0">
                        <div class="overflow-x-auto mt-4 shadow-md sm:rounded-lg hidden sm:block">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Session Name</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Words</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="session in studySessions.data" :key="session.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                            session.name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ session.description || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <Link
                                                :href="route('session-studies.words.index', { study_session: session.id })"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            {{ session.words_count }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('study-sessions.edit', { study_session: session.id })"
                                                class="text-indigo-600 hover:text-indigo-900 mr-2">Manage Words</Link>
                                            <button @click="startSessionReview(session.id, 'all')"
                                                class="cursor-pointer text-blue-600 hover:text-blue-900 mr-2"
                                                :disabled="session.words_count === 0">Study All</button>
                                            <button @click="startSessionReview(session.id, 'failed')"
                                                class="cursor-pointer text-red-600 hover:text-red-900 mr-2"
                                                :disabled="session.words_count === 0">Study Failed</button>
                                            <button @click="exportSingleSessionToCsv(session.id)"
                                                class="cursor-pointer text-purple-600 hover:text-purple-900 mr-2"
                                                :disabled="session.words_count === 0">
                                                Export
                                            </button>
                                            <button @click="deleteSession(session.id)"
                                                class="cursor-pointer text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <ul class="block sm:hidden mt-4 bg-white shadow-sm rounded-lg divide-y divide-gray-200">
                            <li v-for="session in studySessions.data" :key="session.id" class="p-4 flex flex-col">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-lg font-medium text-gray-900 truncate">{{ session.name }}</p>
                                        <p v-if="session.description" class="text-sm text-gray-600 mt-1 line-clamp-2">
                                            {{ session.description }}
                                        </p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <Link
                                            :href="route('session-studies.words.index', { study_session: session.id })"
                                            class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-medium">
                                        {{ session.words_count }} Words &rarr;
                                        </Link>
                                    </div>
                                </div>
                                <div class="flex flex-wrap justify-end gap-2 mt-2">
                                    <Link :href="route('study-sessions.edit', { study_session: session.id })">
                                        <button
                                            class="text-indigo-600 hover:text-indigo-900 text-sm py-1 px-2 rounded">Manage
                                            Words
                                        </button>
                                    </Link>
                                    <button @click="startSessionReview(session.id, 'failed')"
                                        :disabled="session.words_count === 0"
                                        class="text-blue-600 hover:text-blue-900 text-sm py-1 px-2 rounded">Study
                                        failed
                                    </button>
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <button
                                                class="text-gray-500 hover:text-gray-700 p-1 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors duration-200">
                                                <MoreVertical class="h-5 w-5" />
                                            </button>
                                        </template>

                                        <template #content>
                                            <DropdownLink @click="startSessionReview(session.id, 'all')" as="button"
                                                :disabled="session.words_count === 0">
                                                Study All
                                            </DropdownLink>
                                            <DropdownLink @click="exportSingleSessionToCsv(session.id)" as="button"
                                                :disabled="session.words_count === 0">
                                                Export
                                            </DropdownLink>
                                            <DropdownLink @click="deleteSession(session.id)" as="button">
                                                Delete Session
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <Pagination :pagination="studySessions" :current-filters="{ search: search }" />
                </div>
            </div>
        </div>
    </div>
</template>