<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

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
}

interface Status {
  id: number;
  name: string;
}

const props = defineProps<{ vehicles: Vehicle[] }>();

// Breadcrumb
const breadcrumbs = [{ title: 'Vehicle Management', href: '/owner/vehicles' }];

// Filters
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
      [v.plate_number, v.vin, v.brand, v.model, v.color, v.year].some((f) =>
        String(f).toLowerCase().includes(term),
      ),
    );
  }

  return filtered;
});

// Statuses
const statuses = ref<Status[]>([
  { id: 1, name: 'active' },
  { id: 2, name: 'pending' },
  { id: 3, name: 'suspended' },
  { id: 4, name: 'retired' },
]);

// Dialog
const showDialog = ref(false);
const dialogMode = ref<'create' | 'edit'>('create');
const editingVehicle = ref<Vehicle | null>(null);

// Form refs
const plate_number = ref('');
const vin = ref('');
const brand = ref('');
const model = ref('');
const color = ref('');
const year = ref<number>();
const statusId = ref<number | null>(null);

const openCreateDialog = () => {
  dialogMode.value = 'create';
  editingVehicle.value = null;
  plate_number.value = vin.value = brand.value = model.value = color.value = '';
  year.value = undefined;
  statusId.value = null;
  showDialog.value = true;
};

const openEditDialog = (vehicle: Vehicle) => {
  dialogMode.value = 'edit';
  editingVehicle.value = vehicle;
  plate_number.value = vehicle.plate_number;
  vin.value = vehicle.vin;
  brand.value = vehicle.brand;
  model.value = vehicle.model;
  color.value = vehicle.color;
  year.value = vehicle.year;
  statusId.value = vehicle.status_id;
  showDialog.value = true;
};

// Status badge
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

// Save vehicle
const saveVehicle = async () => {
  const payload = {
    plate_number: plate_number.value,
    vin: vin.value,
    brand: brand.value,
    model: model.value,
    color: color.value,
    year: year.value,
    status_id: statusId.value,
  };

  try {
    if (dialogMode.value === 'create') {
      await router.post('/owner/vehicles', payload, {
        onSuccess: () => {
          toast.success('Vehicle Created!');
          showDialog.value = false;
        },
      });
    } else if (editingVehicle.value) {
      await router.put(`/owner/vehicles/${editingVehicle.value.id}`, payload, {
        onSuccess: () => {
          toast.success('Vehicle Updated!');
          showDialog.value = false;
        },
      });
    }
  } catch (e) {
    console.error(e);
    toast.error('Something went wrong');
  }
};

// Delete vehicle
const deleteVehicle = async (vehicle: Vehicle) => {
  if (!confirm(`Delete ${vehicle.plate_number}?`)) return;
  try {
    await router.delete(`/owner/vehicles/${vehicle.id}`, {
      onSuccess: () => toast.success('Deleted!'),
      onError: () => toast.error('Failed'),
    });
  } catch (e) {
    console.error(e);
    toast.error('Something went wrong');
  }
};
</script>

<template>
  <Head title="Vehicle Management" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="mb-1 text-3xl font-bold">Vehicle Management</h1>
          <p class="text-gray-600">Manage all vehicles</p>
        </div>
        <Button @click="openCreateDialog">+ Add Vehicle</Button>
      </div>

      <!-- Filters -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center">
        <input
          v-model="globalFilter"
          placeholder="Search vehicles..."
          class="w-full rounded-md border px-4 py-2 md:flex-1"
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

      <!-- Vehicles Table -->
      <div class="overflow-x-auto rounded-lg border">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="px-4 py-3 font-semibold">Plate</th>
              <th class="px-4 py-3 font-semibold">VIN</th>
              <th class="px-4 py-3 font-semibold">Brand</th>
              <th class="px-4 py-3 font-semibold">Model</th>
              <th class="px-4 py-3 font-semibold">Color</th>
              <th class="px-4 py-3 font-semibold">Year</th>
              <th class="px-4 py-3 font-semibold">Status</th>
              <th class="px-4 py-3 font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="v in filteredVehicles"
              :key="v.id"
              class="border-t hover:bg-gray-50"
            >
              <td class="px-4 py-2">{{ v.plate_number }}</td>
              <td class="px-4 py-2">{{ v.vin }}</td>
              <td class="px-4 py-2">{{ v.brand }}</td>
              <td class="px-4 py-2">{{ v.model }}</td>
              <td class="px-4 py-2">{{ v.color }}</td>
              <td class="px-4 py-2">{{ v.year }}</td>
              <td class="px-4 py-2">
                <Badge :variant="getStatusVariant(v.status_name)">{{
                  v.status_name
                }}</Badge>
              </td>
              <td class="flex gap-2 px-4 py-2">
                <Button size="sm" variant="outline" @click="openEditDialog(v)"
                  >Edit</Button
                >
                <Button
                  size="sm"
                  variant="destructive"
                  @click="deleteVehicle(v)"
                  >Delete</Button
                >
              </td>
            </tr>
            <tr v-if="filteredVehicles.length === 0">
              <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                No results found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog v-model:open="showDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{
            dialogMode === 'create' ? 'Add Vehicle' : 'Edit Vehicle'
          }}</DialogTitle>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <Input v-model="plate_number" placeholder="Plate Number" />
          <Input v-model="vin" placeholder="VIN" />
          <Input v-model="brand" placeholder="Brand" />
          <Input v-model="model" placeholder="Model" />
          <Input v-model="color" placeholder="Color" />
          <Input v-model="year" type="number" placeholder="Year" />
          <Select v-model="statusId">
            <SelectTrigger><SelectValue placeholder="Status" /></SelectTrigger>
            <SelectContent>
              <SelectItem v-for="s in statuses" :key="s.id" :value="s.id">{{
                s.name
              }}</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <DialogFooter class="flex justify-end gap-2">
          <Button variant="outline" @click="showDialog = false">Cancel</Button>
          <Button @click="saveVehicle">{{
            dialogMode === 'create' ? 'Create' : 'Update'
          }}</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
