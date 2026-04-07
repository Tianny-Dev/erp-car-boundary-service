<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import PasswordConfirmDialog from '@/components/PasswordConfirmDialog.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { useDetailsModal } from '@/composables/useDetailsModal';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { debounce } from 'lodash-es';
import { AlertCircleIcon, MoreHorizontal, PlusIcon } from 'lucide-vue-next';
import type { ComponentInstance } from 'vue';
import { computed, h, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

// --- Define Props ---
const props = defineProps<{
  vehicles: {
    data: VehicleRow[];
  };
  franchises: { id: number; name: string }[];
  filters: {
    franchise: string[];
    status: 'active' | 'available' | 'maintenance';
  };
}>();

// --- Define VehicleRow Interface ---
interface VehicleRow {
  id: number;
  franchise_name?: string;
  plate_number: string;
  vin: string;
  status_name: string;
}

// --- 3. Setup Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Vehicle Management',
    href: superAdmin.vehicle.index().url,
  },
];

// --- 4. Setup Reactive State for Filters ---
const selectedFranchise = ref<string[]>(props.filters.franchise || []);
const selectedStatus = ref(props.filters.status || 'active');

const selectedContext = computed({
  get: () => selectedFranchise.value,
  set: (val: string[]) => {
    selectedFranchise.value = val;
  },
});

// Mapping options for the MultiSelect
const contextOptions = computed(() => {
  const data = props.franchises;
  return data.map((item) => ({ id: item.id, label: item.name }));
});

// for vehicle details
interface VehicleModal {
  id: number;
  status: string;
  plate_number: string;
  vin: string;
  brand: string;
  model: string;
  year: string;
  color: string;
  or_cr: string;
  franchise_id?: number;
  franchise_name?: string;
  driver?: string;
}

const vehicleDetails = computed(() => {
  const data = vehicleModal.data.value;
  if (!data) return [];

  return [
    {
      label: 'Assigned Driver',
      value: data.driver || '(No Driver Assigned)',
      type: 'text',
      class: !data.driver ? 'text-rose-500 text-xs italic' : '',
    },
    {
      label: 'Franchise',
      value: data.franchise_name || '(No Franchise Assigned)',
      type: 'text',
      class: !data.franchise_name ? 'text-rose-500 text-xs italic' : '',
    },
    { label: 'Status', value: data.status, type: 'text' },
    { label: 'Plate Number', value: data.plate_number, type: 'text' },
    { label: 'Vehicle Identification Number', value: data.vin, type: 'text' },
    { label: 'Brand', value: data.brand, type: 'text' },
    { label: 'Model', value: data.model, type: 'text' },
    { label: 'Year', value: data.year, type: 'text' },
    { label: 'Color', value: data.color, type: 'text' },
    { label: 'OR-CR Document', value: data.or_cr, type: 'link' },
  ].filter((item) => item.value);
});

// --- Modal State ---
const vehicleModal = useDetailsModal<VehicleModal>({
  baseUrl: '/super-admin/vehicle',
});

// --- Edit Mode Logic ---
const isEditMode = ref(false);
const editForm = useForm({
  plate_number: '',
  vin: '',
  brand: '',
  model: '',
  year: '',
  color: '',
  or_cr: null as File | null,
});

const startEditing = () => {
  const data = vehicleModal.data.value;
  if (!data) return;

  editForm.plate_number = data.plate_number;
  editForm.vin = data.vin;
  editForm.brand = data.brand;
  editForm.model = data.model;
  editForm.year = data.year;
  editForm.color = data.color;
  editForm.or_cr = null; // Reset file input
  isEditMode.value = true;
};

