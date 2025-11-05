<script setup lang="ts">
import { login } from '@/routes';
import { Link } from '@inertiajs/vue3';
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
          class="flex-1 rounded-md bg-brand-blue px-7 py-2 whitespace-nowrap text-white transition-opacity hover:opacity-70 lg:px-5 xl:px-7"
        >
          Download App
        </button>
        <Link
          :href="login()"
          class="flex-1 rounded-md bg-brand-green px-7 py-2 whitespace-nowrap text-white transition-opacity hover:opacity-70 lg:px-5 xl:px-7"
        >
          Login
        </Link>
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
  </div>
</template>
