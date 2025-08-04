<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import BlogLayout from '@/layouts/BlogLayout.vue';
import InputError from '@/components/InputError.vue';
import Input from '@/components/Input.vue';
import InputLabel from '@/components/InputLabel.vue';
import Editor from '@tinymce/tinymce-vue';

defineOptions({
    layout: BlogLayout,
    inheritAttrs: false,
});

const pageProps = usePage().props;
const post = pageProps.post;
const tinymceApiKey = pageProps.tinymceApiKey;

const form = useForm({
    title: post.title,
    content: post.content,
});

const submit = () => {
    form.put(route('blog.update', post.id));
};
</script>

<template>
    <Head :title="'Edit Post: ' + post.title" />

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit Post</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <InputLabel for="title" value="Title" />
                    <Input type="text" id="title" v-model="form.title" required />
                    <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <div>
                    <InputLabel for="content" value="Content" />
                    <Editor
                        :api-key="tinymceApiKey"
                        id="content"
                        v-model="form.content"
                        :init="{
                            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
                            min_height: 300,
                            content_style: 'body { font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif; font-size: 14px; }'
                        }"
                    />
                    <InputError class="mt-2" :message="form.errors.content" />
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                        Update Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
