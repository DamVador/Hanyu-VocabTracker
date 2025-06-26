<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    user: Object, // User object from Profile/Edit.vue
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
});

const verificationLinkSent = ref(false);

const sendEmailVerification = () => {
    form.post(route('verification.send'), {
        onSuccess: () => (verificationLinkSent.value = true),
    });
};

const user = usePage().props.auth.user;
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>
            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update-information'))" class="mt-6 space-y-6">
            <div>
                <label for="name" class="block font-medium text-sm text-gray-700">Name</label>
                <input
                    id="name"
                    type="text"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-black"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                    {{ form.errors.name }}
                </div>
            </div>

            <div>
                <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                <input
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-black"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                    {{ form.errors.email }}
                </div>
            </div>

            <div v-if="user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div v-show="verificationLinkSent" class="mt-2 font-medium text-sm text-green-600">
                    A new verification link has been sent to your email address.
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
                    <p v-if="form.isDirty" class="text-sm text-gray-600">Changed.</p>
                    <p v-else-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>