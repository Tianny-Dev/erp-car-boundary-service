<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useDetailsModal } from '@/composables/useDetailsModal';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { debounce } from 'lodash-es';
import { AlertCircleIcon, MoreHorizontal } from 'lucide-vue-next';
import { computed, h, ref, watch } from 'vue';

// --- Define Props ---
const props = defineProps<{
  vehicles: {
    data: VehicleRow[];
  };
  franchises: { id: number; name: string }[];
  branches: { id: number; name: string }[];
  filters: {
    tab: 'franchise' | 'branch';
    franchise: string | null;
    branch: string | null;
  };
}>();

// --- Define VehicleRow Interface ---
interface VehicleRow {
  id: number;
  franchise_name?: string;
  branch_name?: string;
  plate_number: string;
  vin: string;
  status_name: string;
}

// --- 3. Setup Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Vehicle Management',
    href: superAdmin.vehicle.index().url,
  },
];

// --- 4. Setup Reactive State for Filters ---
const activeTab = ref(props.filters.tab || 'franchise');
const selectedFranchise = ref(props.filters.franchise || 'all');
const selectedBranch = ref(props.filters.branch || 'all');

// --- 5. Computed Properties for UI ---
const title = computed(() => {
  return activeTab.value === 'franchise'
    ? 'Franchise Vehicles'
    : 'Branch Vehicles';
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

interface VehicleModal {
  id: number;
  status: string;
  plate_number: string;
  vin: string;
  brand: string;
  model: string;
  year: string;
  color: string;
}
const vehicleDetails = computed(() => {
  const data = vehicleModal.data.value;
  if (!data) return [];

  return [
    { label: 'Status', value: data.status, type: 'text' },
    { label: 'Plate Number', value: data.plate_number, type: 'text' },
    { label: 'Vehicle Identification Number', value: data.vin, type: 'text' },
    { label: 'Brand', value: data.brand, type: 'text' },
    { label: 'Model', value: data.model, type: 'text' },
    { label: 'Year', value: data.year, type: 'text' },
    { label: 'Color', value: data.color, type: 'text' },
  ].filter((item) => item.value);
});

// --- Modal State ---
const vehicleModal = useDetailsModal<VehicleModal>({
  baseUrl: '/super-admin/vehicle',
});

// Computed columns for the data table
const vehicleColumns = computed<ColumnDef<VehicleRow>[]>(() => {
  const baseColumns: ColumnDef<VehicleRow>[] = [
    // Conditionally add the correct column
    activeTab.value === 'franchise'
      ? { accessorKey: 'franchise_name', header: 'Franchise' }
      : { accessorKey: 'branch_name', header: 'Branch' },
    {
      accessorKey: 'vin',
      header: 'Vehicle Identification Number',
    },
    {
      accessorKey: 'plate_number',
      header: 'Plate Number',
    },
    {
      accessorKey: 'status_name',
      header: () => h('div', { class: 'text-center' }, 'Status'),
      cell: ({ row }) => {
        const status = row.getValue('status_name') as string;
        return h('div', { class: 'text-center' }, [
          h(
            Badge,
            { class: ['bg-blue-500 hover:bg-blue-600', 'text-white'] },
            () => status || 'N/A',
          ),
        ]);
      },
    },
    {
      id: 'actions',
      header: () => h('div', { class: 'text-center' }, 'Actions'),
      cell: ({ row }) => {
        const vehicle = row.original;

        return h('div', { class: 'relative text-center' }, [
          h(DropdownMenu, null, () => [
            h(
              DropdownMenuTrigger,
              { asChild: true, class: 'cursor-pointer' },
              () =>
                h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => [
                  h('span', { class: 'sr-only' }, 'Open menu'),
                  h(MoreHorizontal, { class: 'h-4 w-4' }),
                ]),
            ),
            h(DropdownMenuContent, { align: 'end', class: 'border-2' }, () => [
              h(DropdownMenuLabel, null, () => 'Actions'),
              h(
                DropdownMenuItem,
                {
                  class: 'cursor-pointer',
                  onClick: () => vehicleModal.open(vehicle.id),
                },
                () => 'View Vehicle Details',
              ),
            ]),
          ]),
        ]);
      },
    },
  ];
  return baseColumns;
});

// --- Watchers to Update URL ---
const updateFilters = () => {
  const queryParams: {
    tab: string;
    franchise?: string;
    branch?: string;
  } = {
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

  router.get(superAdmin.vehicle.index().url, queryParams, {
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
  updateFilters();
});

// Watch for select filter changes (debounced)
watch(
  [selectedFranchise, selectedBranch],
  debounce(() => {
    updateFilters();
  }, 300), // Debounce to avoid firing on every keystroke/click
);
</script>

<template>
  <Head title="Vehicle Management" />

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

          <Select v-model="selectedFilter">
            <SelectTrigger class="w-[240px] cursor-pointer">
              <SelectValue placeholder="Filter by..." />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all" class="cursor-pointer">
                All
                {{ activeTab === 'franchise' ? 'Franchises' : 'Branches' }}
              </SelectItem>
              <SelectItem
                class="cursor-pointer"
                v-for="option in selectOptions"
                :key="option.id"
                :value="String(option.id)"
              >
                {{ option.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <DataTable
          :columns="vehicleColumns"
          :data="vehicles.data"
          search-placeholder="Search vehicles..."
        />
      </div>
    </div>
  </AppLayout>

  <Dialog v-model:open="vehicleModal.isOpen.value">
    <DialogContent class="max-w-3xl overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Vehicle Details</DialogTitle>
      </DialogHeader>
      <DialogDescription>
        <div v-if="vehicleModal.isLoading.value" class="grid grid-cols-2 gap-4">
          <template v-for="item in 10" :key="item">
            <Skeleton class="h-5 w-24" />
            <Skeleton class="h-5 w-3/4" />
          </template>
        </div>

        <div
          v-else-if="vehicleDetails.length > 0"
          class="grid grid-cols-2 gap-4"
        >
          <template v-for="item in vehicleDetails" :key="item.label">
            <div class="font-medium">{{ item.label }}:</div>

            <div v-if="item.type === 'link'">
              <a
                :href="item.value"
                target="_blank"
                class="text-blue-500 hover:underline"
                >View</a
              >
            </div>

            <div v-else>
              {{ item.value }}
            </div>
          </template>
        </div>

        <div v-else-if="vehicleModal.isError.value">
          <Alert
            variant="destructive"
            class="border-2 border-red-500 shadow-lg"
          >
            <AlertCircleIcon class="h-4 w-4" />
            <AlertTitle class="font-bold">Error</AlertTitle>
            <AlertDescription class="font-semibold">
              Failed to load vehicle details.
            </AlertDescription>
          </Alert>
        </div>
      </DialogDescription>

      <DialogFooter class="mt-5">
        <Button
          variant="outline"
          class="cursor-pointer"
          @click="vehicleModal.close"
          >Close</Button
        >
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
