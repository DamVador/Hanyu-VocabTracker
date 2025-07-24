<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TextInput from '@/components/TextInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import Pagination from '@/components/Pagination.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    studySessions: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(
        route('study-sessions.index'),
        { search: value, page: 1 },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        }
    );
}, { debounce: 300 });

const clearSearch = () => {
    search.value = '';
};

const deleteSession = (sessionId) => {
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

const startSessionReview = (sessionId, mode) => {
    router.get(route('study.sessionReview', { study_session: sessionId, mode: mode }));
};

const exportSingleSessionToCsv = (sessionId) => {
    window.location.href = route('study-sessions.exportSingleCsv', { study_session: sessionId });
};

</script>

<template>

    <Head title="My Study Sessions" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                            My Study Sessions
                        </h2>
                        <div class="flex gap-4">
                            <Link :href="route('study-sessions.create')">
                            <PrimaryButton>Create New Study Session</PrimaryButton>
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

                    <div v-else-if="studySessions && studySessions.data.length > 0" class="overflow-x-auto mt-4">
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
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ session.description || '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <Link :href="route('session-studies.words.index', { study_session: session.id })"
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
                    <Pagination :pagination="studySessions" :current-filters="{ search: search }" />
                </div>
            </div>
        </div>
    </div>
</template>