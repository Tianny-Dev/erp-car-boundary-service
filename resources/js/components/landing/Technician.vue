<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
  userTypes: { name: string; encrypted_id: string }[];
}

const { userTypes } = defineProps<Props>();

const technician = userTypes.find((t) => t.name === 'technician');
</script>


<template>
  <div
    id="technician"
    class="scroll-mt-12 bg-[url('@/assets/t12.jpg')] bg-cover bg-center bg-no-repeat px-5 py-10 text-center text-white"
  >
    <h1 class="text-4xl font-bold">WANT TO BE A TECHNICIAN?</h1>
    <p class="pt-3 text-xl font-bold">Keeping Every Ride Running Smoothly</p>
  </div>

  <div class="mx-auto w-full max-w-[1320px] px-5 py-12">
    <p class="text-center text-xl text-brand-blue">
      Technicians play a vital role in ensuring all vehicles are safe and
      operational.
    </p>
    <p class="pt-3 text-center text-xl/[23px] text-brand-blue">
      Through our platform, you can receive maintenance requests, update job
      status, and record parts <br />
      used, anytime, anywhere.
    </p>
    <p class="pt-5 text-center text-3xl font-bold text-brand-blue">BENEFITS:</p>

    <div class="grid grid-cols-12 gap-5 pt-7">
      <div class="col-span-12 lg:col-span-6">
        <div
          class="relative w-full rounded-xl border-[2px] border-[#AFC5CA] bg-[#EFF8FF] py-3 ps-8 pe-3"
        >
          <div
            class="hide absolute start-[-15px] top-1/2 grid h-[35px] w-[35px] -translate-y-1/2 place-items-center rounded-full border-3 border-white bg-brand-green shadow xl:block"
          >
            <div class="pt-[3px]">
              <i class="fa-solid fa-check text-white"></i>
            </div>
          </div>

          <p class="text-xl font-bold text-brand-blue">
            VIEW ASSIGNED SERVICE TICKETS
          </p>
        </div>
      </div>

      <div class="col-span-12 lg:col-span-6">
        <div
          class="relative w-full rounded-xl border-[2px] border-[#AFC5CA] bg-[#EFF8FF] py-3 ps-8 pe-3"
        >
          <div
            class="hide absolute start-[-15px] top-1/2 grid h-[35px] w-[35px] -translate-y-1/2 place-items-center rounded-full border-3 border-white bg-brand-green shadow xl:block"
          >
            <div class="pt-[3px]">
              <i class="fa-solid fa-check text-white"></i>
            </div>
          </div>

          <p class="text-xl font-bold text-brand-blue">
            UPDATED JOB PROGRESS IN REAL TIME
          </p>
        </div>
      </div>

      <div class="col-span-12 lg:col-span-6">
        <div
          class="relative w-full rounded-xl border-[2px] border-[#AFC5CA] bg-[#EFF8FF] py-3 ps-8 pe-3"
        >
          <div
            class="hide absolute start-[-15px] top-1/2 grid h-[35px] w-[35px] -translate-y-1/2 place-items-center rounded-full border-3 border-white bg-brand-green shadow xl:block"
          >
            <div class="pt-[3px]">
              <i class="fa-solid fa-check text-white"></i>
            </div>
          </div>

          <p class="text-xl font-bold text-brand-blue">
            LOG SPARE PARTS AND EXPENSES
          </p>
        </div>
      </div>

      <div class="col-span-12 lg:col-span-6">
        <div
          class="relative w-full rounded-xl border-[2px] border-[#AFC5CA] bg-[#EFF8FF] py-3 ps-8 pe-3"
        >
          <div
            class="hide absolute start-[-15px] top-1/2 grid h-[35px] w-[35px] -translate-y-1/2 place-items-center rounded-full border-3 border-white bg-brand-green shadow xl:block"
          >
            <div class="pt-[3px]">
              <i class="fa-solid fa-check text-white"></i>
            </div>
          </div>

          <p class="text-xl font-bold text-brand-blue">
            COMMUNICATE WITH FLEET MANAGERS EASILY
          </p>
        </div>
      </div>
    </div>

    <div class="pt-7 text-center sm:pt-10">
      <Link
          v-if="technician"
          :href="`/register/${technician.encrypted_id}`"
          class="mt-6 rounded-xl bg-brand-blue px-5 py-2 text-xl text-white sm:px-12 sm:text-3xl hover:opacity-80 transition-all"
        >
          Join as a Technician
        </Link>
    </div>
  </div>
</template>
