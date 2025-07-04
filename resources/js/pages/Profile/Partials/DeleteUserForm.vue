<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    // Use nextTick to ensure the modal is rendered before focusing
    // if using a modal or similar. For this simple case, it might not be strictly needed,
    // but good practice if a complex modal pops up.
    // nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => {}, // Handled by backend redirect
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">Delete Account</h2>
            <p class="mt-1 text-sm text-gray-600">
                Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.
            </p>
        </header>

        <button
            type="button"
            @click="confirmUserDeletion"
            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
            Delete Account
        </button>

        <!-- Confirmation Modal / Section (simple inline for this example) -->
        <div v-if="confirmingUserDeletion" class="mt-4 p-6 bg-gray-50 rounded-lg shadow-inner">
            <h3 class="text-lg font-medium text-gray-900">Are you sure you want to delete your account?</h3>
            <p class="mt-1 text-sm text-gray-600">
                Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
            </p>

            <div class="mt-4">
                <label for="password_confirm" class="sr-only">Password</label>
                <input
                    id="password_confirm"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Password"
                    @keyup.enter="deleteUser"
                />
                <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
                    {{ form.errors.password }}
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button
                    type="button"
                    @click="confirmingUserDeletion = false; form.reset();"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    @click="deleteUser"
                    :disabled="form.processing"
                    class="ml-3 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <span v-if="form.processing">Deleting...</span>
                    <span v-else>Delete Account</span>
                </button>
            </div>
        </div>
    </section>
</template>