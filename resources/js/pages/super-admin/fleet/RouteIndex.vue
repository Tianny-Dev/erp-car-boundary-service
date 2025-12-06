<script setup lang="ts">
import LeafletMap, { type MarkerData } from '@/components/LeafletMap.vue';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { debounce } from 'lodash-es';
import { computed, ref, watch } from 'vue';

// --- Define Props ---
const props = defineProps<{
  mapMarkers: {
    data: MarkerData[];
  };
  franchises: { id: number; name: string }[];
  branches: { id: number; name: string }[];
  filters: {
    tab: 'franchise' | 'branch';
    franchise: string | null;
    branch: string | null;
  };
}>();

// --- 3. Setup Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Trip Monitoring',
    href: superAdmin.route.index().url,
  },
];

// --- 4. Setup Reactive State for Filters ---
const activeTab = ref(props.filters.tab || 'franchise');
const selectedFranchise = ref(props.filters.franchise || 'all');
const selectedBranch = ref(props.filters.branch || 'all');

// --- 5. Computed Properties for UI ---
const title = computed(() => {
  return activeTab.value === 'franchise'
    ? 'Franchise Monitoring'
    : 'Branch Monitoring';
});

// Computed list for the select dropdown based on the active tab
const selectOptions = computed(() => {
  return activeTab.value === 'franchise' ? props.franchises : props.branches;
});

// A computed v-model for the *single* select component
const selectedFilter = computed({
  get() {
    return activeTab.value === 'franchise'
      ? selectedFranchise.value
      : selectedBranch.value;
  },
  set(value: string) {
    if (activeTab.value === 'franchise') {
      selectedFranchise.value = value;
    } else {
      selectedBranch.value = value;
    }
  },
});

// --- Watchers to Update URL ---
const updateFilters = () => {
  const queryParams: Record<string, string> = {
    tab: activeTab.value,
  };

  // **This is the crucial part for "no conflicts"**
  // Only add the 'franchise' param if the tab is 'franchise'
  if (activeTab.value === 'franchise' && selectedFranchise.value !== 'all') {
    queryParams.franchise = selectedFranchise.value;
  }
  // Only add the 'branch' param if the tab is 'branch'
  else if (activeTab.value === 'branch' && selectedBranch.value !== 'all') {
    queryParams.branch = selectedBranch.value;
  }

  router.get(superAdmin.route.index().url, queryParams, {
    preserveScroll: true,
    replace: true, // Doesn't pollute browser history
  });
};

// Watch for tab changes (instant update)
watch(activeTab, (newTab) => {
  // When tab switches, reset the *other* filter to 'all'
  // This helps keep the URL clean
  if (newTab === 'franchise') {
    selectedBranch.value = 'all';
  } else {
    selectedFranchise.value = 'all';
  }
});

// Watch for select filter changes (debounced)
watch(
  [selectedFranchise, selectedBranch, activeTab],
  debounce(() => {
    updateFilters();
  }, 300), // Debounce to avoid firing on every keystroke/click
);
</script>

<template>
  <Head title="Trip Monitoring" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
      <Tabs v-model="activeTab" class="w-full">
        <TabsList class="w-full justify-start p-1.5">
          <TabsTrigger
            value="franchise"
            class="cursor-pointer font-semibold"
            :class="{ 'pointer-events-none': activeTab === 'franchise' }"
          >
            Franchise
          </TabsTrigger>
          <TabsTrigger
            value="branch"
            class="cursor-pointer font-semibold"
            :class="{ 'pointer-events-none': activeTab === 'branch' }"
          >
            Branch
          </TabsTrigger>
        </TabsList>
      </Tabs>

      <div
        class="relative rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
      >
        <div class="mb-4 flex items-center justify-between">
          <h2 class="font-mono text-xl font-semibold">
            {{ title }}
          </h2>

          <div class="flex gap-4">
            <Select v-model="selectedFilter">
              <SelectTrigger class="w-[240px]">
                <SelectValue placeholder="Filter by..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  All
                  {{ activeTab === 'franchise' ? 'Franchises' : 'Branches' }}
                </SelectItem>
                <SelectItem
                  v-for="option in selectOptions"
                  :key="option.id"
                  :value="String(option.id)"
                >
                  {{ option.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div class="w-full rounded-lg border shadow-sm">
          <LeafletMap :locations="props.mapMarkers.data" :fit-bounds="true" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
