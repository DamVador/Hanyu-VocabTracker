<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({ layout: AuthenticatedLayout });

defineProps({
    user: Object,
    status: String,
    countries: Array,
});

const selectedTab = ref('profile-information');
</script>

<template>
    <Head title="Profile" />

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-4 sm:px-8" aria-label="Tabs">
                        <button
                            @click="selectedTab = 'profile-information'"
                            :class="[
                                selectedTab === 'profile-information'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                            ]"
                            aria-current="page"
                        >
                            Profile Information
                        </button>
                        <button
                            @click="selectedTab = 'update-password'"
                            :class="[
                                selectedTab === 'update-password'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            Update Password
                        </button>
                        <button
                            @click="selectedTab = 'delete-account'"
                            :class="[
                                selectedTab === 'delete-account'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            Delete Account
                        </button>
                    </nav>
                </div>

                <div class="p-4 sm:p-8">
                    <div v-if="selectedTab === 'profile-information'">
                        <UpdateProfileInformationForm :user="user" :countries="countries" class="max-w-xl" />
                    </div>

                    <div v-if="selectedTab === 'update-password'">
                        <UpdatePasswordForm class="max-w-xl" />
                    </div>

                    <div v-if="selectedTab === 'delete-account'">
                        <DeleteUserForm class="max-w-xl" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>