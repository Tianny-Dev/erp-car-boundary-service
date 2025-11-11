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
        <table class="min-w-full text-left text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="px-4 py-3 font-semibold">Name</th>
              <th class="px-4 py-3 font-semibold">Username</th>
              <th class="px-4 py-3 font-semibold">Email</th>
              <th class="px-4 py-3 font-semibold">Phone</th>
              <th class="px-4 py-3 font-semibold">Region</th>
              <th class="px-4 py-3 font-semibold">Province</th>
              <th class="px-4 py-3 font-semibold">City</th>
              <th class="px-4 py-3 font-semibold">Barangay</th>
              <th class="px-4 py-3 font-semibold">Status</th>
              <th class="px-4 py-3 font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="driver in filteredDrivers"
              :key="driver.id"
              class="border-t hover:bg-gray-50"
            >
              <td class="px-4 py-2">{{ driver.name }}</td>
              <td class="px-4 py-2">{{ driver.username }}</td>
              <td class="px-4 py-2">{{ driver.email }}</td>
              <td class="px-4 py-2">{{ driver.phone }}</td>
              <td class="px-4 py-2">{{ driver.region }}</td>
              <td class="px-4 py-2">{{ driver.province }}</td>
              <td class="px-4 py-2">{{ driver.city }}</td>
              <td class="px-4 py-2">{{ driver.barangay }}</td>
              <td class="px-4 py-2">
                <Badge :variant="getStatusVariant(driver.status)">
                  {{ driver.status }}
                </Badge>
              </td>
              <td class="px-4 py-2">
                <Button
                  size="sm"
                  variant="outline"
                  class="cursor-pointer"
                  :disabled="updatingId === driver.id"
                  @click="toggleStatus(driver.id)"
                >
                  <span v-if="updatingId === driver.id"><Spinner /></span>
                  <span v-else>Toggle Status</span>
                </Button>
              </td>
            </tr>

            <tr v-if="filteredDrivers.length === 0">
              <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                No results found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
