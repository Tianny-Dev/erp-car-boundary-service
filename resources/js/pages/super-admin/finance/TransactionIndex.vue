<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
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
import { computed, h, ref, watch } from 'vue';

// --- Define Props ---
const props = defineProps<{
  transactions: {
    data: TransactionRow[];
  };
  franchises: { id: number; name: string }[];
  branches: { id: number; name: string }[];
  filters: {
    tab: 'franchise' | 'branch';
    franchise: string | null;
    branch: string | null;
    service: 'Trips' | 'Boundary';
  };
}>();

// --- Define TransactionRow Interface ---
interface TransactionRow {
  id: number | null;
  franchise_name?: string;
  branch_name?: string;
  invoice_no: string;
  amount: number;
  date: string;
  service_type: string;
  driver_name: string;
}

// --- 3. Setup Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Transaction History',
    href: superAdmin.transaction.index().url,
  },
];

// --- 4. Setup Reactive State for Filters ---
const activeTab = ref(props.filters.tab);
const selectedFranchise = ref(props.filters.franchise || 'all');
const selectedBranch = ref(props.filters.branch || 'all');
const selectedService = ref(props.filters.service);

// --- 5. Computed Properties for UI ---
const title = computed(() => {
  return activeTab.value === 'franchise'
    ? 'Franchise Transactions'
    : 'Branch Transactions';
});

const selectOptions = computed(() => {
  return activeTab.value === 'franchise' ? props.franchises : props.branches;
});

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
const transactionColumns = computed<ColumnDef<TransactionRow>[]>(() => {
  const isFranchiseTab = activeTab.value === 'franchise';

  const columns: ColumnDef<TransactionRow>[] = [
    {
      accessorKey: 'invoice_no',
      header: 'Invoice #',
    },
    {
      accessorKey: isFranchiseTab ? 'franchise_name' : 'branch_name',
      header: isFranchiseTab ? 'Franchise' : 'Branch',
    },
    {
      accessorKey: 'driver_name',
      header: 'Driver',
    },
    {
      accessorKey: 'date',
      header: 'Date',
    },
    {
      accessorKey: 'service_type',
      header: 'Service Type',
    },
    {
      accessorKey: 'amount',
      header: 'Amount',
      cell: (info) => formatCurrency(info.getValue() as number),
    },
    {
      accessorKey: 'status_name',
      header: () => h('div', { class: 'text-center' }, 'Status'),
      cell: ({ row }) => {
        const status = row.getValue('status_name') as string;
        const badgeClass = {
          'bg-blue-500 hover:bg-green-600': status === 'paid',
          'bg-amber-500 hover:bg-amber-600': status === 'pending',
          'bg-rose-500 hover:bg-rose-600':
            status === 'cancelled' || status === 'overdue',
        };
        return h('div', { class: 'text-center' }, [
          h(
            Badge,
            { class: [badgeClass, 'text-white'] },
            () => status || 'N/A',
          ),
        ]);
      },
    },
  ];

  return columns;
});

// --- Watchers to Update URL ---
const updateFilters = () => {
  const queryParams: Record<string, string> = {
    tab: activeTab.value,
    service: selectedService.value,
  };

  if (activeTab.value === 'franchise' && selectedFranchise.value !== 'all') {
    queryParams.franchise = selectedFranchise.value;
  } else if (activeTab.value === 'branch' && selectedBranch.value !== 'all') {
    queryParams.branch = selectedBranch.value;
  }

  router.get(superAdmin.transaction.index().url, queryParams, {
    preserveScroll: true,
    replace: true,
  });
};

// Watch for tab changes
watch(activeTab, (newTab) => {
  if (newTab === 'franchise') {
    selectedBranch.value = 'all';
  } else {
    selectedFranchise.value = 'all';
  }
  // The main watcher will handle the update
});

// Watch all filters for changes (debounced)
watch(
  [selectedFranchise, selectedBranch, activeTab, selectedService],
  debounce(() => {
    updateFilters();
  }, 300),
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
          <div class="flex gap-4">
            <Select v-model="selectedService">
              <SelectTrigger class="w-[150px] cursor-pointer">
                <SelectValue placeholder="Filter by..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="Trips" class="cursor-pointer">
                  Trips
                </SelectItem>
                <SelectItem value="Boundary" class="cursor-pointer">
                  Boundary
                </SelectItem>
              </SelectContent>
            </Select>

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
        </div>

        <DataTable
          :columns="transactionColumns"
          :data="transactions.data"
          search-placeholder="Search transactions..."
        />
      </div>
    </div>
  </AppLayout>
</template>
