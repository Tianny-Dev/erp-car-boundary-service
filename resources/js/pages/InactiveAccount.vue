<script setup lang="ts">
import { usePageTheme } from '@/composables/usePageTheme';
import { logout } from '@/routes';
import { AppPageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Force light mode on this page only
usePageTheme('light');

const page = usePage<AppPageProps>();

// Determine if the user is a passenger to show specific app instructions
const isPassenger = computed(
  () => page.props.auth.user?.role_name === 'passenger',
);

const apkLink = 'https://bb88group.com/erpfranchisingmgmt/APK/DDGNS-ERP.apk';
</script>

<template>
  <Head title="Success!" />

  <div
    class="flex h-screen w-full items-center justify-center bg-[#EFF8FF] p-2 sm:p-6"
  >
    <div class="relative z-0 w-full max-w-md">
      <div
        class="absolute top-[-60px] left-1/2 z-0 h-[125px] w-[125px] -translate-x-1/2 animate-ping rounded-full bg-white shadow-brand-shadow"
      ></div>

      <div>
        <i
          class="fa-solid fa-circle-check absolute top-[-45px] left-1/2 z-10 -translate-x-1/2 animate-bounce text-[90px] text-green-500"
        ></i>
      </div>

      <div
        class="absolute left-1/2 z-0 h-full w-full -translate-x-1/2 rounded-2xl shadow-brand-shadow"
      ></div>

      <div class="animate-fadeIn relative rounded-2xl p-3 text-center sm:p-8">
        <div class="h-14 sm:h-1"></div>

        <h1
          class="animate-slideDown mb-4 rounded-md bg-green-500 px-1 py-2 text-xl text-white sm:mt-4 sm:px-2 sm:text-2xl"
        >
          Success!
        </h1>

        <div v-if="isPassenger">
          <p class="text-md animate-fadeIn mb-2 font-semibold text-gray-800">
            Download App to Verify
          </p>

          <p class="text-md animate-fadeIn mb-6 px-2 text-sm text-gray-600">
            To fully activate your account, please download the app below. Once
            logged in, you can **verify your account** by requesting and
            entering an **OTP verification code** sent to your mobile number.
          </p>

          <div class="animate-fadeIn flex flex-col gap-3">
            <a
              :href="apkLink"
              class="flex w-full items-center justify-center gap-2 rounded-lg bg-green-500 px-4 py-3 text-center font-bold text-white shadow-md transition hover:bg-green-600"
            >
              <i class="fa-solid fa-download"></i>
              Download App
            </a>

            <div class="flex gap-3">
              <Link
                :href="logout()"
                class="block w-full rounded-lg bg-green-500 px-4 py-2 text-center font-semibold text-white transition hover:bg-green-600"
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

        <div v-else>
          <p class="text-md animate-fadeIn mb-6 py-2 text-center text-gray-600">
            Thank you for registering! We're reviewing your information and will
            notify you by email or text once your account is approved. We
            appreciate your patience!
          </p>

          <div class="animate-fadeIn flex gap-3">
            <Link
              :href="logout()"
              class="block w-full rounded-lg bg-green-500 px-4 py-2 text-center font-semibold text-white transition hover:bg-green-600"
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
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
@keyframes slideDown {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
.animate-fadeIn {
  animation: fadeIn 0.8s ease forwards;
}
.animate-slideDown {
  animation: slideDown 0.6s ease forwards;
}
</style>
