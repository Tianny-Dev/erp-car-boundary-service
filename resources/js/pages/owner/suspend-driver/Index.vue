<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
import owner from '@/routes/owner';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Edit, Eye, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

interface Driver {
  id: number;
  name: string;
  username: string;
  email: string;
  phone: string;
  status: string;
  region: string;
  province: string;
  city: string;
  barangay: string;
}

interface Status {
  id: number;
  name: string;
}

interface DriversPaginator {
  current_page: number;
  data: Driver[];
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
  drivers: DriversPaginator;
  statuses: Status[];
}

const { drivers, statuses } = defineProps<Props>();
const paginator = ref(drivers);

// Dialog
const selectedDriver = ref<Driver | null>(null);
const dialogOpen = ref(false);

const viewDriver = (driver: Driver) => {
  selectedDriver.value = driver;
  dialogOpen.value = true;
};

// -------------------------
// Watcher: update paginator when props change
// -------------------------
watch(
  () => drivers,
  (newDrivers) => {
    paginator.value = newDrivers;
  },
  { deep: true },
);

// -------------------------
// Breadcrumbs
// -------------------------
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Suspend Driver', href: owner.drivers.index().url },
];

// -------------------------
// Filters / Search (server-side)
// -------------------------
const globalFilter = ref('');
const statusFilter = ref('all');

watch([statusFilter, globalFilter], () => {
  router.get(
    paginator.value.path,
    {
      status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
      search: globalFilter.value || undefined,
      per_page: paginator.value.per_page,
    },
    { preserveState: true, preserveScroll: true },
  );
});

// -------------------------
// Pagination helper
// -------------------------
const paginationLinks = computed(() => {
  return paginator.value.links || [];
});

const goToPage = (url: string | null) => {
  if (!url) return;
  router.get(
    url,
    {
      status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
      search: globalFilter.value || undefined,
    },
    { preserveState: true, preserveScroll: true },
  );
};

// -------------------------
// Helpers
// -------------------------
const getStatusVariant = (status: string) => {
  switch (status) {
    case 'active':
      return 'default';
    case 'pending':
      return 'secondary';
    case 'retired':
      return 'destructive';
    case 'suspended':
      return 'destructive';
    default:
      return 'secondary';
  }
};

// -------------------------
// Toggle driver status
// -------------------------
const updatingId = ref<number | null>(null);

const updateDriverStatus = (driverId: number, statusId: number) => {
  updatingId.value = driverId;
  const toastId = toast.loading('Updating driver status...');

  router.put(
    `/owner/suspend-drivers/${driverId}`,
    { status_id: statusId },
    {
      onSuccess: () => toast.success('Driver status updated!', { id: toastId }),
      onError: () =>
        toast.error('Failed to update driver status.', { id: toastId }),
      onFinish: () => (updatingId.value = null),
    },
  );
};
</script>

<template>
  <Head title="Suspend Driver" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div>
        <h1 class="mb-1 text-3xl font-bold">Suspend Driver</h1>
        <p class="text-gray-600">Suspend drivers on your franchise</p>
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

      <!-- Table -->
      <div class="overflow-x-auto rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Name</TableHead>
              <TableHead>Username</TableHead>
              <TableHead>Email</TableHead>
              <TableHead>Phone</TableHead>
              <TableHead>Region</TableHead>
              <TableHead>Province</TableHead>
              <TableHead>City</TableHead>
              <TableHead>Barangay</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow
              v-for="driver in paginator.data"
              :key="driver.id"
              class="hover:bg-muted/50"
            >
              <TableCell>{{ driver.name }}</TableCell>
              <TableCell>{{ driver.username }}</TableCell>
              <TableCell>{{ driver.email }}</TableCell>
              <TableCell>{{ driver.phone }}</TableCell>
              <TableCell>{{ driver.region }}</TableCell>
              <TableCell>{{ driver.province }}</TableCell>
              <TableCell>{{ driver.city }}</TableCell>
              <TableCell>{{ driver.barangay }}</TableCell>
              <TableCell>
                <Badge :variant="getStatusVariant(driver.status)">
                  {{ driver.status }}
                </Badge>
              </TableCell>
              <TableCell class="flex gap-2">
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger as-child>
                      <Button
                        size="sm"
                        variant="outline"
                        @click="viewDriver(driver)"
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

                <DropdownMenu>
                  <DropdownMenuTrigger as-child
                    ><Button size="sm" variant="outline" class="cursor-pointer">
                      <Edit /> </Button
                  ></DropdownMenuTrigger>
                  <DropdownMenuContent>
                    <DropdownMenuLabel>Action</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                      v-for="status in statuses"
                      :key="status.id"
                      @click="updateDriverStatus(driver.id, status.id)"
                    >
                      {{
                        status.name.charAt(0).toUpperCase() +
                        status.name.slice(1)
                      }}
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
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

    <Dialog v-model:open="dialogOpen">
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Driver's Information</DialogTitle>
          <DialogDescription>
            Detailed information for driver
            <strong>{{ selectedDriver?.name }}</strong
            >.
          </DialogDescription>
        </DialogHeader>

        <div class="mt-2 space-y-2">
          <p><strong>ID:</strong> {{ selectedDriver?.id }}</p>
          <p><strong>Username:</strong> {{ selectedDriver?.username }}</p>
          <p><strong>Email:</strong> {{ selectedDriver?.email }}</p>
          <p><strong>Phone:</strong> {{ selectedDriver?.phone }}</p>
          <p><strong>Region:</strong> {{ selectedDriver?.region }}</p>
          <p><strong>Province:</strong> {{ selectedDriver?.province }}</p>
          <p><strong>City:</strong> {{ selectedDriver?.city }}</p>
          <p><strong>Barangay:</strong> {{ selectedDriver?.barangay }}</p>
          <p><strong>Status:</strong> {{ selectedDriver?.status || '—' }}</p>
        </div>

        <DialogFooter>
          <Button @click="dialogOpen = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
