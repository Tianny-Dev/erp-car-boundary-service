<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import owner from '@/routes/owner';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
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

const props = defineProps<{ drivers: Driver[] }>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Driver Management', href: owner.drivers.index().url },
];

const globalFilter = ref('');
const statusFilter = ref('all');

const filteredDrivers = computed(() => {
  let filtered = props.drivers;

  if (statusFilter.value !== 'all') {
    filtered = filtered.filter((d) => d.status === statusFilter.value);
  }

  if (globalFilter.value) {
    const term = globalFilter.value.toLowerCase();
    filtered = filtered.filter((d) =>
      [d.name, d.username, d.email].some((f) => f.toLowerCase().includes(term)),
    );
  }

  return filtered;
});

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'active':
      return 'default';
    case 'pending':
      return 'secondary';
    case 'retired':
      return 'destructive';
    default:
      return 'secondary';
  }
};

const updatingId = ref<number | null>(null);

const toggleStatus = (id: number) => {
  updatingId.value = id;

  // Show a loading toast
  const toastId = toast.loading('Updating driver status...');

  router.put(
    `/owner/drivers/${id}`,
    {},
    {
      onSuccess: () => {
        toast.success('Driver status updated successfully!', { id: toastId });
      },
      onError: () => {
        toast.error('Failed to update driver status.', { id: toastId });
      },
      onFinish: () => {
        updatingId.value = null;
      },
    },
  );
};
</script>

<template>
  <Head title="Driver Management" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div>
        <h1 class="mb-1 text-3xl font-bold">Driver Management</h1>
        <p class="text-gray-600">Manage all franchise drivers</p>
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

        <Select v-model="statusFilter">
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
        </Select>
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
              v-for="driver in filteredDrivers"
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
              <TableCell>
                <Button
                  size="sm"
                  variant="outline"
                  :disabled="updatingId === driver.id"
                  @click="toggleStatus(driver.id)"
                >
                  <Spinner
                    v-if="updatingId === driver.id"
                    class="mr-2 h-4 w-4"
                  />
                  <span v-else>Toggle Status</span>
                </Button>
              </TableCell>
            </TableRow>

            <TableRow v-if="filteredDrivers.length === 0">
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
    </div>
  </AppLayout>
</template>
