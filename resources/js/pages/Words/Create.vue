<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineOptions({ layout: AuthenticatedLayout });

const form = useForm({
  chinese_word: '',
  pinyin: '',
  translation: '',
  tags: [], // Initialize tags as an empty array
});

const submit = () => {
  form.post(route('words.save'), {
    onFinish: () => form.reset('chinese_word', 'pinyin', 'translation', 'tags'), // Reset form fields on success
    // onError: (errors) => { /* Handle specific errors if needed */ },
  });
};

// Example for adding/removing tags (you might use a more sophisticated tag input component)
const addTag = (event) => {
  if (event.key === 'Enter' && event.target.value.trim() !== '') {
    form.tags.push(event.target.value.trim());
    event.target.value = '';
  }
};

const removeTag = (index) => {
  form.tags.splice(index, 1);
};
</script>

<template>
  <Head title="Add New Word" />

  <div class="py-12">
    <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          <h3 class="text-2xl font-bold mb-6">Add New Word</h3>

          <form @submit.prevent="submit">
            <div class="mb-4">
              <label for="chinese_word" class="block text-sm font-medium text-gray-700">Chinese Word</label>
              <input
                type="text"
                id="chinese_word"
                v-model="form.chinese_word"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
              />
              <div v-if="form.errors.chinese_word" class="text-red-600 text-sm mt-1">
                {{ form.errors.chinese_word }}
              </div>
            </div>

            <div class="mb-4">
              <label for="pinyin" class="block text-sm font-medium text-gray-700">Pinyin</label>
              <input
                type="text"
                id="pinyin"
                v-model="form.pinyin"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
              />
              <div v-if="form.errors.pinyin" class="text-red-600 text-sm mt-1">
                {{ form.errors.pinyin }}
              </div>
            </div>

            <div class="mb-4">
              <label for="translation" class="block text-sm font-medium text-gray-700">Translation</label>
              <input
                type="text"
                id="translation"
                v-model="form.translation"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
              />
              <div v-if="form.errors.translation" class="text-red-600 text-sm mt-1">
                {{ form.errors.translation }}
              </div>
            </div>

            <div class="mb-4">
              <label for="tagsInput" class="block text-sm font-medium text-gray-700">Tags (press Enter to add)</label>
              <input
                type="text"
                id="tagsInput"
                @keydown.enter.prevent="addTag"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="e.g., noun, verb, HSK1"
              />
              <div v-if="form.errors.tags" class="text-red-600 text-sm mt-1">
                {{ form.errors.tags }}
              </div>
              <div class="mt-2 flex flex-wrap gap-2">
                <span
                  v-for="(tag, index) in form.tags"
                  :key="index"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                >
                  {{ tag }}
                  <button type="button" @click="removeTag(index)" class="ml-1 -mr-0.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="sr-only">Remove tag</span>
                    <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                    </svg>
                  </button>
                </span>
              </div>
            </div>

            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
              <span v-if="form.processing">Saving...</span>
              <span v-else>Save Word</span>
            </button>

            <!-- <div v-if="$page.props.flash.success" class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-md">
                {{ $page.props.flash.success }}
            </div> -->
          </form>
        </div>
      </div>
    </div>
  </div>
</template>