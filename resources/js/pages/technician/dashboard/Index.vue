<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Table,
  // TableBody,
  // TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import technician from '@/routes/technician';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Check, Clock, Cog, Wrench } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const page = usePage();
const user = page.props.auth.user;

interface MaintenanceJob {
  id: number;
  maintenance_type: string;
  description: string;
  maintenance_date: string;
  next_maintenance_date: string | null;

  vehicle_plate: string | null;
  driver_name: string | null;
  driver_phone: string | null;

  franchise_id: number | null;
  branch_id: number | null;

  created_at: string;
}

interface MaintenancePaginator {
  current_page: number;
  data: MaintenanceJob[];
  first_page_url: string | null;
  from: number | null;
  last_page: number;
  last_page_url: string | null;
  links: Array<{ url: string | null; label: string; active: boolean }>;
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number | null;
  total: number;
}

const props = defineProps<{
  franchiseExists: boolean;
  maintenance: MaintenancePaginator;
}>();

const paginator = ref(props.maintenance);

// -------------------------
// Watch for prop updates
// -------------------------
watch(
  () => props.maintenance,
  (newVal) => {
    paginator.value = newVal;
  },
  { deep: true },
);

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: technician.dashboard().url,
  },
];

// Filters
const globalFilter = ref('');
const statusFilter = ref('all');

// Status badge style
// const getStatusVariant = (status: string) => {
//   switch (status) {
//     case 'active':
//       return 'default';
//     case 'pending':
//       return 'secondary';
//     case 'retired':
//       return 'destructive';
//     case 'suspended':
//       return 'outline';
//     default:
//       return 'secondary';
//   }
// };
</script>

<template>
  <Head title="Technician Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
      <!-- Header -->
      <div class="mb-2">
        <h1 class="mb-2 text-3xl font-bold">Dashboard</h1>
        <p class="text-gray-600">
          Welcome back, <span class="font-semibold">{{ user.name }}</span
          >! Here are your current maintenance assignments.
        </p>
      </div>

      <!-- No Franchise -->
      <div v-if="!franchiseExists">
        <p>No franchise assigned. Dashboard data is not available.</p>
      </div>

      <!-- Stats Cards -->
      <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Active Service Tickets -->
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium"
              >Active Service Tickets</CardTitle
            >
            <Wrench class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">2</div>
            <p class="text-xs text-muted-foreground">Active Service/s</p>
          </CardContent>
        </Card>

        <!-- Pending Requests -->
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium">Pending Requests</CardTitle>
            <Clock class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">5</div>
            <p class="text-xs text-muted-foreground">Pending Requests</p>
          </CardContent>
        </Card>

        <!-- Completed Jobs This Week -->
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium">Completed Jobs</CardTitle>
            <Check class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">10</div>
            <p class="text-xs text-muted-foreground">This Week</p>
          </CardContent>
        </Card>

        <!-- Parts Inventory Status -->
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium"
              >Parts Inventory Status</CardTitle
            >
            <Cog class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">10</div>
            <p class="text-xs text-muted-foreground">Status</p>
          </CardContent>
        </Card>
      </div>

      <div class="space-y-6 py-6">
        <!-- Header -->
        <div class="mb-2">
          <h1 class="mb-2 text-3xl font-bold">Summary of Jobs</h1>
        </div>

        <!-- Filters -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center">
          <input
            v-model="globalFilter"
            placeholder="Search..."
            class="w-full rounded-md border px-3 py-2"
          />
          <select
            v-model="statusFilter"
            class="w-full rounded-md border px-3 py-2 md:w-48"
          >
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="suspended">Suspended</option>
            <option value="retired">Retired</option>
          </select>
        </div>

        <!-- Maintenance Table -->
        <div class="rounded-lg border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Plate</TableHead>
                <TableHead>VIN</TableHead>
                <TableHead>Brand</TableHead>
                <TableHead>Model</TableHead>
                <TableHead>Color</TableHead>
                <TableHead>Year</TableHead>
                <TableHead>Driver</TableHead>
                <TableHead>Technician</TableHead>
                <TableHead>Maintenance Type</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>

            <TableBody>
              <TableRow class="hover:bg-muted/50"> </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
