<script setup lang="ts">
import { defineProps } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import BlogLayout from '@/layouts/BlogLayout.vue';

// Indique à Inertia d'utiliser le layout principal du blog
defineOptions({ layout: BlogLayout });

const pageProps = usePage().props;
const authUser = pageProps.auth.user;

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
});

const isAuthor = authUser && authUser.id === props.post.author.id;
const isAdmin = authUser && authUser.is_admin;
</script>

<template>
    <Head :title="post.title" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-black">
        <article class="bg-white p-8 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-4xl font-bold text-gray-900">{{ post.title }}</h1>
                <!-- Bouton d'édition visible uniquement pour l'auteur ou l'admin -->
                <Link v-if="isAuthor || isAdmin" :href="route('blog.edit', post.id)"
                      class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Edit
                </Link>
            </div>
            <div class="text-sm text-gray-500 mb-6">
                By {{ post.author.username }} on {{ new Date(post.created_at).toLocaleDateString() }}
            </div>
            
            <!-- Appliquez la classe `prose` pour styliser le contenu d'article -->
            <div class="prose max-w-none">
                <div v-html="post.content"></div>
            </div>
        </article>
    </div>
</template>
