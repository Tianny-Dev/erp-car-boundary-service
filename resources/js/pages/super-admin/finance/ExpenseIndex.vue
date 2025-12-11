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
  expenses: {
    data: ExpenseRow[];
  };
  franchises: { id: number; name: string }[];
  branches: { id: number; name: string }[];
  filters: {
    tab: 'franchise' | 'branch';
    franchise: string | null;
    branch: string | null;
    period: 'daily' | 'weekly' | 'monthly';
  };
}>();

// --- Define ExpenseRow Interface ---
interface ExpenseRow {
  id: number | null;
  franchise_name?: string;
  branch_name?: string;
  amount: number;
  payment_date: string;
  description: string;
  plate_number: string;
}

// --- 3. Setup Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Expense Report',
    href: superAdmin.expense.index().url,
  },
];

// --- 4. Setup Reactive State for Filters ---
const activeTab = ref(props.filters.tab);
const selectedFranchise = ref(props.filters.franchise || 'all');
const selectedBranch = ref(props.filters.branch || 'all');
const selectedPeriod = ref(props.filters.period);

// --- 5. Computed Properties for UI ---
const title = computed(() => {
  return activeTab.value === 'franchise'
    ? 'Franchise Expenses'
    : 'Branch Expenses';
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
const expenseColumns = computed<ColumnDef<ExpenseRow>[]>(() => {
  const isDaily = selectedPeriod.value === 'daily';
  const isFranchiseTab = activeTab.value === 'franchise';

  const columns: ColumnDef<ExpenseRow>[] = [
    {
      accessorKey: isFranchiseTab ? 'franchise_name' : 'branch_name',
      header: isFranchiseTab ? 'Franchise' : 'Branch',
    },
    {
      accessorKey: 'plate_number',
      header: 'Plate Number',
    },
    {
      accessorKey: 'description',
      header: 'Description',
    },
    {
      accessorKey: 'payment_date',
      header: isDaily ? 'Date' : 'Period',
    },
    {
      accessorKey: 'amount',
      header: isDaily ? 'Amount' : 'Total Amount',
      cell: (info) => formatCurrency(info.getValue() as number),
    },
  ];

  return columns;
});

// --- Watchers to Update URL ---
const updateFilters = () => {
  const queryParams: Record<string, string> = {
    tab: activeTab.value,
    period: selectedPeriod.value,
  };

  if (activeTab.value === 'franchise' && selectedFranchise.value !== 'all') {
    queryParams.franchise = selectedFranchise.value;
  } else if (activeTab.value === 'branch' && selectedBranch.value !== 'all') {
    queryParams.branch = selectedBranch.value;
  }

  router.get(superAdmin.expense.index().url, queryParams, {
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
  [selectedFranchise, selectedBranch, activeTab, selectedPeriod],
  debounce(() => {
    updateFilters();
  }, 300),
);
</script>

<template>
  <Head title="Expense Report" />

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
            <Select v-model="selectedPeriod">
              <SelectTrigger class="w-[150px]">
                <SelectValue placeholder="Filter by..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="daily"> Daily </SelectItem>
                <SelectItem value="weekly"> Weekly </SelectItem>
                <SelectItem value="monthly"> Monthly </SelectItem>
              </SelectContent>
            </Select>

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

        <DataTable
          :columns="expenseColumns"
          :data="expenses.data"
          search-placeholder="Search expenses..."
        />
      </div>
    </div>
  </AppLayout>
</template>
