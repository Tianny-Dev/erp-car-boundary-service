<script setup lang="ts">
import { usePageTheme } from '@/composables/usePageTheme';
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

// 1. Define the data structure
interface FranchiseData {
  id: number;
  name: string;
  region: string;
  province: string;
  city: string;
}

interface UserTypes {
  name: string;
  encrypted_id: string;
}

interface UserTypeFeedback {
  id: number;
  name: string;
}

interface Feedback {
  id: number;
  name: string;
  avatar?: string | null;
  rating: number;
  description: string;
  user_type: UserTypeFeedback | null;
  created_at: string;
}

// 2. Define a SINGLE Props interface
interface Props {
  userTypes: UserTypes[];
  franchises: FranchiseData[];
  feedbacks: Feedback[];
}

// 3. Destructure BOTH variables so they are available to your template
const { userTypes, franchises, feedbacks } = defineProps<Props>();

// Force light mode
usePageTheme('light');
</script>

<template>
  <Head title=" | Home" />

  <HomeLayout>
    <!-- Banner Start -->
    <Banner :userTypes="userTypes" />
    <!-- Banner End -->

    <!-- About Us Start -->
    <About />
    <!-- About Us End -->

    <!-- How It Works Start -->
    <Works />
    <!-- How It Works End -->

    <!-- Franchise Start -->
    <Franchise :userTypes="userTypes" :franchises="franchises" />
    <!-- Franchise End -->

    <!-- Driver Start -->
    <Driver :userTypes="userTypes" />
    <!-- Driver End -->

    <!-- Technician Start -->
    <Technician :userTypes="userTypes" />
    <!-- Technician End -->

    <!-- Passenger Start -->
    <Passenger />
    <!-- Passenger End -->

    <!-- Testimonials Start -->
    <Testimonials :feedbacks="feedbacks" :userTypes="userTypes" />
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
