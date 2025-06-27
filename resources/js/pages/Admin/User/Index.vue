<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    users: Object, // Paginated users data
});

const flash = computed(() => usePage().props.flash);
</script>

<template>
    <Head title="Manage Users" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold">User Management</h2>
                    </div>

                    <div v-if="users.data.length > 0">
                        <div class="overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Roles
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Registered On
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ user.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-for="(role, index) in user.roles" :key="role" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                                {{ role }}
                                            </span>
                                            <span v-if="user.roles.length === 0" class="text-gray-400">None</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.created_at }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</Link>
                                            </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-center">
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <Link
                                    v-for="(link, index) in users.links"
                                    :key="index"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                    :class="{
                                        'bg-indigo-600 text-white border-indigo-600': link.active,
                                        'bg-white border-gray-300 text-gray-700 hover:bg-gray-50': !link.active,
                                        'rounded-l-md': index === 0,
                                        'rounded-r-md': index === users.links.length - 1,
                                        'cursor-not-allowed opacity-50': !link.url
                                    }"
                                />
                            </nav>
                        </div>

                    </div>
                    <div v-else class="text-center text-gray-500 py-8">
                        No users found.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>