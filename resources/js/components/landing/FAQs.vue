<template>
  <!-- <h1 class="text-center text-3xl font-bold text-brand-blue">
    FREQUENTLY ASKED QUESTIONS (FAQ'S)
  </h1>

  <div
    class="mt-7 border-t-0 border-r-0 border-b-0 border-l-6 border-brand-blue bg-white p-4"
  >
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-bold">
        <span class="text-brand-blue">Q1:</span> What is ERP System for Car
        Boundary Service - Philippines?
      </h1>
      <button
        @click="toggle(0)"
        class="hidden rounded-md bg-brand-green px-5 py-2 whitespace-nowrap text-white transition-all duration-300 md:block"
      >
        {{ open[0] ? 'Read Less' : 'Read More' }}
      </button>
    </div>
    <transition name="slide-fade">
      <p v-if="open[0]" class="mt-2 ps-10 text-gray-800">
        It’s a digital platform that manages franchise, driver, and passenger
        data in one ERP system, designed for electric taxi and boundary
        operations.
      </p>
    </transition>

    <p class="mt-2 block text-gray-800 md:hidden">
      It’s a digital platform that manages franchise, driver, and passenger data
      in one ERP system, designed for electric taxi and boundary operations.
    </p>
  </div>

  <div
    class="mt-4 border-t-0 border-r-0 border-b-0 border-l-6 border-brand-blue bg-white p-4"
  >
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-bold">
        <span class="text-brand-blue">Q2:</span> How can I apply as a driver?
      </h1>
      <button
        @click="toggle(1)"
        class="hidden rounded-md bg-brand-green px-5 py-2 text-white transition-all duration-300 md:block"
      >
        {{ open[1] ? 'Read Less' : 'Read More' }}
      </button>
    </div>
    <transition name="slide-fade">
      <p v-if="open[1]" class="mt-2 ps-10 text-gray-800">
        Visit the Driver page, fill out the registration form, and submit your
        driver’s license and ID. Once verified, you’ll get your login details
        via email.
      </p>
    </transition>
    <p class="mt-2 block text-gray-800 md:hidden">
      Visit the Driver page, fill out the registration form, and submit your
      driver’s license and ID. Once verified, you’ll get your login details via
      email.
    </p>
  </div>

  <div
    class="mt-4 border-t-0 border-r-0 border-b-0 border-l-6 border-brand-blue bg-white p-4"
  >
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-bold">
        <span class="text-brand-blue">Q3:</span> Do you support electric
        vehicles only?
      </h1>
      <button
        @click="toggle(2)"
        class="hidden rounded-md bg-brand-green px-5 py-2 text-white transition-all duration-300 md:block"
      >
        {{ open[2] ? 'Read Less' : 'Read More' }}
      </button>
    </div>
    <transition name="slide-fade">
      <p v-if="open[2]" class="mt-2 ps-10 text-gray-800">
        Our system is optimized for e-vehicles, but traditional vehicles can
        also be registered while transitioning to green fleets.
      </p>
    </transition>
    <p class="mt-2 block text-gray-800 md:hidden">
      Our system is optimized for e-vehicles, but traditional vehicles can also
      be registered while transitioning to green fleets.
    </p>
  </div>

  <div
    class="mt-4 border-t-0 border-r-0 border-b-0 border-l-6 border-brand-blue bg-white p-4"
  >
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-bold">
        <span class="text-brand-blue">Q4:</span> Can passengers book rides
        directly?
      </h1>
      <button
        @click="toggle(3)"
        class="hidden rounded-md bg-brand-green px-5 py-2 text-white transition-all duration-300 md:block"
      >
        {{ open[3] ? 'Read Less' : 'Read More' }}
      </button>
    </div>
    <transition name="slide-fade">
      <p v-if="open[3]" class="mt-2 ps-10 text-gray-800">
        Yes, passengers can book rides using the mobile app or the booking form
        on our website.
      </p>
    </transition>
    <p class="mt-2 block text-gray-800 md:hidden">
      Yes, passengers can book rides using the mobile app or the booking form on
      our website.
    </p>
  </div>

  <div
    class="mt-4 border-t-0 border-r-0 border-b-0 border-l-6 border-brand-blue bg-white p-4"
  >
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-bold">
        <span class="text-brand-blue">Q5:</span> Is my data secure?
      </h1>
      <button
        @click="toggle(4)"
        class="hidden rounded-md bg-brand-green px-5 py-2 text-white transition-all duration-300 md:block"
      >
        {{ open[4] ? 'Read Less' : 'Read More' }}
      </button>
    </div>
    <transition name="slide-fade">
      <p v-if="open[4]" class="mt-2 ps-10 text-gray-800">
        Absolutely. We comply with the Philippine Data Privacy Act and all your
        information is encrypted.
      </p>
    </transition>
    <p class="mt-2 block text-gray-800 md:hidden">
      Absolutely. We comply with the Philippine Data Privacy Act and all your
      information is encrypted.
    </p>
  </div> -->
</template>
