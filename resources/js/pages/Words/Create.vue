<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    allTags: Array,
    userStudySessions: Array,
});

const form = useForm({
    chinese_word: '',
    pinyin: '',
    translation: '',
    tags: [],
    study_session_ids: [],
});

const currentTagInput = ref('');
const filteredTags = ref([]);
const showTagDropdown = ref(false);

watch(currentTagInput, (newInput) => {
    if (newInput.length > 0) {
        filteredTags.value = props.allTags.filter(tag =>
            tag.toLowerCase().includes(newInput.toLowerCase())
        ).filter(tag => !form.tags.includes(tag));
        showTagDropdown.value = true;
    } else {
        filteredTags.value = [];
        showTagDropdown.value = false;
    }
});

const addTag = (tag) => {
    if (tag && !form.tags.includes(tag)) {
        form.tags.push(tag);
    }
    currentTagInput.value = '';
    showTagDropdown.value = false;
};

const removeTag = (tag) => {
    form.tags = form.tags.filter(t => t !== tag);
};

const selectedStudySessionIds = ref(new Set()); // Initialize as empty for new word

watch(selectedStudySessionIds, (newSet) => {
    form.study_session_ids = Array.from(newSet);
}, { deep: true });

const toggleStudySession = (sessionId) => {
    if (selectedStudySessionIds.value.has(sessionId)) {
        selectedStudySessionIds.value.delete(sessionId);
    } else {
        selectedStudySessionIds.value.add(sessionId);
    }
};

const submit = () => {
    form.post(route('words.save'), {
        onFinish: () => {
            form.reset('chinese_word', 'pinyin', 'translation', 'tags');
            selectedStudySessionIds.value.clear();
        },
    });
};
</script>

<template>
    <Head title="Add New Word" />

        <h3 class="text-2xl font-bold mb-2 ml-2 text-black">Add New Word</h3>

        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8 text-black">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div>
                            <InputLabel for="chinese_word" value="Chinese Word" />
                            <TextInput
                                id="chinese_word"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.chinese_word"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.chinese_word" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="pinyin" value="Pinyin" />
                            <TextInput
                                id="pinyin"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.pinyin"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.pinyin" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="translation" value="Translation" />
                            <TextInput
                                id="translation"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.translation"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.translation" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="tags" value="Tags (type and press Enter, or select from dropdown)" />
                            <TextInput
                                id="tags"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="currentTagInput"
                                @keydown.enter.prevent="addTag(currentTagInput)"
                            />
                            <InputError class="mt-2" :message="form.errors.tags" />

                            <div v-if="showTagDropdown && filteredTags.length > 0" class="mt-1 border border-gray-300 rounded-md shadow-lg bg-white max-h-40 overflow-y-auto">
                                <div
                                    v-for="tag in filteredTags"
                                    :key="tag"
                                    @click="addTag(tag)"
                                    class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                >
                                    {{ tag }}
                                </div>
                            </div>

                            <div class="mt-2 flex flex-wrap gap-2">
                                <span v-for="tag in form.tags" :key="tag" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ tag }}
                                    <button type="button" @click="removeTag(tag)" class="flex-shrink-0 ml-1.5 h-3 w-3 rounded-full inline-flex items-center justify-center text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none focus:bg-indigo-500 focus:text-white">
                                        <span class="sr-only">Remove tag</span>
                                        <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                            <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <InputLabel value="Add to Study Sessions" />
                            <p class="text-sm text-gray-600 mb-2">Select one or more sessions to add this word to:</p>
                            <div class="border rounded-md p-4 max-h-48 overflow-y-auto bg-gray-50">
                                <div v-if="userStudySessions.length === 0" class="text-gray-500 italic">
                                    You haven't created any study sessions yet. <Link :href="route('study-sessions.create')" class="text-blue-600 hover:underline">Create one</Link>!
                                </div>
                                <div v-for="session in userStudySessions" :key="session.id" class="flex items-center mb-2">
                                    <input
                                        type="checkbox"
                                        :id="`session-${session.id}`"
                                        :value="session.id"
                                        :checked="selectedStudySessionIds.has(session.id)"
                                        @change="toggleStudySession(session.id)"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    />
                                    <label :for="`session-${session.id}`" class="ml-2 text-sm text-gray-900 cursor-pointer">
                                        {{ session.name }}
                                    </label>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.study_session_ids" />
                        </div>
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