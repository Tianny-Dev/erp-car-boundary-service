<script setup lang="ts">
import RevenueBreakDownPieChart from '@/components/finance/charts/revenue-management/RevenueBreakDownPieChart.vue';
import RevenuePaymentOptionsBreakDownPieChart from '@/components/finance/charts/revenue-management/RevenuePaymentOptionsBreakDownPieChart.vue';
import RevenueTrendSparkLine from '@/components/finance/charts/revenue-management/RevenueTrendSparkLine.vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import finance from '@/routes/finance';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { FileDown } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

// -------------------------
// Interfaces
// -------------------------
interface Revenue {
  id: number;
  invoice_no: string;
  amount: number;
  currency: string;
  service_type: string;
  payment_date: string | null;
  notes: string | null;
  status: string | null;
  franchise: string | null;
  branch: string | null;
  payment_option: string | null;
}

interface RevenuesPaginator {
  current_page: number;
  data: Revenue[];
  first_page_url: string | null;
  from: number | null;
  last_page: number;
  last_page_url: string | null;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number | null;
  total: number;
}

interface Props {
  revenues: RevenuesPaginator;

  revenueServiceTypeBreakdownData: { name: string; total: number }[];
  revenueByPaymentOption: { name: string; total: number }[];

  revenueTrendData: { year: number; revenue: number }[];
}

// -------------------------
// Props and State
// -------------------------
const {
  revenueServiceTypeBreakdownData,
  revenueByPaymentOption,
  revenues,
  revenueTrendData,
} = defineProps<Props>();
const paginator = ref(revenues);

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Revenue Management', href: finance.revenueManagement().url },
];

const globalFilter = ref('');
const pageSize = ref('10');

// Dialog
const selectedRevenue = ref<Revenue | null>(null);
const dialogOpen = ref(false);

const viewRevenue = (revenue: Revenue) => {
  selectedRevenue.value = revenue;
  dialogOpen.value = true;
};

// -------------------------
// Computed: Filtered Data
// -------------------------
const filteredData = computed(() => {
  if (!globalFilter.value) return paginator.value.data;
  const search = globalFilter.value.toLowerCase();

  return paginator.value.data.filter((item) =>
    Object.values(item)
      .filter((v) => v !== null && v !== undefined)
      .some((v) => v.toString().toLowerCase().includes(search)),
  );
});

// Computed: Pagination links without Previous/Next
const paginationLinks = computed(() => {
  return paginator.value.links.filter(
    (link) => link.label !== 'Previous' && link.label !== 'Next',
  );
});

// -------------------------
// Watchers
// -------------------------
watch(
  () => revenues,
  (newRevenues) => {
    paginator.value = newRevenues;
  },
  { deep: true },
);

watch(pageSize, (newSize) => {
  router.get(
    paginator.value.path,
    { per_page: newSize },
    {
      preserveState: true,
      preserveScroll: true,
    },
  );
});

// -------------------------
// Helpers
// -------------------------
const getStatusVariant = (status: string | null) => {
  switch (status) {
    case 'Flagged':
      return 'destructive';
    case 'Received':
      return 'default';
    case 'Pending':
      return 'outline';
    default:
      return 'secondary';
  }
};

const exportPDF = () => {
  console.log('Exporting PDF...');
};

const goToPage = (pageUrl: string | null) => {
  if (pageUrl) {
    router.get(
      pageUrl,
      {},
      {
        preserveState: true,
        preserveScroll: true,
      },
    );
  }
};
</script>

