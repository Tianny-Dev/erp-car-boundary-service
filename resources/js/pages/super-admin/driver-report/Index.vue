<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
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
    revenues: {
        data: RevenueRow[];
    };
    franchises: { id: number; name: string }[];
    branches: { id: number; name: string }[];
    drivers: { id: number; name: string }[];
    filters: {
        tab: 'franchise' | 'branch';
        franchise: string | null;
        branch: string | null;
        driver: string | null;
        service: 'Trips';
        period: 'daily' | 'weekly' | 'monthly';
    };
}>();

// --- Define RevenueRow Interface ---
interface RevenueRow {
    id: number | null;
    franchise_name?: string;
    branch_name?: string;
    invoice_no?: string;
    driver_id: string; // Foreign key
    amount: number;
    payment_date: string; // This now holds the formatted period (e.g., "YYYY-MM-DD" or "Nov 19 - 25, 2025")
    service_type: string;
    driver_name: string;
    driver?: {
        id: number;
        username: string;
    };
    driver_username?: string;
    // Dynamic breakdown fields (e.g., tax, bank, driver_earning)
    [key: string]: any;
}

// --- 3. Setup Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Driver Report',
        href: superAdmin.driverreport().url,
    },
];

// --- 4. Setup Reactive State for Filters ---
const activeTab = ref(props.filters.tab);
const selectedFranchise = ref(props.filters.franchise || 'all');
const selectedBranch = ref(props.filters.branch || 'all');
const selectedDriver = ref(props.filters.driver || 'all');
const selectedService = ref(props.filters.service);
const selectedPeriod = ref(props.filters.period);

// --- 5. Computed Properties for UI ---
const title = computed(() => {
    return activeTab.value === 'franchise' ? 'Franchise Driver' : 'Branch Driver';
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
        selectedDriver.value = 'all';
    },
});

const showExportModal = ref(false);
const exportType = ref<'pdf' | 'excel' | 'csv'>('pdf');
const exportYear = ref(String(new Date().getFullYear()));
const exportMonths = ref<number[]>([]); // Initial state is an empty array (unchecked)

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
    // FIX: Change this line to initialize to an empty array so all months are UNCHECKED by default
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
        service: selectedService.value,
        period: selectedPeriod.value,
        export_type: exportType.value,
        year: exportYear.value,
    });

    if (selectedDriver.value && selectedDriver.value !== 'all') {
         params.append('driver', selectedDriver.value);
     }

    // 2. Add branch/franchise filter if not 'all'
    if (activeTab.value === 'franchise' && selectedFranchise.value !== 'all') {
        params.append('franchise', selectedFranchise.value);
    } else if (activeTab.value === 'branch' && selectedBranch.value !== 'all') {
        params.append('branch', selectedBranch.value);
    }

    // 3. Add months
    exportMonths.value.forEach((month) => {
        params.append('months[]', String(month));
    });

    // 4. Build URL and open in new tab (triggers download)
    const url = `${superAdmin.driver.export().url}?${params.toString()}`;
    window.open(url, '_blank');

    // 5. Close modal and reset
    showExportModal.value = false;
    // This line is correct for resetting after export
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
const revenueColumns = computed<ColumnDef<RevenueRow>[]>(() => {
    const isDaily = selectedPeriod.value === 'daily';

    // Base columns
    const columns: ColumnDef<RevenueRow>[] = [
        {
            accessorKey: 'driver_username',
            header: 'Driver Name',
        },
        {
            accessorKey:
                activeTab.value === 'franchise' ? 'franchise_name' : 'branch_name',
            header: activeTab.value === 'franchise' ? 'Franchise' : 'Branch',
        },
        {
            accessorKey: 'payment_date',
            header: isDaily ? 'Date' : 'Period',
        },
        {
            accessorKey: 'amount',
            header: 'Total Amount',
            cell: (info) => formatCurrency(info.getValue() as number),
        },
    ];

    // --- START: NEW LOGIC to dynamically add breakdown columns and calculate Driver Earning ---
    const firstRow = props.revenues.data[0];
    // This array will hold the keys of the dynamic breakdown fees (e.g., ['tax', 'bank_fee'])
    let dynamicKeys: string[] = [];

    if (firstRow) {
        const keys = Object.keys(firstRow);

        // Filter for dynamic breakdown keys (exclude all known static/non-breakdown keys)
        const breakdownKeys = keys.filter(
            (key) =>
                key !== 'id' &&
                key !== 'invoice_no' &&
                key !== 'driver_id' &&
                key !== 'amount' &&
                key !== 'payment_date' &&
                key !== 'service_type' &&
                key !== 'driver' &&
                key !== 'driver_username' &&
                key !== 'franchise_name' &&
                key !== 'branch_name' &&
                key !== 'week_sort' && // Exclude new sort key from controller
                key !== 'month_sort' && // Exclude month sort key from controller
                key !== 'year_sort' && // Exclude year sort key from controller
                // Exclude 'driver_earning' as we are going to calculate it explicitly
                key !== 'driver_earning',
        );

        dynamicKeys = breakdownKeys;

        // 1. Add columns for all found breakdown keys (e.g., 'tax', 'bank', 'other_fee')
        breakdownKeys.forEach((key) => {
            columns.push({
                accessorKey: key,
                // Convert 'tax' to 'Tax', 'bank_fee' to 'Bank Fee' for the header
                header: key
                    .split('_')
                    .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
                    .join(' '),
                cell: (info) => formatCurrency(info.getValue() as number),
            });
        });
    }

    // 2. Add the Driver Earning column, calculating the value on the fly
    columns.push({
        // accessorKey is used for the column identifier but the value is computed
        accessorKey: 'driver_earning',
        header: 'Driver Earning',
        cell: (info) => {
            // Get the data row for the current cell
            const rowData = info.row.original as RevenueRow;

            // Sum up all dynamic breakdown values using the collected keys
            const totalBreakdowns = dynamicKeys.reduce((sum, key) => {
                // Safely access the value, defaulting to 0 if null, undefined, or not a number
                const value = parseFloat(rowData[key]) || 0;
                return sum + value;
            }, 0);

            // Calculate Driver Earning: Total Amount - Total Breakdowns
            const driverEarning = rowData.amount - totalBreakdowns;

            // Ensure earning is not negative (like using max(0, earning) in PHP) and format it
            return formatCurrency(Math.max(0, driverEarning));
        },
    });
    // --- END: NEW LOGIC ---

    // 3. Add the button column, See All the tansaction this day
    // --- 3. Add the button column, See All the tansaction this day
columns.push({
    accessorKey: 'action',
    header: 'Action',
    cell: (info) => {
        const rowData = info.row.original as RevenueRow;

        return h(
            Button,
            {
                class: "py-1 px-2 text-xs",
                onClick: () => {
                    const queryParams: Record<string, string> = {
                        driver_id: String(rowData.driver_id),
                        payment_date: rowData.payment_date,
                        period: selectedPeriod.value,

                        tab: activeTab.value,
                    };

                    if (activeTab.value === 'franchise' && selectedFranchise.value !== 'all') {
                        queryParams.franchise = selectedFranchise.value;
                    } else if (activeTab.value === 'branch' && selectedBranch.value !== 'all') {
                        queryParams.branch = selectedBranch.value;
                    }

                    router.get(superAdmin.driverreport.details().url, queryParams, {
                        preserveScroll: true,
                        replace: false,
                    });
                }
            },
            () => 'View Details'
        );
    },
});
    // --- END: NEW LOGIC ---

    return columns;
});

