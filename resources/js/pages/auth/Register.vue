<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import  Input  from '@/components/Input.vue';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    username: '',
    first_name: '',
    last_name: '',
    email: '',
    country: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6 text-black">
            <div class="grid gap-y-4 md:grid-cols-2 md:gap-x-6">

                <div class="grid gap-2">
                    <Label for="username">Username</Label>
                    <Input id="username" type="text" required autofocus :tabindex="1" autocomplete="username" v-model="form.username" placeholder="Your username" />
                    <InputError :message="form.errors.username" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" required :tabindex="4" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="first_name">First Name</Label>
                    <Input id="first_name" type="text" required :tabindex="2" autocomplete="given-name" v-model="form.first_name" placeholder="Your first name" />
                    <InputError :message="form.errors.first_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="last_name">Last Name</Label>
                    <Input id="last_name" type="text" required :tabindex="3" autocomplete="family-name" v-model="form.last_name" placeholder="Your last name" />
                    <InputError :message="form.errors.last_name" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="country">Country</Label>
                    <Input id="country" type="text" required :tabindex="5" autocomplete="country-name" v-model="form.country" placeholder="Your country" />
                    <InputError :message="form.errors.country" />
                </div>

                <div class="grid gap-2 md:col-span-2"> <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="6"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2 md:col-span-2"> <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="7"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-4 w-full bg-indigo-600 text-white hover:bg-indigo-700 md:col-span-2" tabindex="8" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-gray-700">
                Already have an account?
                <TextLink :href="route('login')" class="font-semibold text-indigo-600 hover:text-indigo-800" :tabindex="9">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>