<template>
  <Head title="Revenue Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-white p-4 shadow"
    >
      <!-- Header -->
      <div class="flex items-center justify-between border-b pb-4">
        <h1 class="text-xl font-semibold">Revenue Records</h1>
        <Button @click="exportPDF" class="bg-red-600 hover:bg-red-700">
          <FileDown class="mr-2 h-4 w-4" /> Export PDF
        </Button>
      </div>

      <!-- Controls -->
      <div class="flex items-center justify-between border-b pb-4">
        <div class="flex items-center gap-2">
          <span class="text-sm text-gray-600">Search:</span>
          <Input
            v-model="globalFilter"
            placeholder="Search revenues..."
            class="w-48"
          />
        </div>
      </div>

      <!-- ShadCN Table -->
      <div class="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Invoice No</TableHead>
              <TableHead>Service Type</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Payment Date</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Franchise</TableHead>
              <TableHead>Branch</TableHead>
              <TableHead>Payment Option</TableHead>
              <TableHead>Action</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-if="filteredData.length === 0">
              <TableCell colspan="9" class="py-6 text-center text-gray-500">
                No results found.
              </TableCell>
            </TableRow>

            <TableRow
              v-for="record in filteredData"
              :key="record.id"
              class="hover:bg-gray-50"
            >
              <TableCell class="font-medium">{{ record.invoice_no }}</TableCell>
              <TableCell>{{ record.service_type }}</TableCell>
              <TableCell class="font-medium">
                {{ record.currency }} {{ record.amount.toLocaleString() }}
              </TableCell>
              <TableCell>{{ record.payment_date || '—' }}</TableCell>
              <TableCell>
                <Badge
                  :variant="getStatusVariant(record.status)"
                  class="px-2 py-1"
                >
                  {{ record.status }}
                </Badge>
              </TableCell>
              <TableCell>{{ record.franchise || '—' }}</TableCell>
              <TableCell>{{ record.branch || '—' }}</TableCell>
              <TableCell>{{ record.payment_option || '—' }}</TableCell>
              <TableCell>
                <Button
                  size="sm"
                  variant="default"
                  @click="viewRevenue(record)"
                >
                  View
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between pt-4">
        <span class="text-sm text-gray-600">
          Showing {{ paginator.from || 0 }} to {{ paginator.to || 0 }} of
          {{ paginator.total }} entries
        </span>

        <Pagination
          :items-per-page="paginator.per_page"
          :total="paginator.total"
          :default-page="paginator.current_page"
          class="w-auto"
        >
          <PaginationContent>
            <PaginationPrevious
              :disabled="!paginator.prev_page_url"
              @click="goToPage(paginator.prev_page_url)"
            />

            <template v-for="(link, index) in paginationLinks" :key="index">
              <PaginationItem
                v-if="!isNaN(Number(link.label))"
                :value="Number(link.label)"
              >
                <Button
                  variant="ghost"
                  size="sm"
                  :class="{ 'bg-gray-100': link.active }"
                  :disabled="!link.url"
                  @click="goToPage(link.url)"
                >
                  {{ link.label }}
                </Button>
              </PaginationItem>
              <PaginationEllipsis v-else-if="link.label.includes('...')" />
            </template>

            <PaginationNext
              :disabled="!paginator.next_page_url"
              @click="goToPage(paginator.next_page_url)"
            />
          </PaginationContent>
        </Pagination>
      </div>

      <!-- Charts Section -->
      <div class="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader
            ><CardTitle>Revenue Breakdown by Type</CardTitle></CardHeader
          >
          <CardContent>
            <RevenueBreakDownPieChart
              :data="revenueServiceTypeBreakdownData"
              category="total"
              title="Monthly Revenue"
            />
          </CardContent>
        </Card>

        <Card>
          <CardHeader
            ><CardTitle
              >Revenue Breakdown by Payment Options</CardTitle
            ></CardHeader
          >
          <CardContent>
            <RevenuePaymentOptionsBreakDownPieChart
              :data="revenueByPaymentOption"
              category="total"
              title="Monthly Revenue"
            />
          </CardContent>
        </Card>
      </div>
      <Card>
        <CardHeader><CardTitle>Net Profit Trend</CardTitle></CardHeader>
        <CardContent>
          <RevenueTrendSparkLine
            :data="
              revenueTrendData.map((item) => ({
                year: item.year,
                value: item.revenue,
              }))
            "
            label="Revenue"
            :colors="['#3b82f6']"
            :y-formatter="
              (val) =>
                `₱ ${val.toLocaleString(undefined, { minimumFractionDigits: 2 })}`
            "
          />
        </CardContent>
      </Card>
    </div>

    <Dialog v-model:open="dialogOpen">
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Revenue Details</DialogTitle>
          <DialogDescription>
            Detailed information for invoice
            <strong>{{ selectedRevenue?.invoice_no }}</strong
            >.
          </DialogDescription>
        </DialogHeader>

        <div class="mt-2 space-y-2">
          <p><strong>Invoice No:</strong> {{ selectedRevenue?.invoice_no }}</p>
          <p>
            <strong>Service Type:</strong> {{ selectedRevenue?.service_type }}
          </p>
          <p>
            <strong>Amount:</strong> {{ selectedRevenue?.currency }}
            {{ selectedRevenue?.amount.toLocaleString() }}
          </p>
          <p>
            <strong>Payment Date:</strong>
            {{ selectedRevenue?.payment_date || '—' }}
          </p>
          <p><strong>Status:</strong> {{ selectedRevenue?.status || '—' }}</p>
          <p>
            <strong>Franchise:</strong> {{ selectedRevenue?.franchise || '—' }}
          </p>
          <p><strong>Branch:</strong> {{ selectedRevenue?.branch || '—' }}</p>
          <p>
            <strong>Payment Option:</strong>
            {{ selectedRevenue?.payment_option || '—' }}
          </p>
          <p><strong>Notes:</strong> {{ selectedRevenue?.notes || '—' }}</p>
        </div>

        <DialogFooter>
          <Button @click="dialogOpen = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
