<script setup lang="ts">
import { defineProps, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/components/Pagination.vue';
import Input from '@/components/Input.vue';
import { debounce } from 'lodash';

// Définissez les props en premier, avant toute logique qui les utilise.
const props = defineProps({
    posts: {
        type: Object,
        required: true,
    },
    isAdmin: {
        type: Boolean,
        required: true,
    },
    // La prop 'search' est ajoutée pour récupérer la valeur de recherche depuis l'URL
    search: {
        type: String,
        default: '',
    },
});

// `searchTerm` est une variable réactive initialisée avec la valeur de la prop `search`
const searchTerm = ref(props.search);

// Définition d'une fonction nommée pour éviter le problème de compilation
const handleSearch = (value: string) => {
    // Lorsque la valeur change, on effectue une nouvelle requête Inertia
    // en préservant le scroll, en gardant l'état et en modifiant l'URL.
    // L'objet `query` contient les paramètres d'URL, y compris le terme de recherche.
    router.get(
        route('blog.index'),
        { search: value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true, // Remplace l'entrée de l'historique pour ne pas l'encombrer
        }
    );
};

// On utilise `watch` pour observer les changements dans `searchTerm`
// en utilisant la fonction nommée et le debounce
watch(searchTerm, debounce(handleSearch, 300)); // Délais de 300ms pour éviter une requête à chaque frappe

</script>

<script lang="ts">
import BlogLayout from '@/layouts/BlogLayout.vue';

export default {
    // We define the layout here instead of using defineOptions
    // to fix the compilation error in some environments.
    layout: BlogLayout,
};
</script>

<template>
    <Head title="Blog" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <!-- Remplacement du titre par la barre de recherche -->
            <div :class="{'md:w-3/4': props.isAdmin, 'md:w-full': !props.isAdmin}" class="w-full">
                <Input
                    type="text"
                    v-model="searchTerm"
                    placeholder="Search for articles..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-colors duration-200"
                />
            </div>
            
            <!-- Le bouton est toujours affiché si la prop "isAdmin" est true -->
            <Link v-if="props.isAdmin" :href="route('blog.create')"
                  class="w-full md:w-auto text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                Create Article
            </Link>
        </div>

        <div v-if="posts.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="post in posts.data" :key="post.id"
                 class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <Link :href="route('blog.show', post.slug)" class="block">
                    <h2 class="text-2xl font-semibold text-indigo-600 hover:text-indigo-800 transition-colors duration-200">{{ post.title }}</h2>
                </Link>
                <div class="text-sm text-gray-500 mt-2">
                    By {{ post.author.username }} on {{ new Date(post.created_at).toLocaleDateString() }}
                </div>
                <div v-html="post.content" class="text-gray-700 mt-4 line-clamp-3"></div>
                <div class="mt-4">
                    <Link :href="route('blog.show', post.slug)"
                          class="text-indigo-600 hover:text-indigo-800 font-semibold transition-colors duration-200">
                        Read more &rarr;
                    </Link>
                </div>
            </div>
        </div>
        <div v-else class="text-center text-gray-500 mt-10">
            No posts have been published yet.
        </div>
        
        <div v-if="posts.data.length" class="mt-8">
            <Pagination :pagination="posts" />
        </div>
    </div>
</template>
