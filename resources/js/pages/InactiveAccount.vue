<script setup lang="ts">
import { home, logout } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Account Inactive" />

  <div
    class="flex h-screen w-full items-center justify-center bg-[#EFF8FF] p-2 sm:p-6"
  >
    <div class="relative z-0 w-full max-w-md">
      <div
        class="absolute top-[-60px] left-1/2 z-[-1] h-[125px] w-[125px] -translate-x-1/2 rounded-full bg-white shadow-brand-shadow"
      ></div>

      <div>
        <i
          class="fa-solid fa-clock absolute top-[-45px] left-1/2 -translate-x-1/2 text-[90px] text-auth-blue"
        ></i>
      </div>

      <div
        class="absolute left-1/2 z-[-2] h-full w-full -translate-x-1/2 rounded-2xl bg-white shadow-brand-shadow"
      ></div>

      <div class="rounded-2xl bg-white p-3 text-center sm:p-8">
        <div class="h-14 sm:h-1"></div>

        <h1
          class="mb-4 rounded-md bg-auth-blue px-1 py-2 text-xl text-white sm:mt-9 sm:px-2 sm:text-2xl"
        >
          Your Account Is Pending Approval
        </h1>
        <p class="text-md mb-6 text-justify">
          Thank you for registering. Your account is not active yet because we
          are reviewing the information you provided. Once your account is
          approved, you will receive an email or text notification. Please wait
          patiently while we complete the verification process.
        </p>

        <div class="flex gap-3">
          <Link
            :href="home()"
            class="block w-full rounded-lg bg-auth-blue px-4 py-2 text-center font-semibold text-white transition hover:bg-brand-blue"
          >
            Home
          </Link>
          <Link
            :href="logout()"
            class="block w-full rounded-lg bg-[#CE2D3C] px-4 py-2 text-center font-semibold text-white transition hover:bg-red-700"
          >
            Logout
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>
