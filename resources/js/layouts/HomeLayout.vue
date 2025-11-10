<script setup lang="ts">
import { login, logout, selectUserType } from '@/routes';
import { Link, router } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

// -------------------- Reactive State --------------------
const isScrolled = ref(false);
const activeSection = ref<string>('home');
const observers = ref<IntersectionObserver[]>([]);
const isMenuOpen = ref(false);

// -------------------- Section IDs --------------------
const sectionIds = [
  'about',
  'works',
  'franchise',
  'driver',
  'technician',
  'passenger',
  'testi',
  'faq',
  'Terms',
  'contact',
] as const;

// -------------------- Helpers --------------------
const getScrollMargin = (el: HTMLElement): number => {
  const style = window.getComputedStyle(el);
  const scrollMarginTop = parseInt(style.scrollMarginTop || '0', 10);
  return isNaN(scrollMarginTop) ? 0 : scrollMarginTop;
};

let currentSection = '';

// -------------------- Intersection Observer --------------------
const handleIntersection: IntersectionObserverCallback = (entries) => {
  entries.forEach((entry) => {
    const target = entry.target as HTMLElement;
    const id = target.id;

    if (entry.isIntersecting && entry.intersectionRatio > 0) {
      currentSection = id;
      activeSection.value = id;
    }

    if (
      !entry.isIntersecting &&
      entry.boundingClientRect.top > 0 &&
      currentSection === id
    ) {
      const currentIndex = sectionIds.indexOf(
        id as (typeof sectionIds)[number],
      );
      if (currentIndex > 0) {
        activeSection.value = sectionIds[currentIndex - 1];
      } else {
        activeSection.value = 'home';
      }
    }
  });

  const firstSection = document.getElementById(sectionIds[0]);
  if (firstSection && window.scrollY < firstSection.offsetTop - 100) {
    activeSection.value = 'home';
  }
};

// -------------------- Lifecycle Hooks --------------------
let handleHeaderScroll: () => void;

onMounted(() => {
  handleHeaderScroll = () => {
    isScrolled.value = window.scrollY > 50;
  };
  window.addEventListener('scroll', handleHeaderScroll);

  // Create observer for each section using its scroll margin
  sectionIds.forEach((id) => {
    const target = document.getElementById(id);
    if (!target) return;

    const marginTop = getScrollMargin(target);
    const rootMargin = `-${marginTop}px 0px -${window.innerHeight - marginTop - 10}px 0px`;

    const observer = new IntersectionObserver(handleIntersection, {
      root: null,
      rootMargin,
      threshold: [0, 0.25, 0.5, 0.75, 1],
    });

    observer.observe(target);
    observers.value.push(observer);
  });
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleHeaderScroll);
  observers.value.forEach((obs) => obs.disconnect());
});

// -------------------- Click Handler --------------------
const handleClick = (id: string) => {
  activeSection.value = id;
  isMenuOpen.value = false;
};

const handleLogout = () => {
  router.flushAll();
};
</script>

