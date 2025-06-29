<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import StudySessionForm from './Form.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    userWords: Array,
});
</script>

<template>
    <Head title="Create Study Session" />

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <h2 class="text-2xl font-semibold p-6 border-b border-gray-200 text-black">Create New Study Session</h2>
                <StudySessionForm :user-words="userWords" :is-edit="false" />
            </div>
        </div>
    </div>
</template>