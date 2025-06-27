<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: AuthenticatedLayout });

defineProps({
    totalUsers: Number,
    totalWordsInSystem: Number,
    // Add any other props passed from AdminDashboardController
    // total active users --> All user with last connection before 1 month
});
</script>

<template>
  <Head title="Admin Dashboard" />

  <div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 xl:max-w-screen-xl 2xl:max-w-screen-2xl">

      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 sm:p-8 md:p-10">
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-gray-800">
          Admin Panel
        </h2>
        <p class="text-base sm:text-lg text-gray-600 mt-2">
          Overview of your VocabTracker application.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
          <div>
            <p class="text-sm sm:text-base font-medium text-gray-500">Total Registered Users</p>
            <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-blue-600">{{ totalUsers }}</p>
          </div>
          <span class="text-4xl sm:text-5xl text-blue-400">👥</span>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
          <div>
            <p class="text-sm sm:text-base font-medium text-gray-500">Total Words in System</p>
            <p class="text-3xl sm:text-4xl md:text-5xl font-bold text-green-600">{{ totalWordsInSystem }}</p>
          </div>
          <span class="text-4xl sm:text-5xl text-green-400">📖</span>
        </div>

        <!-- Example of another admin-specific card -->
        <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm flex items-center justify-between">
            <div>
              <p class="text-sm sm:text-base font-medium text-gray-500">Manage Users</p>
              <Link :href="route('admin.users.index')" class="mt-2 inline-flex items-center px-4 py-2 bg-purple-600 text-white font-semibold rounded-md shadow-md hover:bg-purple-700 transition">
                Go to User Management
              </Link>
            </div>
            <span class="text-4xl sm:text-5xl text-purple-400">⚙️</span>
        </div>
      </div>

      <!-- You can add more sections here, e.g., recent activity logs, charts, etc. -->

    </div>
  </div>
</template>