<template>
  <div>
    <!-- Top Banner -->
    <div
      class="mx-auto flex w-full max-w-[1320px] flex-wrap items-center justify-center gap-3 px-3 py-3 text-center md:px-5 lg:justify-between lg:gap-2 lg:px-4 xl:gap-3 xl:px-8"
    >
      <div class="hidden md:block">
        <p class="text-md text-brand-blue md:text-lg">
          The Future of Boundary Services Starts Here. Manage, Drive, and Ride.
          All in One System.
        </p>
      </div>

      <div class="flex w-full gap-3 md:w-auto">
        <button
          class="flex-1 rounded-md bg-brand-blue px-7 py-2 whitespace-nowrap text-white lg:px-5 xl:px-7"
        >
          Download App
        </button>
        <template v-if="!$page.props.auth.user">
          <Link
            :href="login()"
            class="flex-1 rounded-md bg-brand-green px-7 py-2 whitespace-nowrap text-white lg:px-5 xl:px-7"
          >
            Login
          </Link>
          <Link
            :href="selectUserType()"
            class="flex-1 rounded-md bg-brand-blue px-7 py-2 whitespace-nowrap text-white lg:px-5 xl:px-7"
          >
            Register
          </Link>
        </template>

        <template v-else>
          <Link
            class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-md bg-red-500 px-7 py-2 text-white hover:opacity-85"
            :href="logout()"
            @click.prevent="handleLogout"
            as="button"
            data-test="logout-button"
          >
            <LogOut class="h-4 w-4" />
            Log out
          </Link>
        </template>
      </div>
    </div>

    <!-- Header Navigation -->
    <div>
      <header
        :class="[
          'fixed left-1/2 z-50 flex w-[calc(100%-40px)] max-w-[1320px] -translate-x-1/2 transform items-center justify-between rounded-md bg-white px-4 py-2 text-[11px] text-brand-blue shadow-brand-shadow transition-all duration-300 xl:text-[16px]',
          isScrolled ? 'top-4' : 'top-22 md:top-32 lg:top-22',
        ]"
      >
        <!-- Logo -->
        <div>
          <button class="border-2 border-brand-blue px-4">Logo</button>
        </div>

        <!-- Burger Button (Mobile Only) -->
        <button
          class="rounded border border-brand-blue p-2 text-brand-blue focus:outline-none lg:hidden"
          @click="isMenuOpen = !isMenuOpen"
        >
          <svg
            v-if="!isMenuOpen"
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>

          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>

        <!-- Navigation Links -->
        <nav
          :class="[
            'absolute top-full left-0 flex w-full flex-col gap-2 overflow-hidden rounded-b-md bg-white shadow-md transition-all duration-300 lg:static lg:top-auto lg:w-auto lg:flex-row lg:gap-3 lg:bg-transparent lg:shadow-none',
            isMenuOpen
              ? 'max-h-[600px] px-4 py-3 opacity-100'
              : 'max-h-0 opacity-0 lg:max-h-none lg:opacity-100',
          ]"
        >
          <!-- Home Link -->
          <a
            href="#"
            @click="handleClick('home')"
            :class="[
              'rounded-md px-2 py-1 transition-colors duration-300',
              activeSection === 'home'
                ? 'bg-brand-blue text-white'
                : 'hover:bg-brand-blue hover:text-white',
            ]"
          >
            Home
          </a>

          <!-- Dynamic Section Links -->
          <a
            v-for="id in sectionIds"
            :key="id"
            :href="'#' + id"
            @click="handleClick(id)"
            :class="[
              'rounded-md px-2 py-1 transition-colors duration-300',
              activeSection === id
                ? 'bg-brand-blue text-white'
                : 'hover:bg-brand-blue hover:text-white',
            ]"
          >
            {{
              id === 'about'
                ? 'About Us'
                : id === 'works'
                  ? 'How it Works?'
                  : id === 'franchise'
                    ? 'Franchise'
                    : id === 'driver'
                      ? 'Driver'
                      : id === 'technician'
                        ? 'Technician'
                        : id === 'passenger'
                          ? 'Passenger'
                          : id === 'testi'
                            ? 'Testimonials'
                            : id === 'faq'
                              ? "FAQ's"
                              : id === 'Terms'
                                ? 'Terms & Privacy'
                                : id === 'contact'
                                  ? 'Contact Us'
                                  : id
            }}
          </a>
        </nav>
      </header>
    </div>

    <main>
      <slot />
    </main>

    <!-- Start Footer -->
    <div class="bg-brand-blue py-8">
      <div class="mx-auto w-full max-w-[1320px] px-3 sm:px-5">
        <div class="grid grid-cols-12 gap-7">
          <div class="col-span-12 pe-8 sm:pe-0 lg:col-span-6">
            <div class="grid items-center gap-5 sm:flex">
              <div
                class="grid h-[70px] w-[150px] place-items-center border-1 border-black bg-white text-2xl text-brand-blue"
              >
                LOGO
              </div>
              <div>
                <h1 class="text-2xl font-bold text-white">
                  ERP SYSTEM FOR CAR BOUNDARY SERVICE - PHILIPPINES
                </h1>
              </div>
            </div>
            <p class="pt-3 text-xl text-white">
              Empowering Every Ride. Connecting Drivers, Franchisees, and
              Passengers Through Smart Mobility.
            </p>

            <div class="pt-4">
              <div class="dm:gap-4 flex gap-3">
                <img
                  src="@/assets/fb.png"
                  class="h-[48px] rounded-full border-2 border-white"
                  alt=""
                />
                <img
                  src="@/assets/yt.png"
                  class="h-[48px] rounded-full border-2 border-white"
                  alt=""
                />
                <img
                  src="@/assets/instagram.png"
                  class="h-[48px] rounded-full border-2 border-white"
                  alt=""
                />
                <img
                  src="@/assets/x.png"
                  class="h-[48px] rounded-full border-2 border-white"
                  alt=""
                />
                <img
                  src="@/assets/tiktok.png"
                  class="h-[48px] rounded-full border-2 border-white"
                  alt=""
                />
              </div>
            </div>
          </div>

          <div class="col-span-12 lg:col-span-6 lg:px-10">
            <h3 class="text-xl font-bold text-white">Quick Links</h3>
            <div class="grid grid-cols-12 gap-4 pt-3">
              <div class="col-span-6 sm:col-span-4">
                <a
                  href="#"
                  class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                >
                  Home
                </a>

                <br />
                <a
                  href="#about"
                  class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                >
                  About Us</a
                >
                <br />
                <a
                  href="#works"
                  class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                >
                  How It Works</a
                >

                <br />
                <a
                  href="#franchise"
                  class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                >
                  Franchise</a
                >
              </div>

              <div class="col-span-6 sm:col-span-4">
                <a
                  href="#driver"
                  class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                >
                  Driver</a
                >
                <br />

                <a
                  href="#technician"
                  class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                >
                  Technician</a
                >
                <br />

                <a
                  href="#passenger"
                  class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                >
                  Passenger</a
                >
                <br />

                <a
                  href="#testi"
                  class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                >
                  Testimonials</a
                >
              </div>

              <div class="col-span-12 sm:col-span-4">
                <a
                  href="#faq"
                  class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                >
                  FAQ's</a
                >
                <br />
                <a
                  href="#Terms"
                  class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                >
                  Terms & Privacy</a
                >
                <br />
                <a
                  href="#contact"
                  class="hover:text-brand-yellow after:bg-brand-yellow relative text-lg text-white transition duration-300 ease-in-out after:absolute after:bottom-0 after:left-0 after:h-[2px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:w-full"
                >
                  Contact Us</a
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Start Footer -->

    <!-- Start All Rights Reserved -->
    <div class="bg-white px-5 py-3 text-center text-lg/[25px] text-brand-blue">
      <p>
        © 2025 ERP System for Car Boundary Service - Philippines <br />
        "Smart Mobility for Every Filipino."
      </p>
    </div>
    <!-- End All Rights Reserved -->
  </div>
</template>
