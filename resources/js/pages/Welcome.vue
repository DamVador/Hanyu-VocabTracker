<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'; // Import Head for title, Link for navigation
import GuestLayout from '@/Layouts/GuestLayout.vue'; // Or your main app layout if you don't separate guest/auth layouts

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

// Access current user data if logged in
const user = usePage().props.auth.user;

// Set the layout for this page (assuming GuestLayout is a simple wrapper without auth-specific elements)
defineOptions({ layout: GuestLayout });
</script>

<template>
  <Head title="Welcome to HanyuVocabTracker" />

  <div class="relative min-h-screen bg-gradient-to-br from-indigo-500 to-purple-600 sm:flex sm:justify-center sm:items-center selection:bg-indigo-500 selection:text-white">

    <div class="absolute top-0 right-0 p-6 text-end">
      <Link
        v-if="user"
        :href="route('dashboard')"
        class="font-semibold text-white hover:text-gray-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
      >
        Dashboard
      </Link>

      <template v-else>
        <Link
          :href="route('login')"
          class="font-semibold text-white hover:text-gray-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
        >
          Log in
        </Link>

        <Link
          v-if="canRegister"
          :href="route('register')"
          class="ms-4 font-semibold text-white hover:text-gray-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
        >
          Register
        </Link>
      </template>
    </div>

    <div class="max-w-4xl mx-auto p-6 lg:p-8 text-center text-white">
      <section class="mb-16">
        <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight mb-4 leading-tight">
          Master Your Chinese Vocabulary.
        </h1>
        <p class="text-xl md:text-2xl opacity-90 mb-8">
          Track the Chinese words that you are learning and organize them with custom tags for more efficiency. Your personal language learning companion.
        </p>

        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <Link
            v-if="!user && canRegister"
            :href="route('register')"
            class="inline-flex items-center justify-center px-8 py-4 bg-white text-indigo-700 font-bold text-lg rounded-full shadow-lg hover:bg-gray-100 transition duration-300 transform hover:scale-105"
          >
            Get Started
          </Link>
          <Link
            v-if="user"
            :href="route('dashboard')"
            class="inline-flex items-center justify-center px-8 py-4 bg-white text-indigo-700 font-bold text-lg rounded-full shadow-lg hover:bg-gray-100 transition duration-300 transform hover:scale-105"
          >
            Go to Dashboard
          </Link>
          <Link
            v-if="!user && canLogin"
            :href="route('login')"
            class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white font-semibold text-lg rounded-full hover:bg-white hover:text-indigo-700 transition duration-300 transform hover:scale-105"
          >
            Login
          </Link>
        </div>
      </section>

      <section class="bg-white bg-opacity-10 backdrop-blur-sm p-8 rounded-lg shadow-xl mb-16 text-black">
          <h2 class="text-4xl font-bold mb-8 text-black">Why Hanyu VocabTracker ?</h2>
          <div class="grid md:grid-cols-3 gap-8">
              <div class="p-4 bg-white bg-opacity-10 rounded-lg">
                  <div class="text-indigo-300 text-3xl mb-3">📝</div>
                  <h3 class="text-xl font-semibold mb-2">Effortless Tracking</h3>
                  <p class="opacity-80">Quickly save new words with Chinese characters, Pinyin, and translations.</p>
              </div>
              <div class="p-4 bg-white bg-opacity-10 rounded-lg">
                  <div class="text-indigo-300 text-3xl mb-3">🏷️</div>
                  <h3 class="text-xl font-semibold mb-2">Smart Organization</h3>
                  <p class="opacity-80">Categorize words with custom tags for focused study sessions.</p>
              </div>
              <div class="p-4 bg-white bg-opacity-10 rounded-lg">
                  <div class="text-indigo-300 text-3xl mb-3">📱</div>
                  <h3 class="text-xl font-semibold mb-2">Accessible Anywhere</h3>
                  <p class="opacity-80">Access your vocabulary list from any device, anytime.</p>
              </div>
          </div>
      </section>

      <footer class="mt-16 text-sm opacity-75">
        &copy; {{ new Date().getFullYear() }} HanyuVocabTracker. All rights reserved.
      </footer>
    </div>
  </div>
</template>