<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import StudySessionForm from './Form.vue'; // Import the shared form component

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    studySession: Object,
    userWords: Array,
});
</script>

<template>
    <Head :title="`Edit Study Session: ${studySession.name}`" />

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <h2 class="text-2xl font-semibold p-6 border-b border-gray-200">Edit Study Session: {{ studySession.name }}</h2>
                <StudySessionForm :study-session="studySession" :user-words="userWords" :is-edit="true" />
            </div>
        </div>
    </div>
</template>