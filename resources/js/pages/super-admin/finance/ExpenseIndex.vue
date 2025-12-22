<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import Button from '@/components/ui/button/Button.vue';
import { Checkbox } from '@/components/ui/checkbox';
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
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { debounce } from 'lodash-es';
import { MoreHorizontal } from 'lucide-vue-next';
import { computed, h, ref, watch } from 'vue';

// --- Define Props ---
const props = defineProps<{
  expenses: {
    data: ExpenseRow[];
  };
  franchises: { id: number; name: string }[];
  branches: { id: number; name: string }[];
  filters: {
    tab: 'franchise' | 'branch';
    franchise: string[];
    branch: string[];
    period: 'daily' | 'weekly' | 'monthly';
  };
}>();

// --- Define ExpenseRow Interface ---
interface ExpenseRow {
  franchise_name?: string;
  branch_name?: string;
  amount: number;
  payment_date: string;
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
const selectedFranchise = ref<string[]>(props.filters.franchise || []);
const selectedBranch = ref<string[]>(props.filters.branch || []);
const selectedPeriod = ref(props.filters.period);

// --- 5. Computed Properties for UI ---
const title = computed(() => {
  return activeTab.value === 'franchise'
    ? 'Franchise Expenses'
    : 'Branch Expenses';
});

const selectedContext = computed({
  get: () =>
    activeTab.value === 'franchise'
      ? selectedFranchise.value
      : selectedBranch.value,
  set: (val: string[]) => {
    if (activeTab.value === 'franchise') {
      selectedFranchise.value = val;
    } else {
      selectedBranch.value = val;
    }
  },
});

// Mapping options for the MultiSelect
const contextOptions = computed(() => {
  const data =
    activeTab.value === 'franchise' ? props.franchises : props.branches;
  return data.map((item) => ({ id: item.id, label: item.name }));
});

const showExportModal = ref(false);
const exportType = ref<'pdf' | 'excel' | 'csv'>('pdf');
const exportYear = ref(String(new Date().getFullYear()));
const exportMonths = ref<number[]>([]);

// Data for modal selects 2025 is minimum up to latest year
const yearOptions = computed(() => {
  const current = new Date().getFullYear();
  const start = 2025;
  return Array.from({ length: current - start + 1 }, (_, i) =>
    String(start + i),
  );
});

const monthOptions = [
  { id: 1, label: 'January' },
  { id: 2, label: 'February' },
  { id: 3, label: 'March' },
  { id: 4, label: 'April' },
  { id: 5, label: 'May' },
  { id: 6, label: 'June' },
  { id: 7, label: 'July' },
  { id: 8, label: 'August' },
  { id: 9, label: 'September' },
  { id: 10, label: 'October' },
  { id: 11, label: 'November' },
  { id: 12, label: 'December' },
];

// Open modal and set the export type
function openExportModal(type: 'pdf' | 'excel' | 'csv') {
  exportType.value = type;
  // exportMonths.value = monthOptions.map((month) => month.id);
  exportMonths.value = [];
  showExportModal.value = true;
}

// Handle checkbox-style "multi-select" for months
function toggleMonth(monthId: number) {
  const index = exportMonths.value.indexOf(monthId);
  if (index > -1) {
    exportMonths.value.splice(index, 1);
  } else {
    exportMonths.value.push(monthId);
  }
}

// Build and trigger the download URL
function handleExport() {
  if (!exportYear.value || exportMonths.value.length === 0) {
    return;
  }

  // 1. Get all *current* page filters
  const params = new URLSearchParams({
    tab: activeTab.value,
    period: selectedPeriod.value,
    export: exportType.value,
    year: exportYear.value,
  });

  // 2. Add branch/franchise filter if not 'all'
  if (activeTab.value === 'franchise' && selectedFranchise.value.length > 0) {
    selectedFranchise.value.forEach((f) => params.append('franchise[]', f));
  } else if (activeTab.value === 'branch' && selectedBranch.value.length > 0) {
    selectedBranch.value.forEach((b) => params.append('branch[]', b));
  }

  // 3. Add months
  exportMonths.value.forEach((month) => {
    params.append('months[]', String(month));
  });

  // 4. Build URL and open in new tab (triggers download)
  const url = `${superAdmin.expense.export().url}?${params.toString()}`;
  window.open(url, '_blank');

  // 5. Close modal and reset
  showExportModal.value = false;
  exportMonths.value = [];
}

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
      accessorKey: 'payment_date',
      header: isDaily ? 'Date' : 'Period',
    },
    {
      accessorKey: 'amount',
      header: isDaily ? 'Amount' : 'Total Amount',
      cell: (info) => formatCurrency(info.getValue() as number),
    },
    {
      id: 'actions',
      header: () => h('div', 'Actions'),
      cell: ({ row }) => {
        const rowData = row.original as any;

        return h('div', { class: 'relative' }, [
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
                  onClick: () => {
                    const queryParams: Record<string, string> = {
                      start: rowData.query_params.start,
                      end: rowData.query_params.end,
                      label: rowData.payment_date,
                      tab: activeTab.value,
                      franchise: rowData.franchise_id,
                      branch: rowData.branch_id,
                    };

                    router.get(superAdmin.expense.show().url, queryParams, {
                      preserveScroll: true,
                      replace: false,
                    });
                  },
                },
                () => 'View Expense Details',
              ),
            ]),
          ]),
        ]);
      },
    },
  ];

  return columns;
});

