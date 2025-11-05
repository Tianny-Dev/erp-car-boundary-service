<script setup lang="ts">
import HomeLayout from '@/layouts/HomeLayout.vue';
import { Head } from '@inertiajs/vue3';
import { nextTick, onMounted, ref } from 'vue';

// landing page import
import About from '@/components/landing/About.vue';
import Banner from '@/components/landing/Banner.vue';
import Contact from '@/components/landing/Contact.vue';
import Driver from '@/components/landing/Driver.vue';
import Franchise from '@/components/landing/Franchise.vue';
import Join from '@/components/landing/Join.vue';
import Passenger from '@/components/landing/Passenger.vue';
import Technician from '@/components/landing/Technician.vue';
import Testimonials from '@/components/landing/Testimonials.vue';
import Works from '@/components/landing/Works.vue';

declare const $: any;

onMounted(async () => {
  await nextTick();

  const $carousel = $('.owl-1');

  if ($carousel.length > 0) {
    const owl1 = $carousel.owlCarousel({
      center: false,
      items: 1,
      loop: true,
      stagePadding: 0,
      margin: 20,
      smartSpeed: 1000,
      autoplay: false,
      nav: false,
      dots: false,
      pauseOnHover: false,
      responsive: {
        850: {
          margin: 20,
          nav: false,
          items: 1,
        },
        1000: {
          margin: 20,
          stagePadding: 0,
          nav: false,
          items: 1,
        },
        1280: {
          margin: 20,
          stagePadding: 0,
          nav: false,
          items: 2,
        },
      },
    });

    // Custom navigation buttons
    $('.bg-left-half123').on('click', () => {
      owl1.trigger('prev.owl.carousel', [1000]);
    });

    $('.bg-right-half').on('click', () => {
      owl1.trigger('next.owl.carousel', [1000]);
    });
  }
});

// -------------------- FAQ Toggle Logic --------------------
const open = ref<boolean[]>([false, false, false, false, false]);

const toggle = (index: number) => {
  open.value[index] = !open.value[index];
};
</script>

<template>
  <Head title=" | Home" />

  <HomeLayout>
    <!-- Banner Start -->
    <Banner />
    <!-- Banner End -->

    <!-- About Us Start -->
    <About />
    <!-- About Us End -->

    <!-- How It Works Start -->
    <Works />
    <!-- How It Works End -->

    <!-- How It Franchise Start -->
    <Franchise />
    <!-- How It Franchise End -->

    <!-- How It Driver Start -->
    <Driver />
    <!-- How It Driver End -->

    <!-- How It Technician Start -->
    <Technician />
    <!-- How It Technician End -->

    <!-- How It Passenger Start -->
    <Passenger />
    <!-- How It Passenger End -->

    <!-- How It Testimonials Start -->
    <Testimonials />
    <!-- How It Testimonials End -->

    <!-- Join Start -->
    <Join />
    <!-- Join End -->

    <div id="faq" class="scroll-mt-12 bg-[#e3f0fa] px-5">
      <div class="mx-auto w-full max-w-[1320px] py-10">
        <h1 class="text-center text-3xl font-bold text-brand-blue">
          FREQUENTLY ASKED QUESTIONS (FAQ'S)
        </h1>

        <div
          class="mt-7 border-t-0 border-r-0 border-b-0 border-l-6 border-brand-blue bg-white p-4"
        >
          <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">
              <span class="text-brand-blue">Q1:</span> What is ERP System for
              Car Boundary Service - Philippines?
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
              It’s a digital platform that manages franchise, driver, and
              passenger data in one ERP system, designed for electric taxi and
              boundary operations.
            </p>
          </transition>

          <p class="mt-2 block text-gray-800 md:hidden">
            It’s a digital platform that manages franchise, driver, and
            passenger data in one ERP system, designed for electric taxi and
            boundary operations.
          </p>
        </div>

        <div
          class="mt-4 border-t-0 border-r-0 border-b-0 border-l-6 border-brand-blue bg-white p-4"
        >
          <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">
              <span class="text-brand-blue">Q2:</span> How can I apply as a
              driver?
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
              Visit the Driver page, fill out the registration form, and submit
              your driver’s license and ID. Once verified, you’ll get your login
              details via email.
            </p>
          </transition>
          <p class="mt-2 block text-gray-800 md:hidden">
            Visit the Driver page, fill out the registration form, and submit
            your driver’s license and ID. Once verified, you’ll get your login
            details via email.
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
              Our system is optimized for e-vehicles, but traditional vehicles
              can also be registered while transitioning to green fleets.
            </p>
          </transition>
          <p class="mt-2 block text-gray-800 md:hidden">
            Our system is optimized for e-vehicles, but traditional vehicles can
            also be registered while transitioning to green fleets.
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
              Yes, passengers can book rides using the mobile app or the booking
              form on our website.
            </p>
          </transition>
          <p class="mt-2 block text-gray-800 md:hidden">
            Yes, passengers can book rides using the mobile app or the booking
            form on our website.
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
              Absolutely. We comply with the Philippine Data Privacy Act and all
              your information is encrypted.
            </p>
          </transition>
          <p class="mt-2 block text-gray-800 md:hidden">
            Absolutely. We comply with the Philippine Data Privacy Act and all
            your information is encrypted.
          </p>
        </div>

        <h1
          id="Terms"
          class="scroll-mt-10 pt-12 text-center text-3xl font-bold text-brand-blue"
        >
          TERMS & PRIVACY
        </h1>

        <div class="mt-7 bg-white px-3 py-6 text-center">
          <p>
            <strong>ERP System for Car Boundary Service - Philippines</strong>
            collects only the information necessary to provide transportation
            and account services
          </p>

          <p class="pt-4">
            We ad here to the
            <strong>Data Privacy Act of 2012 (RA 10173)</strong> and do not
            share your data with third parties without consent.
          </p>
        </div>

        <div
          class="mt-4 border-t-0 border-r-0 border-b-0 border-l-6 border-brand-blue bg-white p-4"
        >
          <h1 class="text-xl font-bold">
            Users must verify their identity before full account access.
          </h1>
        </div>

        <div
          class="mt-4 border-t-0 border-r-0 border-b-0 border-l-6 border-brand-blue bg-white p-4"
        >
          <h1 class="text-xl font-bold">
            All payments are processed securely through authorized gateways
            (e.g., GCash, PayMaya).
          </h1>
        </div>

        <div
          class="mt-4 border-t-0 border-r-0 border-b-0 border-l-6 border-brand-blue bg-white p-4"
        >
          <h1 class="text-xl font-bold">
            By register, users agree to abide by platform rules and community
            guidelines.
          </h1>
        </div>

        <div
          class="mt-4 border-t-0 border-r-0 border-b-0 border-l-6 border-brand-blue bg-white p-4"
        >
          <h1 class="text-xl font-bold">
            You may request deletion of your data anytime by emailing
            privacy@carboundary.ph.
          </h1>
        </div>
      </div>
    </div>

    <Contact />
  </HomeLayout>
</template>

<style scoped>
/* Smooth slide transition */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.4s ease;
  overflow: hidden;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-5px);
}
.slide-fade-enter-to,
.slide-fade-leave-from {
  opacity: 1;
  max-height: 200px;
  transform: translateY(0);
}
</style>
