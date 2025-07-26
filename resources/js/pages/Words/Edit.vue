<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import WordForm from './Form.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    word: Object,
    allTags: Array,
    currentTags: Array,
    userStudySessions: Array,
    attachedStudySessionIds: Array,
});

const form = useForm({
    chinese_word: props.word.chinese_word,
    pinyin: props.word.pinyin,
    translation: props.word.translation,
    notes: props.word.notes || '',
    tags: props.currentTags || [],
    study_session_ids: props.attachedStudySessionIds || [],
});

const submit = () => {
    form.put(route('words.update', props.word.id), {
        // no specific onFinish
    });
};
</script>

<template>

    <Head :title="`Edit Word: ${word.chinese_word}`" />
    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 text-black">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <h2 class="text-2xl font-semibold p-6 border-b border-gray-200 text-black">Edit Word</h2>
                <form @submit.prevent="submit" class="p-6">
                    <WordForm
                        :form="form"
                        :all-tags="allTags"
                        :user-study-sessions="userStudySessions"
                        :is-edit-mode="true"
                    />

                    <div class="flex items-center justify-end mt-6">
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Update Word
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>