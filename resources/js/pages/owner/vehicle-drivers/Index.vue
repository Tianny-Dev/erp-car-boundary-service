<script setup lang="ts">
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
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
import { Spinner } from '@/components/ui/spinner';

import AppLayout from '@/layouts/AppLayout.vue';
import owner from '@/routes/owner';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

interface Driver {
  id: number;
  name: string;
  email: string;
  phone: string;
}

interface Vehicle {
  id: number;
  plate_number: string;
  vin: string;
  brand: string;
  model: string;
  color: string;
  year: number;
  status_id: number;
  status_name: string;
  driver: Driver | null;
}

const props = defineProps<{ vehicles: Vehicle[]; drivers: Driver[] }>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Assign Drivers', href: owner.vehicleDrivers.index().url },
];

// filters
const globalFilter = ref('');
const statusFilter = ref('all');

const filteredVehicles = computed(() => {
  let filtered = props.vehicles;
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(
      (v) => v.status_name.toLowerCase() === statusFilter.value,
    );
  }
  if (globalFilter.value) {
    const term = globalFilter.value.toLowerCase();
    filtered = filtered.filter((v) =>
      [
        v.plate_number,
        v.vin,
        v.brand,
        v.model,
        v.color,
        v.year,
        v.driver?.name,
        v.driver?.email,
      ].some((f) => String(f).toLowerCase().includes(term)),
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
    case 'suspended':
      return 'outline';
    default:
      return 'secondary';
  }
};

// state
const showDialog = ref(false);
const editingVehicle = ref<Vehicle | null>(null);
const driverId = ref<number | null>(null);
const loading = ref(false);
const confirmVehicle = ref<Vehicle | null>(null);

const openAssignDialog = (vehicle: Vehicle) => {
  editingVehicle.value = vehicle;
  driverId.value = vehicle.driver?.id ?? null;
  showDialog.value = true;
};

const assignDriver = async () => {
  if (!editingVehicle.value) return;
  loading.value = true;

  const toastId = toast.loading('Assigning driver...');
  try {
    await router.put(
      `/owner/vehicle-drivers/${editingVehicle.value.id}`,
      { driver_id: driverId.value },
      {
        onSuccess: () => {
          toast.success('Driver assigned successfully!', { id: toastId });
          showDialog.value = false;
        },
        onError: () => toast.error('Failed to assign driver', { id: toastId }),
      },
    );
  } catch (e) {
    console.error(e);
    toast.error('Something went wrong', { id: toastId });
  } finally {
    loading.value = false;
  }
};

const confirmUnassign = (vehicle: Vehicle) => {
  confirmVehicle.value = vehicle;
};

const removeDriver = async () => {
  if (!confirmVehicle.value) return;
  loading.value = true;

  const toastId = toast.loading('Unassigning driver...');
  try {
    await router.put(
      `/owner/vehicle-drivers/${confirmVehicle.value.id}`,
      { driver_id: null },
      {
        onSuccess: () =>
          toast.success('Driver unassigned successfully!', { id: toastId }),
        onError: () =>
          toast.error('Failed to unassign driver', { id: toastId }),
      },
    );
  } catch (e) {
    console.error(e);
    toast.error('Something went wrong', { id: toastId });
  } finally {
    loading.value = false;
    confirmVehicle.value = null;
  }
};
</script>

<template>
  <Head title="Assign Drivers" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="mb-1 text-3xl font-bold">Assign Drivers</h1>
          <p class="text-muted-foreground">
            Manage assigning of drivers to vehicles
          </p>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center">
        <input
          v-model="globalFilter"
          placeholder="Search vehicles..."
          class="w-full rounded-md border px-3 py-2"
        />
      </div>

      <!-- Vehicles Table -->
      <div class="overflow-x-auto rounded-lg border">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-muted text-muted-foreground">
            <tr>
              <th class="px-4 py-2 font-medium">Plate</th>
              <th class="px-4 py-2 font-medium">VIN</th>
              <th class="px-4 py-2 font-medium">Brand</th>
              <th class="px-4 py-2 font-medium">Model</th>
              <th class="px-4 py-2 font-medium">Color</th>
              <th class="px-4 py-2 font-medium">Year</th>
              <th class="px-4 py-2 font-medium">Driver</th>
              <th class="px-4 py-2 font-medium">Email</th>
              <th class="px-4 py-2 font-medium">Status</th>
              <th class="px-4 py-2 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="v in filteredVehicles"
              :key="v.id"
              class="border-t hover:bg-muted/50"
            >
              <td class="px-4 py-2">{{ v.plate_number }}</td>
              <td class="px-4 py-2">{{ v.vin }}</td>
              <td class="px-4 py-2">{{ v.brand }}</td>
              <td class="px-4 py-2">{{ v.model }}</td>
              <td class="px-4 py-2">{{ v.color }}</td>
              <td class="px-4 py-2">{{ v.year }}</td>
              <td class="px-4 py-2">{{ v.driver?.name ?? '-' }}</td>
              <td class="px-4 py-2">{{ v.driver?.email ?? '-' }}</td>
              <td class="px-4 py-2">
                <Badge :variant="getStatusVariant(v.status_name)">
                  {{ v.status_name }}
                </Badge>
              </td>
              <td class="flex gap-2 px-4 py-2">
                <Button
                  size="sm"
                  variant="outline"
                  @click="openAssignDialog(v)"
                  :disabled="loading"
                >
                  <Spinner
                    v-if="loading && editingVehicle?.id === v.id"
                    class="mr-2 h-4 w-4"
                  />
                  Assign / Edit
                </Button>

                <!-- AlertDialog for Unassign -->
                <AlertDialog>
                  <AlertDialogTrigger as-child>
                    <Button
                      size="sm"
                      variant="destructive"
                      :disabled="!v.driver || loading"
                      @click="confirmUnassign(v)"
                    >
                      <Spinner
                        v-if="loading && confirmVehicle?.id === v.id"
                        class="mr-2 h-4 w-4"
                      />
                      Unassign
                    </Button>
                  </AlertDialogTrigger>
                  <AlertDialogContent v-if="confirmVehicle?.id === v.id">
                    <AlertDialogHeader>
                      <AlertDialogTitle>
                        Are you sure you want to unassign this driver?
                      </AlertDialogTitle>
                      <AlertDialogDescription>
                        This will remove the driver from
                        <strong>{{ confirmVehicle.plate_number }}</strong
                        >. This action cannot be undone.
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel @click="confirmVehicle = null">
                        Cancel
                      </AlertDialogCancel>
                      <AlertDialogAction @click="removeDriver">
                        Continue
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </td>
            </tr>

            <tr v-if="filteredVehicles.length === 0">
              <td
                colspan="10"
                class="px-4 py-6 text-center text-muted-foreground"
              >
                No results found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Assign Driver Dialog -->
    <Dialog v-model:open="showDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Assign Driver</DialogTitle>
        </DialogHeader>

        <div class="grid gap-4 py-4">
          <Select v-model="driverId">
            <SelectTrigger>
              <SelectValue placeholder="Select Driver" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="d in props.drivers" :key="d.id" :value="d.id">
                {{ d.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <DialogFooter class="flex justify-end gap-2">
          <Button variant="outline" @click="showDialog = false">Cancel</Button>
          <Button @click="assignDriver" :disabled="loading">
            <Spinner v-if="loading" class="mr-2 h-4 w-4" />
            Save
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
