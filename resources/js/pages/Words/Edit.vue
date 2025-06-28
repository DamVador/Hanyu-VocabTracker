<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3'; // Import `router` for the delete action

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    word: Object,
    allTags: Array,
});

const form = useForm({
    chinese_word: props.word.chinese_word,
    pinyin: props.word.pinyin,
    translation: props.word.translation,
    tags: props.word.current_tags || [],
});

const submit = () => {
    form.patch(route('words.update', props.word.id), {
        onSuccess: () => {
        },
    });
};

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this word? This action cannot be undone.')) {
        router.delete(route('words.destroy', props.word.id), {
            onSuccess: () => {
                // The controller will redirect to words.index with a flash message
            },
            onError: (errors) => {
                console.error('Error deleting word:', errors);
                alert('Failed to delete word. Please check console for details.');
            }
        });
    }
};

</script>

<template>
    <Head :title="`Edit Word: ${word.pinyin}`" />

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-semibold mb-6">Edit Word: {{ word.pinyin }}</h2>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="chinese_word" class="block font-medium text-sm text-gray-700">Chinese Word</label>
                            <input
                                id="chinese_word"
                                v-model="form.chinese_word"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                                autofocus
                            />
                            <div v-if="form.errors.chinese_word" class="text-red-600 text-sm mt-1">
                                {{ form.errors.chinese_word }}
                            </div>
                        </div>

                        <div>
                            <label for="pinyin" class="block font-medium text-sm text-gray-700">Pinyin</label>
                            <input
                                id="pinyin"
                                v-model="form.pinyin"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="form.errors.pinyin" class="text-red-600 text-sm mt-1">
                                {{ form.errors.pinyin }}
                            </div>
                        </div>

                        <div>
                            <label for="translation" class="block font-medium text-sm text-gray-700">Translation</label>
                            <input
                                id="translation"
                                v-model="form.translation"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="form.errors.translation" class="text-red-600 text-sm mt-1">
                                {{ form.errors.translation }}
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-2">Tags</label>
                            <div class="flex flex-wrap gap-2">
                                <div v-for="tag in allTags" :key="tag" class="flex items-center">
                                    <input
                                        :id="`tag-${tag}`"
                                        :value="tag"
                                        v-model="form.tags"
                                        type="checkbox"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    />
                                    <label :for="`tag-${tag}`" class="ml-2 block text-sm text-gray-900">
                                        {{ tag.charAt(0).toUpperCase() + tag.slice(1) }}
                                    </label>
                                </div>
                            </div>
                            <div v-if="form.errors.tags" class="text-red-600 text-sm mt-1">
                                {{ form.errors.tags }}
                            </div>
                            <div v-if="form.errors['tags.*']" class="text-red-600 text-sm mt-1">
                                {{ form.errors['tags.*'] }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Update Word
                            </button>

                            <button
                                type="button"
                                @click="confirmDelete"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Delete Word
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>