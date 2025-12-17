<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { home, login } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// --- ASSETS ---
import driverBg from '@/assets/auth/logindriver.jpg';
import franchiseBg from '@/assets/auth/loginfranchise.jpg';
import defaultBg from '@/assets/auth/loginNewBG.jpg';
import passengerBg from '@/assets/auth/loginpassenger.jpg';
import technicianBg from '@/assets/auth/logintech.jpg';
import logo from '@/assets/loginlogo.png';

// --- MAP IMPORTS ---
import LeafletMap, {
  type MarkerData,
} from '@/components/FranchisingLocation.vue';

interface FranchiseData {
  id: number;
  name: string;
  region: string;
  province: string;
  city: string;
  latitude: string;
  longitude: string;
}

// --- CONSOLIDATED PROPS ---
const props = defineProps<{
  userTypes?: any[];
  franchises?: FranchiseData[]; // Made optional to prevent crashes if undefined
  userTypeName: string;
  textOverlay?: string;
  title?: string;
  description?: string;
  titleRegistration?: string;
  goBackHomeButton?: boolean;
}>();

const isOpen = ref(false);
const selectedFranchise = ref<FranchiseData | null>(null);

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const selectLocation = (franchise: FranchiseData | null) => {
  selectedFranchise.value = franchise;
  isOpen.value = false;
};

const selectedButtonText = computed(() => {
  if (!selectedFranchise.value) return 'All Locations';
  return `${selectedFranchise.value.city}, ${selectedFranchise.value.province}`;
});

const parseToFloat = (val: any) => parseFloat(val) || 0;

// 🚀 MAP DATA LOGIC
const mapLocations = computed<MarkerData[]>(() => {
  // If a specific franchise is selected from dropdown
  if (selectedFranchise.value) {
    return [
      {
        id: selectedFranchise.value.id,
        latitude: parseToFloat(selectedFranchise.value.latitude),
        longitude: parseToFloat(selectedFranchise.value.longitude),
        name: selectedFranchise.value.name,
        city: selectedFranchise.value.city,
        type: 'End',
      },
    ];
  }

  // If no franchises data at all
  if (!props.franchises || props.franchises.length === 0) return [];

  // Show all markers
  return props.franchises.map((f) => ({
    id: f.id,
    latitude: parseToFloat(f.latitude),
    longitude: parseToFloat(f.longitude),
    name: f.name,
    city: f.city,
    type: 'End',
  }));
});

// --- BACKGROUND LOGIC ---
const userBackgrounds: Record<string, string> = {
  driver: driverBg,
  passenger: passengerBg,
  technician: technicianBg,
  franchise: franchiseBg,
};

const backgroundImage = computed(() => {
  return userBackgrounds[props.userTypeName] || defaultBg;
});
</script>

<template>
  <div
    class="flex min-h-svh flex-col items-center justify-center bg-muted bg-[url(@/assets/auth/loginbg.jpg)] bg-cover bg-center bg-no-repeat p-1.5 sm:p-6 md:p-10"
  >
    <div class="w-full max-w-sm md:max-w-3xl">
      <div class="flex flex-col gap-6">
        <Card class="overflow-hidden border p-0">
          <CardContent class="grid p-0 md:grid-cols-2">
            <div class="relative hidden bg-muted md:block">
              <div
                class="absolute inset-0 dark:brightness-[0.2] dark:grayscale"
                :style="{
                  backgroundImage: `url(${backgroundImage})`,
                  backgroundSize: 'cover',
                  backgroundPosition: 'center',
                  backgroundRepeat: 'no-repeat',
                }"
              ></div>

              <div class="absolute inset-0 bg-black/40"></div>

              <div
                class="relative z-10 flex h-full w-full flex-col items-center justify-between text-center"
              >
                <h1
                  v-if="textOverlay"
                  class="mt-auto mb-auto px-6 py-10 text-2xl leading-relaxed font-bold text-white drop-shadow-lg"
                >
                  {{ textOverlay }}
                </h1>

                <div v-else class="relative flex h-full w-full flex-col">
                  <button
                    @click="toggleDropdown"
                    type="button"
                    class="absolute end-5 top-5 z-[1000] flex items-center gap-2 rounded-xl bg-brand-green px-6 py-2.5 text-white shadow-lg transition hover:scale-105 active:scale-95"
                  >
                    <span class="font-semibold">{{ selectedButtonText }}</span>
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-4 w-4"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                      />
                    </svg>
                  </button>

                  <div
                    v-if="isOpen"
                    class="absolute end-5 top-16 z-[1001] w-72 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-2xl"
                  >
                    <div class="max-h-80 overflow-y-auto py-2">
                      <button
                        @click="selectLocation(null)"
                        class="block w-full border-b border-gray-50 px-5 py-3 text-left font-bold text-brand-blue hover:bg-blue-50"
                      >
                        📍 Show All Locations
                      </button>

                      <button
                        v-for="item in franchises"
                        :key="item.id"
                        @click="selectLocation(item)"
                        class="group block w-full px-5 py-3 text-left transition hover:bg-gray-50"
                      >
                        <span
                          class="block text-[10px] font-bold text-gray-400 uppercase group-hover:text-brand-green"
                        >
                          {{ item.region }}
                        </span>
                        <span class="block text-sm font-medium text-gray-800">
                          {{ item.province }}, {{ item.city }}
                        </span>
                      </button>
                    </div>
                  </div>

                  <div
                    class="relative z-0 min-h-full w-full flex-1 overflow-hidden bg-white shadow-md"
                  >
                    <LeafletMap :locations="mapLocations" :fit-bounds="true">
                      <template #popup="{ item }">
                        <div class="p-1 text-xs">
                          <p class="font-bold text-brand-blue">
                            <span class="text-black">Franchise:</span>
                            {{ item.name }}
                          </p>
                        </div>
                      </template>
                    </LeafletMap>
                  </div>
                </div>

                <template v-if="goBackHomeButton">
                  <div
                    class="absolute bottom-8 left-1/2 z-10 flex w-full max-w-xs -translate-x-1/2 flex-col items-center space-y-3"
                  >
                    <Button
                      variant="secondary"
                      asChild
                      class="w-full cursor-pointer"
                    >
                      <Link :href="home()">Return Home</Link>
                    </Button>
                    <div class="text-center text-sm text-white/90">
                      Already have an account?
                      <TextLink
                        :href="login()"
                        class="text-white underline underline-offset-4 hover:text-white/80"
                      >
                        Log in
                      </TextLink>
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <div>
              <Card class="border-0 shadow-none">
                <CardHeader class="pt-6 text-center">
                  <img :src="logo" alt="Logo" class="mx-auto h-14 w-auto" />
                  <CardTitle class="pt-4 text-2xl text-auth-blue">{{
                    title
                  }}</CardTitle>
                </CardHeader>

                <CardTitle
                  v-if="titleRegistration"
                  class="w-full bg-auth-blue py-2 text-center text-3xl text-white"
                >
                  {{ titleRegistration }}
                </CardTitle>

                <CardContent class="border-0 px-3 pb-2 md:px-8">
                  <slot />
                </CardContent>
              </Card>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>