const handleUpdateVehicle = () => {
  if (!vehicleModal.data.value?.id) return;

  // 1. Transform the data to include the method spoofing key
  editForm
    .transform((data) => ({
      ...data,
      _method: 'patch', // This tells Laravel to treat the POST as a PATCH
    }))
    .post(superAdmin.vehicle.update(vehicleModal.data.value.id).url, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        isEditMode.value = false;
        toast.success('Vehicle updated successfully!');

        // 2. Refresh the modal data to show the newly saved information
        if (vehicleModal.data.value?.id) {
          vehicleModal.open(vehicleModal.data.value.id);
        }
      },
      // onError: (errors) => {
      //   toast.error('Please check the form for errors.');
      // },
    });
};

// Reset Edit Mode when modal closes
watch(vehicleModal.isOpen, (isOpen) => {
  if (!isOpen) {
    isEditMode.value = false;
    editForm.reset();
    editForm.clearErrors();
  }
});

// for maintenance history
interface MaintenanceRow {
  id: number;
  description: string;
  maintenance_date: string;
  next_maintenance_date: string;
  inventory_name: string;
  category: string;
  specification: string;
}
const maintenanceModal = useDetailsModal<MaintenanceRow[]>({
  baseUrl: '/super-admin/vehicle',
});

// --- Assign Franchise Modal State ---
const isAssignModalOpen = ref(false);
const selectedVehicle = ref<Partial<VehicleRow>>({});

const assignForm = useForm({
  franchise_id: null as number | null,
});

const openAssignModal = (vehicle: VehicleRow) => {
  selectedVehicle.value = vehicle;
  isAssignModalOpen.value = true;
};

const handleAssignVehicle = () => {
  if (!selectedVehicle.value?.id) return;

  assignForm.patch(superAdmin.vehicle.assign(selectedVehicle.value.id).url, {
    onSuccess: () => {
      assignForm.reset();
      isAssignModalOpen.value = false;
      toast.success('Vehicle assigned to franchise successfully!');
    },
  });
};

// --- Delete Vehicle Modal State ---
const isDeleteModalOpen = ref(false);
const isDeletingVehicle = ref(false);
const isPasswordModalOpen = ref(false);
const passwordDialogRef = ref<ComponentInstance<
  typeof PasswordConfirmDialog
> | null>(null);

const openDeleteModal = (vehicle: VehicleRow) => {
  selectedVehicle.value = vehicle;
  isDeleteModalOpen.value = true;
};

const openPasswordModal = () => {
  isDeleteModalOpen.value = false;
  isPasswordModalOpen.value = true;
};

const confirmDelete = (password: string) => {
  if (!selectedVehicle.value) return;
  isDeletingVehicle.value = true;

  router.delete(
    superAdmin.vehicle.destroy(selectedVehicle.value.id as number).url,
    {
      data: { password: password },
      preserveScroll: true,

      onSuccess: () => {
        isDeleteModalOpen.value = false;
        selectedVehicle.value = {};
        isPasswordModalOpen.value = false;
        toast.success('Vehicle deleted successfully!');
      },

      onError: (errors) => {
        if (errors.password) {
          passwordDialogRef.value?.setError(errors.password);
        } else {
          toast.error('Something went wrong. Please try again.');
        }
      },

      onFinish: () => {
        isDeletingVehicle.value = false;
      },
    },
  );
};

const createVehicle = () => {
  router.get(superAdmin.vehicle.create().url);
};

