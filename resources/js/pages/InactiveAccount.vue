<script setup lang="ts">
import { home, logout } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Account Inactive" />

  <div class="flex h-screen w-full items-center justify-center bg-gray-50 p-6">
    <div
      class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-xl"
    >
      <h1 class="mb-4 text-3xl font-extrabold text-red-600">
        Account Inactive
      </h1>
      <p class="mb-6 text-base text-gray-600">
        Your account is not active. Please contact support or try again later.
      </p>
      <div class="flex flex-col gap-3">
        <Link
          :href="home()"
          class="block w-full rounded-lg bg-blue-600 px-4 py-2 text-center font-semibold text-white transition hover:bg-blue-700"
        >
          Go to Home
        </Link>
        <Link
          :href="logout()"
          class="block w-full rounded-lg bg-red-600 px-4 py-2 text-center font-semibold text-white transition hover:bg-red-700"
        >
          Logout
        </Link>
      </div>
    </div>
  </div>
</template>