// --- Watchers to Update URL ---
const updateFilters = () => {
    const queryParams: Record<string, string> = {
        tab: activeTab.value,
        service: selectedService.value,
        period: selectedPeriod.value,
    };

    if (selectedDriver.value && selectedDriver.value !== 'all') {
      queryParams.driver = selectedDriver.value;
    }

    if (activeTab.value === 'franchise' && selectedFranchise.value !== 'all') {
        queryParams.franchise = selectedFranchise.value;
    } else if (activeTab.value === 'branch' && selectedBranch.value !== 'all') {
        queryParams.branch = selectedBranch.value;
    }

    router.get(superAdmin.driverreport().url, queryParams, {
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
    selectedDriver.value = 'all';
});

// Watch all filters for changes (debounced)
watch(
    [
        selectedFranchise,
        selectedBranch,
        activeTab,
        selectedService,
        selectedPeriod,
        selectedDriver,
    ],
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
                        <Select v-model="selectedPeriod">
                            <SelectTrigger class="w-[150px] cursor-pointer">
                                <SelectValue placeholder="Filter by..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="daily" class="cursor-pointer">
                                    Daily
                                </SelectItem>
                                <SelectItem value="weekly" class="cursor-pointer">
                                    Weekly
                                </SelectItem>
                                <SelectItem value="monthly" class="cursor-pointer">
                                    Monthly
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

                        <Select v-model="selectedDriver">
              <SelectTrigger class="w-[200px] cursor-pointer">
                <SelectValue placeholder="Select Driver" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Drivers</SelectItem>
                <SelectItem
                  v-for="driver in drivers"
                  :key="driver.id"
                  :value="String(driver.id)"
                >
                  {{ driver.name }}
                </SelectItem>
              </SelectContent>
            </Select>

                    </div>
                </div>

                <DataTable
                    :columns="revenueColumns"
                    :data="revenues.data"
                    search-placeholder="Search drivers..."
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
                        <Button @click="handleExport"> Confirm Export </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
