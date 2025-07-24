<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Input from '@/components/Input.vue';
import { ref, computed } from 'vue'; // Importez ref et computed
import Select from '@/components/Select.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    user: Object,
    all_roles: Array,
    countries: Array,
});

const form = useForm({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    username: props.user.username,
    email: props.user.email,
    country: props.user.country,
    city: props.user.city,
    roles: props.user.current_roles || [],
});

const deleteForm = useForm({});

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

const countrySearchTerm = ref(props.user.country ? props.countries.find(c => c.alpha2 === props.user.country)?.name : ''); // Valeur d'affichage dans l'input
const showCountrySuggestions = ref(false);

const filteredCountries = computed(() => {
    if (!countrySearchTerm.value || !props.countries) {
        return [];
    }
    const lowerCaseSearchTerm = countrySearchTerm.value.toLowerCase();
    return props.countries.filter(country =>
        country.name.toLowerCase().includes(lowerCaseSearchTerm)
    ).slice(0, 8); // Limitez les suggestions pour la performance
});

const handleCountryInputBlur = () => {
    setTimeout(() => {
        showCountrySuggestions.value = false;
    }, 150);
};

const selectCountry = (country) => {
    countrySearchTerm.value = country.name;
    form.country = country.alpha2;
    showCountrySuggestions.value = false;
};
</script>

<template>
    <Head :title="`Edit User: ${user.username}`" />

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-semibold mb-6">Edit User: {{ user.username }}</h2>

                    <form @submit.prevent="submit" class="space-y-6">

                        <div>
                            <label for="username" class="block font-medium text-sm text-gray-700">Username</label>
                            <Input
                                id="username"
                                v-model="form.username"
                                type="text"
                                class="mt-1"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <div v-if="form.errors.username" class="text-red-600 text-sm mt-1">
                                {{ form.errors.username }}
                            </div>
                        </div>

                        <div>
                            <label for="first_name" class="block font-medium text-sm text-gray-700">First Name</label>
                            <Input
                                id="first_name"
                                v-model="form.first_name"
                                type="text"
                                class="mt-1"
                                required
                            />
                            <div v-if="form.errors.first_name" class="text-red-600 text-sm mt-1">
                                {{ form.errors.first_name }}
                            </div>
                        </div>

                        <div>
                            <label for="last_name" class="block font-medium text-sm text-gray-700">Last Name</label>
                            <Input
                                id="last_name"
                                v-model="form.last_name"
                                type="text"
                                class="mt-1"
                                required
                            />
                            <div v-if="form.errors.last_name" class="text-red-600 text-sm mt-1">
                                {{ form.errors.last_name }}
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1"
                                required
                            />
                            <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <div class="relative">
                            <label for="country_search" class="block font-medium text-sm text-gray-700">Country</label>
                            <Input
                                id="country_search"
                                type="text"
                                v-model="countrySearchTerm"
                                @focus="showCountrySuggestions = true"
                                @blur="handleCountryInputBlur"
                                class="mt-1"
                                autocomplete="off"
                            />
                            <InputError class="mt-2" :message="form.errors.country" />

                            <ul
                                v-if="showCountrySuggestions && filteredCountries.length"
                                class="text-gray-900 absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto mt-1"
                            >
                                <li
                                    v-for="country in filteredCountries"
                                    :key="country.alpha2"
                                    @mousedown.prevent="selectCountry(country)"
                                    class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                >
                                    {{ country.name }} {{ country.emoji }}
                                </li>
                            </ul>
                        </div>

                        <div>
                            <label for="city" class="block font-medium text-sm text-gray-700">City</label>
                            <Input
                                id="city"
                                v-model="form.city"
                                type="text"
                                class="mt-1"
                                required
                            />
                            <div v-if="form.errors.city" class="text-red-600 text-sm mt-1">
                                {{ form.errors.city }}
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