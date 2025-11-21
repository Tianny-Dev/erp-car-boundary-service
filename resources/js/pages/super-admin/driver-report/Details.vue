<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import Button from '@/components/ui/button/Button.vue';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { computed, h } from 'vue';

// --- Define Props (Matches DriverDetailsController@show return) ---
const props = defineProps<{
    // Driver Details (for header context)
    driver: { id: number; name: string };
    periodLabel: string; // The formatted date string (e.g., "November 20, 2025")
    breakdownTypes: string[]; // e.g., ['Tax', 'Bank Fee', 'Markup Fee']

    // Individual Revenue Records (The detailed data)
    details: DetailedRevenueRow[];

    // Filters used to generate the data (for context/back button)
    filters: {
        tab: 'franchise' | 'branch';
        franchise: string | null;
        branch: string | null;
        driver_id: string; // Updated key to match incoming URL
        payment_date: string; // Added for completeness in filters prop
        period: 'daily' | 'weekly' | 'monthly';
    };
}>();

// --- Define DetailedRevenueRow Interface ---
interface DetailedRevenueRow {
    id: number;
    invoice_no: string;
    amount: number; // Total trip amount
    payment_date: string;
    franchise?: { name: string } | null;
    branch?: { name: string } | null;
    driver?: { name: string } | null;
    // The breakdowns are complex objects, not simple values like in the index report
    breakdowns: Array<{
        total_earning: number;
        percentage_type: {
            name: string; // e.g., 'tax', 'bank_fee'
        };
    }>;
}

// --- 3. Setup Breadcrumbs (omitted for brevity) ---
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Driver Report',
        href: superAdmin.driverreport().url,
    },
    {
        title: 'Transaction Details',
        href: '#',
    },
];


// --- 4. Helper Functions ---
const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(amount);
};

// Function to calculate the breakdown amount for a given revenue row and type
const getBreakdownAmount = (row: DetailedRevenueRow, typeName: string): number => {
    // Convert the clean display name (e.g., "Bank Fee") back to the DB key (e.g., "bank_fee")
    const dbKey = typeName.toLowerCase().replace(/\s/g, '_');

    const breakdown = row.breakdowns.find(
        (b) => b.percentage_type.name.toLowerCase() === dbKey,
    );
    return breakdown ? breakdown.total_earning : 0;
};

// Function to calculate Driver Earning for a single row
const calculateDriverEarning = (row: DetailedRevenueRow): number => {
    const totalBreakdowns = row.breakdowns.reduce((sum, b) => sum + b.total_earning, 0);
    return Math.max(0, row.amount - totalBreakdowns);
};

// --- 5. Computed Properties for Grand Totals ---
const grandTotals = computed(() => {
    let totalAmount = 0;

    // FIX 1: Use type assertion to resolve TypeScript compilation error
    let totalBreakdowns = {} as Record<string, number>;

    let totalDriverEarning = 0;

    // Initialize breakdown totals using the CLEAN display names
    props.breakdownTypes.forEach(type => {
        // The key is the clean type name (e.g., 'Tax')
        totalBreakdowns[type] = 0;
    });

    props.details.forEach(row => {
        totalAmount += row.amount;

        props.breakdownTypes.forEach(type => {
            // FIX 2: Call helper function with the CLEAN display name (e.g., 'Tax')
            const breakdownValue = getBreakdownAmount(row, type);
            // Sum to the corresponding clean type name key
            totalBreakdowns[type] += breakdownValue;
        });

        totalDriverEarning += calculateDriverEarning(row);
    });

    return {
        totalAmount: formatCurrency(totalAmount),
        breakdowns: Object.keys(totalBreakdowns).map(key => ({
            name: key,
            value: formatCurrency(totalBreakdowns[key]),
        })),
        totalDriverEarning: formatCurrency(totalDriverEarning),
    };
});


// --- 6. Define Columns for DataTable ---
const detailColumns = computed<ColumnDef<DetailedRevenueRow>[]>(() => {
    const columns: ColumnDef<DetailedRevenueRow>[] = [
        {
            accessorKey: 'invoice_no',
            header: 'Invoice No.',
            minSize: 100,
            cell: (info) => h(Badge, { variant: 'outline' }, () => info.getValue()),
        },
        {
            accessorKey: 'payment_date',
            header: 'Date/Time',
            minSize: 150,
            cell: (info) => {
                const date = info.getValue() as string;
                // Assuming payment_date is a valid timestamp
                return new Date(date).toLocaleString('en-US', {
                    month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true
                });
            },
        },
        {
            accessorKey: 'amount',
            header: 'Total Trip Amount',
            minSize: 150,
            cell: (info) => formatCurrency(info.getValue() as number),
        },
    ];

    // Dynamically add breakdown columns
    props.breakdownTypes.forEach(type => {
        // The accessorKey is just a unique column identifier; it doesn't map directly to a simple object key here.
        const internalKey = type.toLowerCase().replace(/\s/g, '_');

        columns.push({
            // Use the internal key for the accessor
            accessorKey: internalKey,
            header: type, // Use the clean title (e.g., 'Tax', 'Bank Fee')
            minSize: 100,
            cell: ({ row }) => {
                // FIX 3: Call helper function with the CLEAN display name (type) for the lookup
                const amount = getBreakdownAmount(row.original, type);
                return formatCurrency(amount);
            },
        });
    });

    // Add Driver Earning column
    columns.push({
        accessorKey: 'driver_earning',
        header: 'Driver Earning',
        minSize: 150,
        cell: ({ row }) => {
            const earning = calculateDriverEarning(row.original);
            return formatCurrency(earning);
        },
    });

    return columns;
});

// --- 7. Go Back Function ---
const goBack = () => {
    // Navigate back to the index page using the preserved filters
    const queryParams: Record<string, string> = {
        tab: props.filters.tab,
        period: props.filters.period,
        driver: props.filters.driver_id || 'all', // Use driver_id from the details filters
        service: 'Trips',
    };

    if (props.filters.franchise) {
        queryParams.franchise = props.filters.franchise;
    } else if (props.filters.branch) {
        queryParams.branch = props.filters.branch;
    }

    router.get(superAdmin.driverreport().url, queryParams);
};
</script>

<template>
    <Head title="Driver Transaction Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div
                class="relative rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
            >
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Button variant="outline" class="mb-4 sm:mb-0" @click="goBack">
                            ← Back to Report
                        </Button>
                        <h2 class="font-mono text-2xl font-bold mt-2">
                            Driver Transaction Details
                        </h2>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-primary">
                            Driver: {{ props.driver.name }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Period: {{ props.periodLabel }}
                        </p>
                    </div>
                </div>

                <div class="mb-8 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg shadow-inner">
                    <div class="col-span-2 sm:col-span-1 border-r pr-4 sm:pr-2">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Total Trips Amount
                        </p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">
                            {{ grandTotals.totalAmount }}
                        </p>
                    </div>

                    <div v-for="(item, index) in grandTotals.breakdowns" :key="item.name" class="col-span-2 sm:col-span-1" :class="{'border-r pr-4 sm:pr-2': index < grandTotals.breakdowns.length - 1}">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Total {{ item.name }}
                        </p>
                        <p class="text-xl font-bold">
                            {{ item.value }}
                        </p>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Total Driver Earning
                        </p>
                        <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ grandTotals.totalDriverEarning }}
                        </p>
                    </div>
                </div>


                <DataTable
                    :columns="detailColumns"
                    :data="props.details"
                    search-placeholder="Search by Invoice No."
                />
            </div>
        </div>
    </AppLayout>
</template>
