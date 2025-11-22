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

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Maintenance Requests',
    href: owner.maintenanceRequests.index().url,
  },
];

const globalFilter = ref('');
const pageSize = ref('10');

// const filteredData = computed(() => {
//   if (!globalFilter.value) return paginator.value.data;
//   const search = globalFilter.value.toLowerCase();

//   return paginator.value.data.filter((item) =>
//     Object.values(item)
//       .filter((v) => v !== null && v !== undefined)
//       .some((v) => v.toString().toLowerCase().includes(search)),
//   );
// });

const paginationLinks = computed(() => {
  return paginator.value.links.filter(
    (link) => link.label !== 'Previous' && link.label !== 'Next',
  );
});

watch(
  () => requests,
  (newRequests) => {
    paginator.value = newRequests;
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
              <TableHead>Vehicle</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Description</TableHead>
              <TableHead>Date</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow
              v-for="request in paginator.data"
              :key="request.id"
              class="hover:bg-muted/50"
            >
              <TableCell>{{ request.vehicle?.plate_number }}</TableCell>
              <TableCell>{{ request.maintenance_type }}</TableCell>
              <TableCell>{{ request.description }}</TableCell>
              <TableCell>{{ request.maintenance_date }}</TableCell>
            </TableRow>

            <TableRow v-if="paginator.data.length === 0">
              <TableCell
                colspan="10"
                class="py-6 text-center text-muted-foreground"
              >
                No results found.
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
    </div>
  </AppLayout>
</template>
