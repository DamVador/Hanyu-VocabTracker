<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { watch, ref } from 'vue';
import Pagination from '@/components/Pagination.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    users: Object,
    filters: Object,
    allRoles: Array,
    activeUsersCount: Number,
});

const form = ref({
    search: props.filters.search || '',
    role: props.filters.role || '',
});

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
watch(() => form.value.search, () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

watch(() => form.value.role, () => {
    applyFilters();
});

const applyFilters = () => {
    router.get(route('admin.users.index'), {
        search: form.value.search,
        role: form.value.role,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    form.value.search = '';
    form.value.role = '';
    applyFilters();
};
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

                    <div class="mb-6 flex flex-col sm:flex-row gap-4 items-center">
                        <div class="flex-grow w-full sm:w-auto">
                            <label for="search" class="sr-only">Search</label>
                            <input id="search" type="text" v-model="form.search"
                                placeholder="Search by name or email..."
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <div class="w-full sm:w-auto">
                            <label for="role" class="sr-only">Filter by Role</label>
                            <select id="role" v-model="form.role"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Roles</option>
                                <option v-for="roleName in allRoles" :key="roleName" :value="roleName">
                                    {{ roleName.charAt(0).toUpperCase() + roleName.slice(1) }}
                                </option>
                            </select>
                        </div>

                        <button @click="resetFilters"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto">
                            Reset Filters
                        </button>
                    </div>

                    <div v-if="users.data.length > 0">
                        <div class="overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Username
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            First Name
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Last Name
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Roles
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Country
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            City
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Words Registered
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                                            {{ user.username }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.first_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.last_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-for="(role) in user.roles" :key="role"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                                {{ role }}
                                            </span>
                                            <span v-if="user.roles.length === 0" class="text-gray-400">None</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.country }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.city }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.words_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.created_at }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.users.edit', user.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="py-12">
                            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 text-gray-900">
                                        <div v-if="users && users.data.length > 0">
                                            <Pagination :pagination="users" :current-filters="form" />
                                        </div>
                                        <div v-else class="text-center text-gray-500 py-8">
                                            No users found matching your criteria.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div v-else class="text-center text-gray-500 py-8">
                        No users found matching your criteria.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>