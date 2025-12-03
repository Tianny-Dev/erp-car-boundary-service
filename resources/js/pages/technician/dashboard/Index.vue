<script setup lang="ts">
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
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import technician from '@/routes/technician';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Check, Clock, Cog, Eye, Wrench } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import { Badge } from '@/components/ui/badge';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import FullCalendar from '@fullcalendar/vue3';

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
  driver_email: string | null;
  driver_phone: string | null;

  technician: string | null;

  franchise_id: number | null;
  branch_id: number | null;

  status: string;

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

// Filtered Maintenances
const filteredMaintenances = computed(() => {
  let filtered = paginator.value.data;

  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(
      (v) => v.status.toLowerCase() === statusFilter.value,
    );
  }

  if (globalFilter.value) {
    const term = globalFilter.value.toLowerCase();
    filtered = filtered.filter((v) =>
      [
        v.vehicle_plate,
        v.driver_name,
        v.driver_email,
        v.driver_phone,
        v.maintenance_type,
        v.maintenance_date,
      ].some((f) => String(f).toLowerCase().includes(term)),
    );
  }

  return filtered;
});

// Status badge style
const getStatusVariant = (status: string) => {
  switch (status) {
    case 'active':
      return 'default';
    case 'pending':
      return 'secondary';
    case 'inactive':
      return 'destructive';
    default:
      return 'secondary';
  }
};

// Pagination Helpers
const paginationLinks = computed(() =>
  paginator.value.links.filter(
    (link) => link.label !== 'Previous' && link.label !== 'Next',
  ),
);

const goToPage = (url: string | null) => {
  if (!url) return;
  router.get(url, {}, { preserveState: true, preserveScroll: true });
};

const selectedMaintenance = ref<MaintenanceJob | null>(null);
const dialogOpen = ref(false);
const viewMaintenance = (maintenance: MaintenanceJob) => {
  selectedMaintenance.value = maintenance;
  dialogOpen.value = true;
};

const calendarEvents = computed(() =>
  paginator.value.data.map((m) => ({
    id: String(m.id),
    title: `${m.maintenance_type} — ${m.vehicle_plate}`,
    start: m.maintenance_date,
    // end: m.next_maintenance_date || undefined,
    allDay: true,
    extendedProps: m,
  })),
);

const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  selectable: true,
  editable: false,
  events: calendarEvents.value,
  eventClick(info) {
    selectedMaintenance.value = info.event.extendedProps;
    dialogOpen.value = true;
  },
}));
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
          </select>
        </div>

        <!-- Maintenance Table -->
        <div class="rounded-lg border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Plate</TableHead>
                <TableHead>Driver</TableHead>
                <TableHead>Email</TableHead>
                <TableHead>Phone</TableHead>
                <TableHead>Technician</TableHead>
                <TableHead>Maintenance Type</TableHead>
                <TableHead>Maintenance Date</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>

            <TableBody>
              <TableRow
                v-for="maintenance in filteredMaintenances"
                :key="maintenance.id"
                class="hover:bg-muted/50"
              >
                <TableCell>{{ maintenance.vehicle_plate }}</TableCell>
                <TableCell>{{ maintenance.driver_name }}</TableCell>
                <TableCell>{{ maintenance.driver_email }}</TableCell>
                <TableCell>{{ maintenance.driver_phone }}</TableCell>
                <TableCell>{{ maintenance.technician }}</TableCell>
                <TableCell>{{ maintenance.maintenance_type }}</TableCell>
                <TableCell>{{ maintenance.maintenance_date }}</TableCell>
                <TableCell>
                  <Badge :variant="getStatusVariant(maintenance.status)">
                    {{ maintenance.status }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <TooltipProvider>
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <Button
                          size="sm"
                          variant="outline"
                          @click="viewMaintenance(maintenance)"
                          class="cursor-pointer"
                        >
                          <Eye />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>
                        <p>View</p>
                      </TooltipContent>
                    </Tooltip>
                  </TooltipProvider>
                </TableCell>
              </TableRow>

              <TableRow v-if="filteredMaintenances.length === 0">
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

        <!-- Calendar View Section -->
        <div class="mt-8 rounded-lg border p-4">
          <h2 class="mb-4 text-xl font-semibold">
            Scheduled Maintenance Calendar
          </h2>

          <FullCalendar
            :events="calendarEvents"
            :options="calendarOptions"
            height="650px"
          />
        </div>
      </div>
      <Dialog v-model:open="dialogOpen">
        <DialogContent class="sm:max-w-lg">
          <DialogHeader>
            <DialogTitle>Maintenance Details</DialogTitle>
            <DialogDescription>
              Job for:
              <strong>{{ selectedMaintenance?.maintenance_type }}</strong
              >.
            </DialogDescription>
          </DialogHeader>

          <div class="mt-2 space-y-2" v-if="selectedMaintenance">
            <Badge :variant="getStatusVariant(selectedMaintenance.status)">
              {{ selectedMaintenance.status }}
            </Badge>
            <p>
              <strong>Description:</strong>
              {{ selectedMaintenance.description || '—' }}
            </p>

            <p>
              <strong>Maintenance Date:</strong>
              {{ selectedMaintenance.maintenance_date || '—' }}
            </p>

            <p>
              <strong>Next Maintenance:</strong>
              {{ selectedMaintenance.next_maintenance_date || '—' }}
            </p>

            <p>
              <strong>Vehicle Plate:</strong>
              {{ selectedMaintenance.vehicle_plate || '—' }}
            </p>

            <p>
              <strong>Driver Name:</strong>
              {{ selectedMaintenance.driver_name || '—' }}
            </p>

            <p>
              <strong>Driver Email:</strong>
              {{ selectedMaintenance.driver_email || '—' }}
            </p>

            <p>
              <strong>Driver Phone:</strong>
              {{ selectedMaintenance.driver_phone || '—' }}
            </p>

            <p>
              <strong>Technician:</strong>
              {{ selectedMaintenance.technician || '—' }}
            </p>

            <p>
              <strong>Franchise ID:</strong>
              {{ selectedMaintenance.franchise_id || '—' }}
            </p>

            <p>
              <strong>Branch ID:</strong>
              {{ selectedMaintenance.branch_id || '—' }}
            </p>

            <p>
              <strong>Created At:</strong>
              {{ selectedMaintenance.created_at || '—' }}
            </p>
          </div>

          <DialogFooter>
            <Button @click="dialogOpen = false">Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
