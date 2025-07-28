<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import StudySessionForm from './Form.vue';

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
                <h2 class="text-2xl font-semibold p-6 border-b border-gray-200 text-black">Edit Study Session: {{ props.studySession.name }}</h2>
                <StudySessionForm :study-session="studySession" :user-words="userWords" :is-edit="true" :current-session-id="studySession.id" />
            </div>
        </div>
    </div>
</template>