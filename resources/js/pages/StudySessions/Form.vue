<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import Input from '@/components/Input.vue';
import TextareaInput from '@/components/TextareaInput.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    studySession: {
        type: Object,
        default: null,
    },
    userWords: {
        type: Array,
        required: true,
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
    currentSessionId: {
        type: Number,
        default: null,
    },
});

const initialSelectedWordIds = props.studySession?.words?.map(word => word.id) || [];
const selectedWordIds = ref(new Set(initialSelectedWordIds));

const form = useForm({
    name: props.studySession?.name || '',
    description: props.studySession?.description || '',
    word_ids: Array.from(selectedWordIds.value),
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
            <Input
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
                <Input
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

        <div class="flex items-center justify-end mt-6 gap-3">
            <Link v-if="isEdit && currentSessionId" :href="route('words.create', {
                redirect_to: route('study-sessions.edit', { study_session: currentSessionId }),
                prefill_study_session_id: currentSessionId
            })">
                <PrimaryButton type="button">Add New Word</PrimaryButton>
            </Link>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ isEdit ? 'Update Session' : 'Create Session' }}
            </PrimaryButton>
        </div>
    </form>
</template>