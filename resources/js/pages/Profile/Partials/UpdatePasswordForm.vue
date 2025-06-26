<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('Profile.update-password'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Update Password</h2>
            <p class="mt-1 text-sm text-gray-600">
                Ensure your account is using a long, random password to stay secure.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <label for="current_password" class="block font-medium text-sm text-gray-700">Current Password</label>
                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-black"
                    autocomplete="current-password"
                />
                <div v-if="form.errors.current_password" class="text-red-600 text-sm mt-1">
                    {{ form.errors.current_password }}
                </div>
            </div>

            <div>
                <label for="password" class="block font-medium text-sm text-gray-700">New Password</label>
                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-black"
                    autocomplete="new-password"
                />
                <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
                    {{ form.errors.password }}
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirm Password</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-black"
                    autocomplete="new-password"
                />
                <div v-if="form.errors.password_confirmation" class="text-red-600 text-sm mt-1">
                    {{ form.errors.password_confirmation }}
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Save
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>