// Computed columns for the data table
const vehicleColumns = computed<ColumnDef<VehicleRow>[]>(() => {
  const baseColumns: ColumnDef<VehicleRow>[] = [
    {
      accessorKey: 'franchise_name',
      header: 'Franchise',
      cell: ({ row }) => {
        const franchise = row.getValue('franchise_name') as string;
        const isMissing = !franchise;
        return h(
          'div',
          {
            class: [isMissing ? 'text-rose-500 text-xs italic' : ''],
          },
          franchise || '(No Franchise Assigned)',
        );
      },
    },
    {
      accessorKey: 'vin',
      header: 'Vehicle Identification Number',
    },
    {
      accessorKey: 'plate_number',
      header: 'Plate Number',
    },
    {
      accessorKey: 'status_name',
      header: () => h('div', { class: 'text-center' }, 'Status'),
      cell: ({ row }) => {
        const status = row.getValue('status_name') as string;
        const badgeClass = {
          'bg-blue-500 hover:bg-blue-600': status === 'active',
          'bg-rose-500 hover:bg-rose-600': status === 'maintenance',
          'bg-green-500 hover:bg-green-600': status === 'available',
        };
        return h('div', { class: 'text-center' }, [
          h(
            Badge,
            { class: [badgeClass, 'text-white'] },
            () => status || 'N/A',
          ),
        ]);
      },
    },
    {
      id: 'actions',
      header: () => h('div', { class: 'text-center' }, 'Actions'),
      cell: ({ row }) => {
        const vehicle = row.original;

        return h('div', { class: 'relative text-center' }, [
          h(DropdownMenu, null, () => [
            h(
              DropdownMenuTrigger,
              { asChild: true, class: 'cursor-pointer' },
              () =>
                h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => [
                  h('span', { class: 'sr-only' }, 'Open menu'),
                  h(MoreHorizontal, { class: 'h-4 w-4' }),
                ]),
            ),
            h(DropdownMenuContent, { align: 'end', class: 'border-2' }, () => [
              h(DropdownMenuLabel, { class: 'text-gray-500' }, () => 'Actions'),
              h(
                DropdownMenuItem,
                {
                  class: 'cursor-pointer',
                  onClick: () => vehicleModal.open(vehicle.id),
                },
                () => 'View Vehicle Details',
              ),
              h(
                DropdownMenuItem,
                {
                  class: 'cursor-pointer',
                  onClick: () =>
                    maintenanceModal.open(vehicle.id, 'maintenances'),
                },
                () => 'View Maintenance History',
              ),
              h(DropdownMenuSeparator),
              !vehicle.franchise_name && vehicle.status_name === 'available'
                ? h(
                    DropdownMenuItem,
                    {
                      class: 'cursor-pointer text-blue-500 focus:text-blue-600',
                      onClick: () => openAssignModal(vehicle),
                    },
                    () => 'Assign Franchise',
                  )
                : null,
              [
                h(
                  DropdownMenuItem,
                  {
                    class: 'cursor-pointer text-red-500 focus:text-red-600',
                    onClick: () => openDeleteModal(vehicle),
                  },
                  () => 'Delete Vehicle',
                ),
              ],
            ]),
          ]),
        ]);
      },
    },
  ];
  return baseColumns;
});

// --- Watchers to Update URL ---
const updateFilters = () => {
  router.get(
    superAdmin.vehicle.index().url,
    {
      status: selectedStatus.value,
      franchise: selectedFranchise.value || [],
    },
    {
      preserveScroll: true,
      replace: true,
    },
  );
};

// Watch for select filter changes (debounced)
watch(
  [selectedStatus],
  debounce(() => {
    updateFilters();
  }, 300),
);

const handlePlateInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  let val = target.value.toUpperCase();

  // Strip non-alphanumeric then add space
  val = val.replace(/[^A-Z0-9]/g, '');
  if (val.length > 3 && isNaN(Number(val[2]))) {
    // 3 Letters + Numbers (Standard)
    val = val.slice(0, 3) + ' ' + val.slice(3, 7);
  } else if (val.length > 2) {
    // 2 Letters + Numbers (Motorcycles)
    val = val.slice(0, 2) + ' ' + val.slice(2, 7);
  }

  editForm.plate_number = val;
  editForm.errors.plate_number = '';
};

const handleVinInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  let val = target.value.toUpperCase();

  // Remove non-alphanumeric and prohibited letters (I, O, Q)
  val = val.replace(/[^A-Z0-9]/g, '').replace(/[IOQ]/g, '');

  if (val.length > 17) {
    val = val.slice(0, 17);
  }

  editForm.vin = val;
  editForm.errors.vin = '';
};

const handleYearInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  let val = target.value;

  if (val.length > 4) {
    val = val.slice(0, 4);
  }

  editForm.year = val;
  editForm.errors.year = '';
};

const vehicleColors = [
  'White',
  'Black',
  'Silver',
  'Gray',
  'Red',
  'Blue',
  'Brown',
  'Green',
  'Yellow',
  'Orange',
  'Gold',
  'Beige',
  'Magenta',
  'Purple',
];

const selectedFranchiseName = computed(() => {
  // Find the franchise object where the ID matches the form value
  const franchise = props.franchises.find(
    (f) => f.id === assignForm.franchise_id,
  );

  // Return the name if found, otherwise a placeholder
  return franchise ? franchise.name : '...';
});
</script>

<template>
  <Head title="Vehicle Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
      <div
        class="relative rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
      >
        <div
          class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
          <h2 class="font-mono text-xl font-semibold">Franchise Vehicles</h2>

          <div
            class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:gap-4"
          >
            <Select v-model="selectedStatus">
              <SelectTrigger
                class="w-full cursor-pointer sm:w-[150px] sm:shrink-0"
              >
                <SelectValue placeholder="Filter by..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="available">Available</SelectItem>
                <SelectItem value="maintenance">Maintenance</SelectItem>
              </SelectContent>
            </Select>

            <MultiSelect
              class="w-full sm:w-[175px]"
              v-model="selectedContext"
              :options="contextOptions"
              placeholder="Select Franchises"
              all-label="All Franchises"
              @change="
                (val) => {
                  selectedFranchise = val;
                  updateFilters();
                }
              "
            />
          </div>
        </div>

        <DataTable
          :columns="vehicleColumns"
          :data="vehicles.data"
          search-placeholder="Search vehicles..."
        >
          <template #custom-actions>
            <Button
              class="flex w-full items-center justify-center gap-2 sm:me-5 sm:w-auto"
              @click="createVehicle"
            >
              <PlusIcon class="h-4 w-4" /> Add Vehicle
            </Button>
          </template>
        </DataTable>
      </div>
    </div>
  </AppLayout>

  <Dialog v-model:open="vehicleModal.isOpen.value">
    <DialogContent class="max-h-[90vh] max-w-3xl overflow-y-auto">
      <DialogHeader>
        <DialogTitle>{{
          isEditMode ? 'Edit Vehicle' : 'Vehicle Details'
        }}</DialogTitle>
      </DialogHeader>

      <div class="py-4">
        <div v-if="vehicleModal.isLoading.value" class="grid grid-cols-2 gap-4">
          <template v-for="item in 10" :key="item">
            <Skeleton class="h-5 w-24" />
            <Skeleton class="h-5 w-3/4" />
          </template>
        </div>

        <div v-else-if="isEditMode" class="grid grid-cols-2 gap-x-6 gap-y-4">
          <div class="flex flex-col gap-1.5">
            <Label>Plate Number</Label>
            <Input
              v-model="editForm.plate_number"
              placeholder="e.g., ABC 1234"
              @input="handlePlateInput"
              :class="{ 'border-destructive': editForm.errors.plate_number }"
            />
            <span
              v-if="editForm.errors.plate_number"
              class="text-xs text-destructive"
              >{{ editForm.errors.plate_number }}</span
            >
          </div>

          <div class="flex flex-col gap-1.5">
            <Label>VIN (Chassis Number)</Label>
            <Input
              v-model="editForm.vin"
              @input="handleVinInput"
              :class="{ 'border-destructive': editForm.errors.vin }"
            />

            <span v-if="editForm.errors.vin" class="text-xs text-destructive">{{
              editForm.errors.vin
            }}</span>
            <p v-else class="px-1 text-[10px] text-muted-foreground">
              Excludes I, O, and Q
            </p>
          </div>

          <div class="flex flex-col gap-1.5">
            <Label>Brand</Label>
            <Input
              v-model="editForm.brand"
              :class="{ 'border-destructive': editForm.errors.brand }"
            />
          </div>

          <div class="flex flex-col gap-1.5">
            <Label>Model</Label>
            <Input
              v-model="editForm.model"
              :class="{ 'border-destructive': editForm.errors.model }"
            />
          </div>

          <div class="flex flex-col gap-1.5">
            <Label>Year</Label>
            <Input
              v-model="editForm.year"
              type="number"
              @input="handleYearInput"
              :class="{ 'border-destructive': editForm.errors.year }"
            />
          </div>

          <div class="flex flex-col gap-1.5">
            <Label>Color</Label>
            <Select
              v-model="editForm.color"
              @update:model-value="editForm.errors.color = ''"
            >
              <SelectTrigger
                :class="{ 'border-red-500': editForm.errors.color }"
              >
                <SelectValue placeholder="Select Color" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="c in vehicleColors" :key="c" :value="c">
                  {{ c }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="col-span-2 gap-1.5">
            <Label
              >OR-CR
              <span class="text-[11px mb-1"
                >(Leave empty to keep current)</span
              ></Label
            >
            <Input
              type="file"
              @input="editForm.or_cr = $event.target.files[0]"
              class="cursor-pointer"
            />
            <span
              v-if="editForm.errors.or_cr"
              class="text-xs text-destructive"
              >{{ editForm.errors.or_cr }}</span
            >
          </div>
        </div>

        <div
          v-else-if="vehicleDetails.length > 0"
          class="grid grid-cols-2 gap-4"
        >
          <template v-for="item in vehicleDetails" :key="item.label">
            <div class="font-medium text-muted-foreground">
              {{ item.label }}:
            </div>

            <div v-if="item.type === 'link'">
              <a
                :href="item.value"
                target="_blank"
                class="flex items-center gap-1 text-blue-500 hover:underline"
                >View Document</a
              >
            </div>

            <div v-else :class="item.class">
              {{ item.value }}
            </div>
          </template>
        </div>

        <div v-else-if="vehicleModal.isError.value">
          <Alert
            variant="destructive"
            class="border-2 border-red-500 shadow-lg"
          >
            <AlertCircleIcon class="h-4 w-4" />
            <AlertTitle class="font-bold">Error</AlertTitle>
            <AlertDescription class="font-semibold">
              Failed to load vehicle details.
            </AlertDescription>
          </Alert>
        </div>
      </div>

      <DialogFooter class="mt-5 border-t pt-4">
        <div class="flex w-full justify-between gap-2">
          <div>
            <Button
              v-if="!isEditMode && vehicleModal.data.value"
              @click="startEditing"
              variant="default"
            >
              Edit
            </Button>
            <Button
              v-if="isEditMode"
              @click="handleUpdateVehicle"
              :disabled="editForm.processing"
            >
              {{ editForm.processing ? 'Saving Changes...' : 'Save Changes' }}
            </Button>
          </div>
          <div class="flex gap-2">
            <Button
              v-if="isEditMode"
              variant="ghost"
              @click="isEditMode = false"
              >Cancel</Button
            >
            <Button variant="outline" @click="vehicleModal.close">Close</Button>
          </div>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <Dialog
    v-model:open="isAssignModalOpen"
    @update:open="(val) => !val && assignForm.reset()"
  >
    <DialogContent class="max-w-md font-mono">
      <DialogHeader>
        <DialogTitle class="text-xl">Assign Vehicle to Franchise</DialogTitle>
        <DialogDescription>
          Assign the vehicle to
          <strong class="text-blue-500">{{ selectedFranchiseName }}</strong>
          franchise.
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label>Franchise</Label>
          <Select v-model="assignForm.franchise_id">
            <SelectTrigger>
              <SelectValue placeholder="Select franchise" />
            </SelectTrigger>
            <SelectContent>
              <template v-for="s in franchises" :key="s.id">
                <SelectItem :value="s.id">
                  {{ s.name }}
                </SelectItem>
              </template>
            </SelectContent>
          </Select>
          <span
            v-if="assignForm.errors.franchise_id"
            class="text-xs text-destructive"
            >{{ assignForm.errors.franchise_id }}</span
          >
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          @click="
            isAssignModalOpen = false;
            assignForm.reset();
          "
          >Cancel</Button
        >
        <Button
          @click="handleAssignVehicle"
          :disabled="assignForm.processing || !assignForm.franchise_id"
        >
          {{ assignForm.processing ? 'Assigning...' : 'Confirm Assign' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <Dialog v-model:open="isDeleteModalOpen">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>Delete Vehicle</DialogTitle>
        <DialogDescription>
          Are you sure you want to delete vehicle with plate no.
          <span class="font-semibold text-red-500">
            {{ selectedVehicle?.plate_number }} </span
          >? All related data will be deleted. This action cannot be undone.
        </DialogDescription>
      </DialogHeader>

      <DialogFooter class="gap-2">
        <Button variant="outline" @click="isDeleteModalOpen = false"
          >Cancel</Button
        >

        <Button
          variant="destructive"
          @click="openPasswordModal"
          :disabled="isDeletingVehicle"
        >
          {{ isDeletingVehicle ? 'Deleting...' : 'Yes, Delete' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <PasswordConfirmDialog
    ref="passwordDialogRef"
    v-model:open="isPasswordModalOpen"
    title="Security Check"
    description="This action is irreversible. Please input your password to continue."
    :processing="isDeletingVehicle"
    @confirmed="confirmDelete"
  />

  <Dialog v-model:open="maintenanceModal.isOpen.value">
    <DialogContent class="flex max-h-[80vh] max-w-4xl flex-col">
      <DialogHeader>
        <DialogTitle>Maintenance History</DialogTitle>
        <DialogDescription>
          Showing all maintenance records for this vehicle.
        </DialogDescription>
      </DialogHeader>

      <div class="flex-1 overflow-y-auto py-4 pe-1.5">
        <div v-if="maintenanceModal.isLoading.value" class="space-y-4">
          <Skeleton v-for="i in 3" :key="i" class="h-20 w-full" />
        </div>

        <Alert v-else-if="maintenanceModal.isError.value" variant="destructive">
          <AlertCircleIcon class="h-4 w-4" />
          <AlertTitle>Error</AlertTitle>
          <AlertDescription
            >Failed to load maintenance history.</AlertDescription
          >
        </Alert>

        <div v-else-if="maintenanceModal.data.value?.length" class="space-y-4">
          <div
            v-for="item in maintenanceModal.data.value"
            :key="item.id"
            class="rounded-lg border p-4 transition-colors hover:bg-muted/50"
          >
            <div class="mb-2 flex items-start justify-between">
              <div>
                <h4 class="text-lg font-bold text-primary">
                  {{ item.inventory_name }}
                </h4>
                <Badge variant="outline" class="mt-1">{{
                  item.category
                }}</Badge>
              </div>
              <div class="text-right text-sm">
                <p class="font-medium">Date: {{ item.maintenance_date }}</p>
                <p class="text-muted-foreground italic">
                  Next: {{ item.next_maintenance_date }}
                </p>
              </div>
            </div>

            <div class="mt-3 grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
              <div>
                <span class="block font-semibold">Specification:</span>
                <p class="text-muted-foreground">{{ item.specification }}</p>
              </div>
              <div>
                <span class="block font-semibold">Work Done:</span>
                <p class="text-muted-foreground">{{ item.description }}</p>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="py-10 text-center text-muted-foreground">
          No maintenance records found for this vehicle.
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="maintenanceModal.close">Close</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
