<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import owner from '@/routes/owner';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';

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
import { Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Request {
  id: number;
  vehicle: Vehicle | null;
  maintenance_type: string;
  description: string;
  maintenance_date: string | null;
  next_maintenance_date: string | null;
}

interface Vehicle {
  id: number;
  plate_number: string;
  vin: string;
  brand: string;
  model: string;
  color: string;
  year: number;
}

interface RequestsPaginator {
  current_page: number;
  data: Request[];
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
  requests: RequestsPaginator;
}

const { requests } = defineProps<Props>();
const paginator = ref(requests);

// -------------------------
// Watcher: update paginator when props change
// -------------------------
watch(
  () => requests,
  (newRequests) => {
    paginator.value = newRequests;
  },
  { deep: true },
);

// -------------------------
// Breadcrumbs
// -------------------------
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Maintenance Requests',
    href: owner.maintenanceRequests.index().url,
  },
];

// -------------------------
// Filters / Search
// -------------------------
const globalFilter = ref('');

// -------------------------
// Dialog
// -------------------------
// const selectedContract = ref<Request | null>(null);
// const showDepositDialog = ref(false);

// const filteredData = computed(() => {
//   if (!globalFilter.value) return paginator.value.data;
//   const search = globalFilter.value.toLowerCase();

// const openDepositDialog = (contract: Contract) => {
//   selectedContract.value = contract;
//   showDepositDialog.value = true;
// };

// const handleDepositAction = (action: 'approved' | 'flagged') => {
//   if (selectedContract.value) {
//     const index = paginator.value.data.findIndex(
//       (item) => item.id === selectedContract.value!.id,
//     );
//     if (index !== -1) {
//       paginator.value.data[index].depositStatus = action;
//     }
//   }
//   showDepositDialog.value = false;
// };

// -------------------------
// Computed: Filtered data for client-side search
// -------------------------
const filteredData = computed(() => {
  if (!globalFilter.value) return paginator.value.data;
  const search = globalFilter.value.toLowerCase();

  return paginator.value.data.filter((item) => {
    // Flatten searchable fields including vehicle
    const vehicleStr = item.vehicle
      ? [
          item.vehicle.plate_number,
          item.vehicle.vin,
          item.vehicle.brand,
          item.vehicle.model,
          item.vehicle.color,
          item.vehicle.year?.toString(),
        ].join(' ')
      : '';

    return (
      item.description.toLowerCase().includes(search) ||
      item.maintenance_type.toLowerCase().includes(search) ||
      vehicleStr.toLowerCase().includes(search)
    );
  });
});

// -------------------------
// Pagination links without Previous/Next
// -------------------------
const paginationLinks = computed(() =>
  paginator.value.links.filter(
    (link) => link.label !== 'Previous' && link.label !== 'Next',
  ),
);

const goToPage = (pageUrl: string | null) => {
  if (pageUrl) {
    router.get(
      pageUrl,
      {
        search: globalFilter.value || undefined,
      },
      {
        preserveState: true,
        preserveScroll: true,
      },
    );
  }
};

// Watch filters / search and reload table
watch([globalFilter], () => {
  router.get(
    paginator.value.path,
    {
      search: globalFilter.value || undefined,
      per_page: paginator.value.per_page,
    },
    { preserveState: true, preserveScroll: true },
  );
});
</script>

<template>
  <Head title="Maintenance Requests" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div>
        <h1 class="mb-1 text-3xl font-bold">Maintenance Requests</h1>
        <p class="text-gray-600">Track response and repair status</p>
      </div>

      <!-- Filters -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center">
        <div class="relative flex-1">
          <Search
            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
          />
          <input
            v-model="globalFilter"
            placeholder="Search drivers..."
            class="w-full rounded-md border px-10 py-2"
          />
        </div>

        <!-- <Select v-model="statusFilter">
          <SelectTrigger class="w-full md:w-48">
            <SelectValue placeholder="Filter by status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Status</SelectItem>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="suspended">Suspended</SelectItem>
            <SelectItem value="retired">Retired</SelectItem>
          </SelectContent>
        </Select> -->
      </div>

      <div class="overflow-x-auto rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Brand</TableHead>
              <TableHead>Model</TableHead>
              <TableHead>Plate Number</TableHead>
              <TableHead>VIN</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Description</TableHead>
              <TableHead>Date</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-if="filteredData.length === 0">
              <TableCell colspan="6" class="py-6 text-center text-gray-500">
                No results found.
              </TableCell>
            </TableRow>

            <TableRow v-for="request in filteredData" :key="request.id">
              <TableCell>{{ request.vehicle?.brand }}</TableCell>
              <TableCell>{{ request.vehicle?.model }}</TableCell>
              <TableCell>{{ request.vehicle?.plate_number }}</TableCell>
              <TableCell>{{ request.vehicle?.vin }}</TableCell>
              <TableCell>{{ request.maintenance_type }}</TableCell>
              <TableCell>{{ request.description }}</TableCell>
              <TableCell>{{ request.maintenance_date }}</TableCell>
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

            <template v-for="link in paginationLinks" :key="link.label">
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
    </div>
  </AppLayout>
</template>
