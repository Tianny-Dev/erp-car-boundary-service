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
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
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

// State
const isSaving = ref(false);
const deletingId = ref<number | null>(null);
const selectedVehicleToDelete = ref<Vehicle | null>(null);

// Filtered Vehicles
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

// Status options
const statuses = ref<Status[]>([
  { id: 1, name: 'active' },
  { id: 2, name: 'pending' },
  { id: 3, name: 'suspended' },
  { id: 4, name: 'retired' },
]);

// Dialogs
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

// Status badge style
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

// Save vehicle (create or update)
const saveVehicle = () => {
  const payload = {
    plate_number: plate_number.value,
    vin: vin.value,
    brand: brand.value,
    model: model.value,
    color: color.value,
    year: year.value,
    status_id: statusId.value,
  };

  router.visit(
    dialogMode.value === 'create'
      ? '/owner/vehicles'
      : `/owner/vehicles/${editingVehicle.value?.id}`,
    {
      method: dialogMode.value === 'create' ? 'post' : 'put',
      data: payload,
      onStart: () => (isSaving.value = true),
      onFinish: () => (isSaving.value = false),
      onSuccess: () => {
        toast.success(
          dialogMode.value === 'create'
            ? 'Vehicle Created!'
            : 'Vehicle Updated!',
        );
        showDialog.value = false;
      },
      onError: () => toast.error('Something went wrong'),
    },
  );
};

// Confirm delete (open AlertDialog)
const confirmDeleteVehicle = (vehicle: Vehicle) => {
  selectedVehicleToDelete.value = vehicle;
};

// Perform delete
const deleteVehicle = () => {
  if (!selectedVehicleToDelete.value) return;
  const id = selectedVehicleToDelete.value.id;

  router.delete(`/owner/vehicles/${id}`, {
    onStart: () => (deletingId.value = id),
    onFinish: () => {
      deletingId.value = null;
      selectedVehicleToDelete.value = null;
    },
    onSuccess: () => toast.success('Vehicle Deleted!'),
    onError: () => toast.error('Failed to delete vehicle'),
  });
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
              <TableHead>Status</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow
              v-for="v in filteredVehicles"
              :key="v.id"
              class="hover:bg-muted/50"
            >
              <TableCell>{{ v.plate_number }}</TableCell>
              <TableCell>{{ v.vin }}</TableCell>
              <TableCell>{{ v.brand }}</TableCell>
              <TableCell>{{ v.model }}</TableCell>
              <TableCell>{{ v.color }}</TableCell>
              <TableCell>{{ v.year }}</TableCell>
              <TableCell>
                <Badge :variant="getStatusVariant(v.status_name)">
                  {{ v.status_name }}
                </Badge>
              </TableCell>
              <TableCell class="flex gap-2">
                <Button size="sm" variant="outline" @click="openEditDialog(v)">
                  Edit
                </Button>

                <!-- Delete -->
                <AlertDialog>
                  <AlertDialogTrigger as-child>
                    <Button
                      size="sm"
                      variant="destructive"
                      :disabled="deletingId === v.id"
                      @click="confirmDeleteVehicle(v)"
                    >
                      <Spinner
                        v-if="deletingId === v.id"
                        class="mr-2 h-4 w-4"
                      />
                      Delete
                    </Button>
                  </AlertDialogTrigger>

                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>
                        Are you sure you want to delete this vehicle?
                      </AlertDialogTitle>
                      <AlertDialogDescription>
                        This will permanently remove
                        <b>{{ v.plate_number }}</b> from the system.
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>Cancel</AlertDialogCancel>
                      <AlertDialogAction @click="deleteVehicle">
                        Confirm
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </TableCell>
            </TableRow>

            <TableRow v-if="filteredVehicles.length === 0">
              <TableCell
                colspan="8"
                class="py-6 text-center text-muted-foreground"
              >
                No results found.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog v-model:open="showDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>
            {{ dialogMode === 'create' ? 'Add Vehicle' : 'Edit Vehicle' }}
          </DialogTitle>
        </DialogHeader>

        <div class="grid gap-4 py-4">
          <Input v-model="plate_number" placeholder="Plate Number" />
          <Input v-model="vin" placeholder="VIN" />
          <Input v-model="brand" placeholder="Brand" />
          <Input v-model="model" placeholder="Model" />
          <Input v-model="color" placeholder="Color" />
          <Input v-model="year" type="number" placeholder="Year" />
          <Select v-model="statusId">
            <SelectTrigger>
              <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="s in statuses" :key="s.id" :value="s.id">
                {{ s.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <DialogFooter class="flex justify-end gap-2">
          <Button variant="outline" @click="showDialog = false">
            Cancel
          </Button>
          <Button @click="saveVehicle" :disabled="isSaving">
            <Spinner v-if="isSaving" class="mr-2 h-4 w-4" />
            {{ dialogMode === 'create' ? 'Create' : 'Update' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
