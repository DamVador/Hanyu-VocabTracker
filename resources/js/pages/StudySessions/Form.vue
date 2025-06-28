<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue'; // Assuming you have this component
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    studySession: {
        type: Object,
        default: null, // Null for create mode
    },
    userWords: { // All words belonging to the current user
        type: Array,
        required: true,
    },
    isEdit: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    name: props.studySession?.name || '',
    description: props.studySession?.description || '',
    // Initialize word_ids with already attached words for edit mode
    word_ids: props.studySession?.words?.map(word => word.id) || [],
});

const searchTerm = ref('');
const filteredWords = computed(() => {
    if (!searchTerm.value) {
        return props.userWords;
    }
    const lowerSearchTerm = searchTerm.value.toLowerCase();
    return props.userWords.filter(word =>
        word.chinese_word.toLowerCase().includes(lowerSearchTerm) ||
        word.pinyin.toLowerCase().includes(lowerSearchTerm) ||
        word.translation.toLowerCase().includes(lowerSearchTerm)
    );
});

// Use a Set for efficient tracking of selected word IDs
const selectedWordIds = ref(new Set(form.word_ids));

// Watch for changes in the form.word_ids (e.g., initial load in edit mode)
watch(() => form.word_ids, (newVal) => {
    selectedWordIds.value = new Set(newVal);
}, { immediate: true });

// Sync the form's word_ids with the selectedWordIds Set whenever it changes
watch(selectedWordIds, (newVal) => {
    form.word_ids = Array.from(newVal);
}, { deep: true });


const toggleWordSelection = (wordId) => {
    if (selectedWordIds.value.has(wordId)) {
        selectedWordIds.value.delete(wordId);
    } else {
        selectedWordIds.value.add(wordId);
    }
};

const submit = () => {
    if (props.isEdit) {
        form.put(route('study-sessions.update', props.studySession.id), {
            onSuccess: () => {
                // Redirects are handled by the controller
            },
            onError: (errors) => {
                console.error('Error updating session:', errors);
            }
        });
    } else {
        form.post(route('study-sessions.store'), {
            onSuccess: () => {
                // Redirects are handled by the controller
            },
            onError: (errors) => {
                console.error('Error creating session:', errors);
            }
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="p-6 text-black">
        <div>
            <InputLabel for="name" value="Session Name" />
            <TextInput
                id="name"
                type="text"
                class="mt-1 block w-full"
                v-model="form.name"
                required
                autofocus
            />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div class="mt-4">
            <InputLabel for="description" value="Description (Optional)" />
            <TextareaInput
                id="description"
                class="mt-1 block w-full"
                v-model="form.description"
                rows="3"
            />
            <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Words in this Session</h3>
            <p class="text-sm text-gray-600 mb-2">Select words from your dictionary to add to this session.</p>

            <div class="mb-4">
                <TextInput
                    type="text"
                    placeholder="Search words..."
                    v-model="searchTerm"
                    class="w-full"
                />
            </div>

            <div class="border rounded-md max-h-60 overflow-y-auto p-3">
                <div v-if="filteredWords.length === 0" class="text-gray-500 text-sm italic">
                    No words found matching your search, or you haven't added any words yet.
                </div>
                <div v-for="word in filteredWords" :key="word.id" class="flex items-center mb-2">
                    <input
                        type="checkbox"
                        :id="`word-${word.id}`"
                        :value="word.id"
                        :checked="selectedWordIds.has(word.id)"
                        @change="toggleWordSelection(word.id)"
                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                    />
                    <label :for="`word-${word.id}`" class="ml-2 text-sm text-gray-900 cursor-pointer">
                        {{ word.chinese_word }} ({{ word.pinyin }}) - {{ word.translation }}
                    </label>
                </div>
            </div>
            <InputError class="mt-2" :message="form.errors.word_ids" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ isEdit ? 'Update Session' : 'Create Session' }}
            </PrimaryButton>
        </div>
    </form>
</template>