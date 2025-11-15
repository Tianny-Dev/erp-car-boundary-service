<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
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
import { type ColumnDef } from '@tanstack/vue-table';
import { debounce } from 'lodash-es';
import { computed, ref, watch } from 'vue';

// --- Define Props ---
const props = defineProps<{
  revenues: {
    data: RevenueRow[];
  };
  franchises: { id: number; name: string }[];
  branches: { id: number; name: string }[];
  filters: {
    tab: 'franchise' | 'branch';
    franchise: string | null;
    branch: string | null;
  };
}>();

// --- Define RevenueRow Interface ---
interface RevenueRow {
  id: number;
  franchise_name?: string;
  branch_name?: string;
  invoice_no: string;
  amount: number;
  payment_date: string;
  service_type: string;
}

// --- 3. Setup Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Revenue Report',
    href: superAdmin.revenue.index().url,
  },
];

// --- 4. Setup Reactive State for Filters ---
const activeTab = ref(props.filters.tab || 'franchise');
const selectedFranchise = ref(props.filters.franchise || 'all');
const selectedBranch = ref(props.filters.branch || 'all');

// --- 5. Computed Properties for UI ---
const title = computed(() => {
  return activeTab.value === 'franchise'
    ? 'Franchise Revenues'
    : 'Branch Revenues';
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

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
  }).format(amount);
};

// Computed columns for the data table
const revenueColumns = computed<ColumnDef<RevenueRow>[]>(() => {
  const baseColumns: ColumnDef<RevenueRow>[] = [
    {
      accessorKey: 'invoice_no',
      header: 'Invoice #',
    },
    // Conditionally add the correct column
    activeTab.value === 'franchise'
      ? { accessorKey: 'franchise_name', header: 'Franchise' }
      : { accessorKey: 'branch_name', header: 'Branch' },
    {
      accessorKey: 'payment_date',
      header: 'Date',
    },
    {
      accessorKey: 'service_type',
      header: 'Service Type',
    },
    {
      accessorKey: 'amount',
      header: 'Amount',
      cell: (info) => {
        return formatCurrency(info.getValue() as number);
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

  router.get(superAdmin.revenue.index().url, queryParams, {
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
  <Head title="Super Admin Dashboard" />

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
          :columns="revenueColumns"
          :data="revenues.data"
          search-placeholder="Search revenues..."
        />
      </div>
    </div>
  </AppLayout>
</template>
