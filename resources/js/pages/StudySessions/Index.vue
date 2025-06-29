<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    studySessions: Array,
});

// Access flash messages (like 'success' or 'error' from redirects)
const flash = computed(() => usePage().props.flash);

const deleteSession = (sessionId) => {
    if (confirm('Are you sure you want to delete this study session? All associated words will remain in your dictionary.')) {
        router.delete(route('study-sessions.destroy', sessionId), {
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
</script>

<template>
    <Head title="My Study Sessions" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-semibold mb-4">My Study Sessions</h2>

                    <Link :href="route('study-sessions.create')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mb-4">
                        Create New Study Session
                    </Link>

                    <div v-if="studySessions.length === 0" class="text-gray-600 italic">
                        No study sessions created yet. Click "Create New Study Session" to get started!
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Words</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="session in studySessions" :key="session.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ session.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ session.description || '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ session.words_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('study-sessions.edit', session.id)" class="text-indigo-600 hover:text-indigo-900 mr-2">Manage Words</Link>
                                        <button @click="startSessionReview(session.id, 'all')" class="text-blue-600 hover:text-blue-900 mr-2" :disabled="session.words_count === 0">Study All</button>
                                        <button @click="startSessionReview(session.id, 'failed')" class="text-red-600 hover:text-red-900 mr-2" :disabled="session.words_count === 0">Study Failed</button>
                                        <button @click="deleteSession(session.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>