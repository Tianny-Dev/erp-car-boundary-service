<script setup lang="ts">
import { computed, ref } from 'vue';
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

// Destructure props for easier use in script
const { franchises, userTypes } = defineProps<{
  userTypes: any[];
  franchises: FranchiseData[];
}>();

const isOpen = ref(false);
// We store the whole object or null (for "All")
const selectedFranchise = ref<FranchiseData | null>(null);

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

// Handle Selection
const selectLocation = (franchise: FranchiseData | null) => {
  selectedFranchise.value = franchise;
  isOpen.value = false;
};

// Compute the button text
const selectedButtonText = computed(() => {
  if (!selectedFranchise.value) return 'All Locations';
  return `${selectedFranchise.value.city}, ${selectedFranchise.value.province}`;
});

// Helper to safely convert strings to numbers
const parseToFloat = (val: string) => parseFloat(val) || 0;

// 🚀 MAP DATA LOGIC
const mapLocations = computed<MarkerData[]>(() => {
  // 1. If we have a selection, show only that one marker
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

  // 2. If "Show All" is clicked (selectedFranchise is null)
  if (!franchises || franchises.length === 0) return [];

  // Return all locations
  return franchises.map((f) => ({
    id: f.id,
    latitude: parseToFloat(f.latitude),
    longitude: parseToFloat(f.longitude),
    name: f.name,
    city: f.city,
    type: 'End',
  }));
});
</script>

<template>
  <div id="franchise" class="scroll-mt-14 px-5 py-12">
    <div class="mx-auto w-full max-w-[1320px]">
      <div class="grid grid-cols-12 items-center gap-5">
        <div class="col-span-12 md:col-span-6">
          <h1 class="text-4xl font-bold text-brand-blue">FRANCHISED NOW!</h1>
          <h1 class="pt-4 text-4xl font-bold">
            OWN A
            <span class="text-brand-blue">SMART E-TAXI FLEET.</span>
            MANAGE WITH EASE.
          </h1>
          <p class="pt-4 text-xl">
            As a Franchised Partner, you can oversee your vehicles, driver, and
            boundary payments effortlessly.
          </p>
          <p class="pt-4 text-xl">
            With our ERP System, you get real-time monitoring, financial
            transparency, and reliable reports.
          </p>
        </div>

        <div class="relative col-span-12 h-[450px] md:col-span-6">
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
            class="h-full w-full overflow-hidden rounded-2xl border-4 border-brand-blue shadow-md"
          >
            <LeafletMap :locations="mapLocations" :fit-bounds="true">
              <template #popup="{ item }">
                <div class="p-1">
                  <p class="font-bold text-brand-blue">
                    <span class="text-black">Franchise Name:</span>
                    {{ item.name }}
                  </p>
                </div>
              </template>
            </LeafletMap>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div
    class="bg-[url('@/assets/f1.jpg')] bg-cover bg-center bg-no-repeat px-5 py-16"
  >
    <div class="mx-auto w-full max-w-[1320px]">
      <div class="grid grid-cols-12 gap-10 text-white">
        <div class="col-span-12 md:col-span-6">
          <h2
            class="mb-6 border-l-4 border-brand-green pl-4 text-3xl font-bold"
          >
            BENEFITS
          </h2>
          <ul class="space-y-4 text-lg">
            <li>✓ View all fleet data in one dashboard</li>
            <li>✓ Automate boundary collection</li>
            <li>✓ Track driver performance and vehicle health</li>
            <li>✓ Access real-time GPS tracking</li>
          </ul>
        </div>
        <div class="col-span-12 md:col-span-6">
          <h2
            class="mb-6 border-l-4 border-brand-green pl-4 text-3xl font-bold"
          >
            REQUIREMENTS
          </h2>
          <ul class="space-y-4 text-lg">
            <li>• Valid Business Permit</li>
            <li>• DTI/SEC Registration</li>
            <li>• Bank Account for Payments</li>
            <li>• Franchise Agreement</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
