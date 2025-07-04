<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
// No need for onMounted if it's not being used for initial logic
// import { onMounted } from 'vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    user: Object,
    all_roles: Array,
});

// Form for updating user details
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    roles: props.user.current_roles || [],
});

// Form for deleting the user
const deleteForm = useForm({}); // Use a separate form for delete action

const submit = () => {
    form.patch(route('admin.users.update', props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Flash message is handled by the redirect in the controller
        },
        onError: () => {
            // Error messages will automatically appear below the fields
        }
    });
};

const confirmAndDeleteUser = () => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        deleteForm.delete(route('admin.users.destroy', props.user.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Controller redirects to index with flash message on success
            },
            onError: () => {
                alert('Error deleting user. Please try again.');
            }
        });
    }
};
</script>

<template>
    <Head :title="`Edit User: ${user.name}`" />

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-semibold mb-6">Edit User: {{ user.name }}</h2>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="name" class="block font-medium text-sm text-gray-700">Name</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                                autofocus
                            />
                            <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-2">User Roles</label>
                            <div class="space-y-2">
                                <div v-for="role in all_roles" :key="role.id" class="flex items-center">
                                    <input
                                        :id="`role-${role.id}`"
                                        :value="role.name"
                                        v-model="form.roles"
                                        type="checkbox"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    />
                                    <label :for="`role-${role.id}`" class="ml-2 block text-sm text-gray-900">
                                        {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                                    </label>
                                </div>
                            </div>
                            <div v-if="form.errors.roles" class="text-red-600 text-sm mt-1">
                                {{ form.errors.roles }}
                            </div>
                            <div v-if="form.errors['roles.*']" class="text-red-600 text-sm mt-1">
                                {{ form.errors['roles.*'] }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Update User
                            </button>

                            <button
                                type="button"
                                @click="confirmAndDeleteUser"
                                :disabled="deleteForm.processing"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Delete User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>