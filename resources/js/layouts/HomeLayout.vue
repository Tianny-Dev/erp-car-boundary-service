<script setup lang="ts">
import Footer from '@/components/landing/Footer.vue';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useNavbar } from '@/composables/navbar';
import { dashboard, login, logout, selectUserType } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { Menu } from 'lucide-vue-next';

const { isScrolled, activeSection, isMenuOpen, sectionIds, handleClick } =
  useNavbar();
</script>

<template>
  <div>
    <!-- Top Banner Start -->
    <div
      class="mx-auto flex w-full max-w-[1320px] flex-wrap items-center justify-center gap-2 px-3 py-3 text-center md:px-5 lg:justify-between lg:gap-2 lg:px-4 xl:gap-3 xl:px-8"
    >
      <div class="hidden md:block">
        <p class="text-md text-brand-blue xl:text-lg">
          The Future of Boundary Services Starts Here. Manage, Drive, and Ride.
          All in One System.
        </p>
      </div>

      <div class="flex w-full gap-2 lg:w-auto xl:gap-3">
        <button
          class="flex-1 rounded-md bg-brand-blue px-2 py-2 whitespace-nowrap text-white lg:px-4 xl:px-7"
        >
          Download App
        </button>
        <DropdownMenu v-if="$page.props.auth.user">
          <DropdownMenuTrigger as-child>
            <button
              class="cursor-pointer rounded-md bg-brand-green p-2 text-white transition-all hover:opacity-85"
            >
              <Menu class="h-5 w-7" />
            </button>
          </DropdownMenuTrigger>

          <DropdownMenuContent class="w-40">
            <DropdownMenuItem>
              <Link :href="dashboard()" class="block w-full">Dashboard</Link>
            </DropdownMenuItem>

            <DropdownMenuItem>
              <Link
                :href="logout()"
                method="post"
                as="button"
                class="w-full text-left"
              >
                Logout
              </Link>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <template v-else>
          <Link
            :href="login()"
            class="flex-1 rounded-md bg-brand-green py-2 whitespace-nowrap text-white transition-all hover:opacity-85 lg:px-5 xl:px-7"
          >
            Login
          </Link>
          <Link
            :href="selectUserType()"
            class="flex-1 rounded-md bg-brand-blue py-2 whitespace-nowrap text-white transition-all hover:opacity-85 lg:px-4 xl:px-7"
          >
            Register
          </Link>
        </template>
      </div>
    </div>
    <!-- Top Banner End -->

    <!-- Header Navigation Start -->
    <div>
      <header
        :class="[
          'fixed left-1/2 z-50 flex w-[calc(100%-40px)] max-w-[1320px] -translate-x-1/2 transform items-center justify-between rounded-md bg-white px-4 py-2 text-[11px] text-brand-blue shadow-brand-shadow transition-all duration-300 xl:text-[16px]',
          isScrolled ? 'top-4' : 'top-22 md:top-32 lg:top-22',
        ]"
      >
        <!-- Logo -->
        <div>
          <img src="@/assets/navlogo.png" class="h-4 w-auto" alt="" />
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
    <!-- Header Navigation End -->

    <!-- Main Start -->
    <main>
      <slot />
    </main>
    <!-- Main End -->

    <!-- Footer Start -->
    <Footer />
    <!-- Footer End -->
  </div>
</template>