// --- Watchers to Update URL ---
const updateFilters = () => {
  router.get(
    superAdmin.expense.index().url,
    {
      tab: activeTab.value,
      period: selectedPeriod.value,
      franchise: activeTab.value === 'franchise' ? selectedFranchise.value : [],
      branch: activeTab.value === 'branch' ? selectedBranch.value : [],
    },
    {
      preserveScroll: true,
      replace: true,
    },
  );
};

// Watch for tab changes
watch(activeTab, () => {
  selectedFranchise.value = [];
  selectedBranch.value = [];
  updateFilters(); // Trigger reload
});

// Watch all filters for changes (debounced)
watch(
  [selectedPeriod],
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

            <MultiSelect
              v-model="selectedContext"
              :options="contextOptions"
              :placeholder="
                activeTab === 'franchise'
                  ? 'Select Franchises'
                  : 'Select Branches'
              "
              :all-label="
                activeTab === 'franchise' ? 'All Franchises' : 'All Branches'
              "
              @change="
                (val) => {
                  if (activeTab === 'franchise') selectedFranchise = val;
                  else selectedBranch = val;
                  updateFilters();
                }
              "
            />
          </div>
        </div>

        <DataTable
          :columns="expenseColumns"
          :data="expenses.data"
          search-placeholder="Search expenses..."
        >
          <template #custom-actions>
            <Button @click="openExportModal('pdf')"> Export PDF </Button>
            <Button @click="openExportModal('excel')"> Export Excel </Button>
            <Button @click="openExportModal('csv')"> Export CSV </Button>
          </template>
        </DataTable>
      </div>
      <Dialog v-model:open="showExportModal">
        <DialogContent class="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle> Export {{ exportType.toUpperCase() }} </DialogTitle>
            <DialogDescription>
              Select the year and months to export. This will use your currently
              active filters.
            </DialogDescription>
          </DialogHeader>
          <div class="grid gap-4 py-4">
            <div class="grid grid-cols-4 items-center gap-4">
              <label class="text-right">Year</label>
              <Select v-model="exportYear">
                <SelectTrigger class="col-span-3">
                  <SelectValue placeholder="Select year" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="year in yearOptions"
                    :key="year"
                    :value="year"
                  >
                    {{ year }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="grid grid-cols-4 items-start gap-4">
              <label class="pt-2 text-right">Months</label>
              <div class="col-span-3 grid grid-cols-2 gap-2">
                <div
                  v-for="month in monthOptions"
                  :key="month.id"
                  class="flex items-center gap-2"
                >
                  <Checkbox
                    :id="`month-${month.id}`"
                    :model-value="exportMonths.includes(month.id)"
                    @update:model-value="() => toggleMonth(month.id)"
                  />

                  <label :for="`month-${month.id}`" class="cursor-pointer">
                    {{ month.label }}
                  </label>
                </div>
              </div>
            </div>
          </div>
          <DialogFooter>
            <Button @click="handleExport" :disabled="exportMonths.length === 0">
              Confirm Export
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
