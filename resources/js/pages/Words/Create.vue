<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import WordForm from './Form.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    allTags: Array,
    userStudySessions: Array,
    redirect_to: {
        type: String,
        default: () => window.route('words.index'),
    },
    prefill_study_session_id: {
        type: Number,
        default: null,
    },
});

const initialStudySessionIds = props.prefill_study_session_id ? [props.prefill_study_session_id] : [];

const form = useForm({
    chinese_word: '',
    pinyin: '',
    translation: '',
    notes: '',
    tags: [],
    study_session_ids: initialStudySessionIds,
    _redirect_to: props.redirect_to,
});

const submit = () => {
    form.post(route('words.save'), {
        onFinish: () => {
            form.reset('chinese_word', 'pinyin', 'translation', 'notes', 'tags');
        },
    });
};
</script>

<template>
    <Head title="Add New Word" />

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 text-black">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <h2 class="text-2xl font-semibold p-6 border-b border-gray-200 text-black">Add New Word</h2>
                <form @submit.prevent="submit" class="p-6">
                    <WordForm
                        :form="form"
                        :all-tags="allTags"
                        :user-study-sessions="userStudySessions"
                        :is-edit-mode="false"
                    />

                    <div class="flex items-center justify-end mt-6">
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Create Word
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>