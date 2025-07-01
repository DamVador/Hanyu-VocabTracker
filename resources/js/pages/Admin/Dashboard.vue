<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: AuthenticatedLayout });

defineProps({
    activeUsersCount: Number, // New prop for active users
    totalUsers: Number, // Example prop if you want to display total users too
    // ... any other props your admin dashboard currently receives
});
</script>

<template>
  <Head title="Admin Dashboard" />

  <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 xl:max-w-screen-xl 2xl:max-w-screen-2xl">

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 sm:p-8 md:p-10">
      <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-gray-800">
        Admin Overview
      </h2>
      <p class="text-base sm:text-lg text-gray-600 mt-2 mb-10">
        Monitor your application's health and user activity.
      </p>

      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="text-gray-900">
          <div class="flex flex-wrap gap-4">
              <Link :href="route('admin.users.index')" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-wider hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm">
                  Manage Users
              </Link>
              </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
        <div>
          <p class="text-sm sm:text-base font-medium text-gray-500">Total Registered Users</p>
          <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-blue-600">{{ totalUsers ?? 0 }}</p>
        </div>
        <span class="text-4xl sm:text-5xl text-blue-400">👤</span>
      </div>

      <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
        <div>
          <p class="text-sm sm:text-base font-medium text-gray-500">Active Users (Last 2 Weeks)</p>
          <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-green-600">{{ activeUsersCount ?? 0 }}</p>
        </div>
        <span class="text-4xl sm:text-5xl text-green-400">🟢</span>
      </div>

      <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
          <div>
            <p class="text-sm sm:text-base font-medium text-gray-500">Some Other Statistic</p>
            <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-orange-500">XXX</p>
          </div>
          <span class="text-4xl sm:text-5xl text-orange-400">📊</span>
      </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 sm:p-8">
        <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4">Admin Activity Log (Example)</h3>
        <p class="text-gray-500 text-base sm:text-lg">This is where you could display recent admin actions or other relevant data.</p>
      </div>
    </div>

  </div>
</template>