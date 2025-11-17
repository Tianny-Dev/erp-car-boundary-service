<script setup lang="ts">
import HomeLayout from '@/layouts/HomeLayout.vue';
import { Head } from '@inertiajs/vue3';

// landing page import
import About from '@/components/landing/About.vue';
import Banner from '@/components/landing/Banner.vue';
import Contact from '@/components/landing/Contact.vue';
import Driver from '@/components/landing/Driver.vue';
import FAQs from '@/components/landing/FAQs.vue';
import Franchise from '@/components/landing/Franchise.vue';
import Join from '@/components/landing/Join.vue';
import Passenger from '@/components/landing/Passenger.vue';
import Technician from '@/components/landing/Technician.vue';
import Terms from '@/components/landing/Terms.vue';
import Testimonials from '@/components/landing/Testimonials.vue';
import Works from '@/components/landing/Works.vue';
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

    <!-- Franchise Start -->
    <Franchise />
    <!-- Franchise End -->

    <!-- Driver Start -->
    <Driver />
    <!-- Driver End -->

    <!-- Technician Start -->
    <Technician />
    <!-- Technician End -->

    <!-- Passenger Start -->
    <Passenger />
    <!-- Passenger End -->

    <!-- Testimonials Start -->
    <Testimonials />
    <!-- Testimonials End -->

    <!-- Join Start -->
    <Join />
    <!-- Join End -->

    <!-- FAQs Start -->
    <FAQs />
    <!-- FAQs End -->

    <!-- Terms Start -->
    <Terms />
    <!-- Terms End -->

    <!-- Contact Start -->
    <Contact />
    <!-- Contact End-->
  </HomeLayout>
</template>
