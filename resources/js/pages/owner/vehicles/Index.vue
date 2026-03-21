<script setup lang="ts">
import { usePage, router, Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { MoreHorizontal } from 'lucide-vue-next';

// UI Components
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
import Label from '@/components/ui/label/Label.vue';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination';
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
import { Textarea } from '@/components/ui/textarea';
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

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
  or_cr: string;
}

interface VehiclesPaginator {
  current_page: number;
  data: Vehicle[];
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

interface Status {
  id: number;
  name: string;
}

const props = defineProps<{
  vehicles: VehiclesPaginator;
  inventories: any[];
}>();

const page = usePage();
// Fixed Error Handling: Computed for initial, but we will mutate the source
const errors = computed(() => page.props.errors as any);

// Helper to clear errors immediately on input
const clearError = (field: string) => {
  if (page.props.errors[field]) {
    delete (page.props.errors as any)[field];
  }
};

const paginator = ref(props.vehicles);

// Maintenance Form State
const showMaintenanceDialog = ref(false);
const isSubmittingMaintenance = ref(false);

const maintenanceForm = ref({
  vehicle_id: null as number | null,
  inventory_id: null as number | null,
  quantity: 1,
  description: '',
  maintenance_date: new Date().toISOString().substr(0, 10),
  next_maintenance_date: '',
});

const resetMaintenanceForm = () => {
  maintenanceForm.value = {
    vehicle_id: null,
    inventory_id: null,
    quantity: 1,
    description: '',
    maintenance_date: new Date().toISOString().substr(0, 10),
    next_maintenance_date: '',
  };
};

// -------------------------
// Watchers
// -------------------------
watch(
  () => props.vehicles,
  (newVehicles) => {
    paginator.value = newVehicles;
  },
  { deep: true },
);

const maintenanceModal = ref({
  isOpen: false,
  isLoading: false,
  isError: false,
  data: [] as any[],
});

const openMaintenanceHistory = async (vehicle: Vehicle) => {
  maintenanceModal.value.isOpen = true;
  maintenanceModal.value.isLoading = true;
  maintenanceModal.value.isError = false;
  maintenanceModal.value.data = [];

  try {
    const response = await axios.get(
      `/owner/vehicles/${vehicle.id}/maintenance-history`,
    );
    maintenanceModal.value.data = response.data;
  } catch (error) {
    console.error('Failed to load maintenance history:', error);
    maintenanceModal.value.isError = true;
  } finally {
    maintenanceModal.value.isLoading = false;
  }
};

const closeMaintenanceHistory = () => {
  maintenanceModal.value.isOpen = false;
};

const selectedInventory = computed(() =>
  props.inventories?.find((i) => i.id === maintenanceForm.value.inventory_id),
);

const submitMaintenance = () => {
  router.post('/owner/vehicles/maintenance', maintenanceForm.value, {
    onStart: () => (isSubmittingMaintenance.value = true),
    onFinish: () => (isSubmittingMaintenance.value = false),
    onSuccess: () => {
      toast.success('Maintenance record added and stock updated!');
      showMaintenanceDialog.value = false;
      resetMaintenanceForm();
      router.reload({ only: ['errors'] });
    },
    // onError: (errs) => toast.error('Please fix the errors in the form'),
  });
};

const handleVinInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  let val = target.value.toUpperCase();

  // 1. Remove non-alphanumeric characters
  // 2. Globally prohibited letters in VINs: I (eye), O (oh), and Q (que)
  val = val.replace(/[^A-Z0-9]/g, '').replace(/[IOQ]/g, '');

  // 3. Limit to 17 characters
  if (val.length > 17) {
    val = val.slice(0, 17);
  }

  vin.value = val;

  if (errors.value.vin) {
    clearError('vin');
  }
};

const breadcrumbs = [{ title: 'Vehicle Management', href: '/owner/vehicles' }];
const globalFilter = ref('');
const statusFilter = ref('all');
const isSaving = ref(false);
const deletingId = ref<number | null>(null);
const selectedVehicleToDelete = ref<Vehicle | null>(null);
const or_cr_file = ref<File | null>(null);

const filteredVehicles = computed(() => {
  let filtered = paginator.value.data;
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

const statuses = ref<Status[]>([
  { id: 15, name: 'Available' },
  { id: 5, name: 'Maintenance' },
]);

const showDialog = ref(false);
const dialogMode = ref<'create' | 'edit'>('create');
const editingVehicle = ref<Vehicle | null>(null);

const plate_number = ref('');
const vin = ref('');
const brand = ref('');
const model = ref('');
const color = ref('');
const year = ref<number>();
const statusId = ref<number | null>(null);
const existing_or_cr = ref<string | null>(null);

const openCreateDialog = () => {
  // Clear any existing errors from previous attempts
  router.reload({ only: ['errors'] });
  dialogMode.value = 'create';
  editingVehicle.value = null;
  plate_number.value = vin.value = brand.value = model.value = color.value = '';
  year.value = undefined;
  statusId.value = null;
  or_cr_file.value = null;
  existing_or_cr.value = null;
  showDialog.value = true;
};

const openEditDialog = (vehicle: Vehicle) => {
  router.reload({ only: ['errors'] });
  dialogMode.value = 'edit';
  editingVehicle.value = vehicle;
  plate_number.value = vehicle.plate_number;
  vin.value = vehicle.vin;
  brand.value = vehicle.brand;
  model.value = vehicle.model;
  color.value = vehicle.color;
  year.value = vehicle.year;
  statusId.value = vehicle.status_id;
  existing_or_cr.value = vehicle.or_cr;
  or_cr_file.value = null;
  showDialog.value = true;
};

const paginationLinks = computed(() => paginator.value.links || []);

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

const getBadgeClass = (status: string) => {
  switch (status) {
    case 'active':
      return 'bg-blue-500 hover:bg-blue-600';
    case 'available':
      return 'bg-green-500 hover:bg-green-600';
    case 'maintenance':
      return 'bg-rose-500 hover:bg-rose-600';
    default:
      return 'bg-gray-500 hover:bg-gray-600';
  }
};

const saveVehicle = () => {
  const formData = new FormData();
  formData.append(
    'plate_number',
    plate_number.value.toUpperCase().trim() || '',
  );
  formData.append('vin', vin.value || '');
  formData.append('brand', brand.value || '');
  formData.append('model', model.value || '');
  formData.append('color', color.value || '');
  formData.append('year', year.value ? String(year.value) : '');
  formData.append('status_id', statusId.value ? String(statusId.value) : '');

  if (or_cr_file.value) formData.append('or_cr', or_cr_file.value);
  if (dialogMode.value === 'edit') formData.append('_method', 'PUT');

  router.post(
    dialogMode.value === 'create'
      ? '/owner/vehicles'
      : `/owner/vehicles/${editingVehicle.value?.id}`,
    formData,
    {
      forceFormData: true,
      onStart: () => (isSaving.value = true),
      onFinish: () => (isSaving.value = false),
      onSuccess: () => {
        toast.success(
          dialogMode.value === 'create'
            ? 'Vehicle Created!'
            : 'Vehicle Updated!',
        );
        showDialog.value = false;
        or_cr_file.value = null;
        existing_or_cr.value = null;
      },
      onError: () => {
        toast.error('Check the form for errors');
      },
    },
  );
};

// -------------------------
// REFINED INPUT HANDLERS
// -------------------------

const handlePlateInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  let val = target.value.toUpperCase();

  // Format Logic: Strip non-alphanumeric then add space
  val = val.replace(/[^A-Z0-9]/g, '');
  if (val.length > 3 && isNaN(Number(val[2]))) {
    // 3 Letters + Numbers
    val = val.slice(0, 3) + ' ' + val.slice(3, 7);
  } else if (val.length > 2) {
    // 2 Letters + Numbers (Motorcycles)
    val = val.slice(0, 2) + ' ' + val.slice(2, 7);
  }

  plate_number.value = val;
  clearError('plate_number');
};

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    or_cr_file.value = target.files[0];
    existing_or_cr.value = null;
    clearError('or_cr');
  }
};

const handleYearInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.value.length > 4) {
    target.value = target.value.slice(0, 4);
    year.value = Number(target.value);
  }
  clearError('year');
};

// Handle Select changes
watch(statusId, () => clearError('status_id'));
watch(vin, () => clearError('vin'));
watch(brand, () => clearError('brand'));
watch(model, () => clearError('model'));
watch(color, () => clearError('color'));

const confirmDeleteVehicle = (vehicle: Vehicle) => {
  selectedVehicleToDelete.value = vehicle;
};

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
  });
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
</script>

<template>
  <Head title="Vehicle Management" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div
        class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
      >
        <div>
          <h1 class="mb-1 text-3xl font-bold">Vehicle Management</h1>
          <p class="text-sm text-gray-600 sm:text-base">Manage all vehicles</p>
        </div>
        <div
          class="grid w-full grid-cols-2 gap-3 sm:flex sm:w-auto sm:flex-row sm:gap-4"
        >
          <Button
            class="w-full sm:w-auto"
            @click="showMaintenanceDialog = true"
          >
            + Add Maintenance
          </Button>
          <Button class="w-full sm:w-auto" @click="openCreateDialog">
            + Add Vehicle
          </Button>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center">
        <input
          v-model="globalFilter"
          placeholder="Search vehicles..."
          class="w-full rounded-md border px-4 py-2 md:flex-1"
        />

        <Select v-model="statusFilter">
          <SelectTrigger class="w-full md:w-48">
            <SelectValue>{{
              statusFilter === 'all' ? 'Filter by status' : statusFilter
            }}</SelectValue>
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Status</SelectItem>
            <SelectItem value="available">Available</SelectItem>
            <SelectItem value="maintenance">Maintenance</SelectItem>
          </SelectContent>
        </Select>
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
                <Badge :class="getBadgeClass(v.status_name)" class="text-white">
                  {{ v.status_name }}
                </Badge>
              </TableCell>

              <!-- <TableCell class="flex gap-2">
                <Button size="sm" variant="outline" @click="openEditDialog(v)">
                  Edit
                </Button>

                <Button
                  size="sm"
                  variant="outline"
                  @click="maintenanceModal.open(v)"
                >
                  View Maintenance History
                </Button>

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
              </TableCell> -->

              <TableCell class="text-center">
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="ghost" class="h-8 w-8 p-0">
                      <span class="sr-only">Open menu</span>
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-48">
                    <DropdownMenuLabel class="text-xs text-muted-foreground"
                      >Actions</DropdownMenuLabel
                    >

                    <DropdownMenuItem
                      @click="openEditDialog(v)"
                      class="cursor-pointer"
                    >
                      Edit Vehicle
                    </DropdownMenuItem>

                    <DropdownMenuItem
                      @click="openMaintenanceHistory(v)"
                      class="cursor-pointer"
                    >
                      Maintenance History
                    </DropdownMenuItem>

                    <DropdownMenuSeparator />

                    <AlertDialog>
                      <AlertDialogTrigger as-child>
                        <div
                          class="relative flex cursor-pointer items-center rounded-sm px-2 py-1.5 text-sm text-red-600 transition-colors outline-none select-none hover:bg-destructive hover:text-destructive-foreground focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                        >
                          Delete Vehicle
                        </div>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Are you sure?</AlertDialogTitle>
                          <AlertDialogDescription>
                            This will permanently remove
                            <b>{{ v.plate_number }}</b> from the system.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>Cancel</AlertDialogCancel>
                          <AlertDialogAction
                            class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                            @click="
                              confirmDeleteVehicle(v);
                              deleteVehicle();
                            "
                          >
                            Confirm Delete
                          </AlertDialogAction>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </DropdownMenuContent>
                </DropdownMenu>
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
                  :class="{
                    'bg-slate-200 text-black dark:bg-slate-800 dark:text-white':
                      link.active,
                  }"
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

    <Dialog v-model:open="maintenanceModal.isOpen">
      <DialogContent class="flex max-h-[80vh] max-w-4xl flex-col">
        <DialogHeader>
          <DialogTitle>Maintenance History</DialogTitle>
          <DialogDescription>
            Showing all maintenance records for this vehicle.
          </DialogDescription>
        </DialogHeader>

        <div class="flex-1 overflow-y-auto py-4 pe-1.5">
          <div v-if="maintenanceModal.isLoading" class="space-y-4">
            <div
              v-for="i in 3"
              :key="i"
              class="h-24 w-full animate-pulse rounded-lg bg-muted"
            />
          </div>

          <Alert v-else-if="maintenanceModal.isError" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Error</AlertTitle>
            <AlertDescription
              >Failed to load maintenance history.</AlertDescription
            >
          </Alert>

          <div v-else-if="maintenanceModal.data?.length" class="space-y-4">
            <div
              v-for="item in maintenanceModal.data"
              :key="item.id"
              class="rounded-lg border p-4 transition-colors hover:bg-muted/50"
            >
              <div class="mb-2 flex items-start justify-between">
                <div>
                  <h4 class="text-lg font-bold text-primary">
                    {{ item.inventory_name }}
                  </h4>
                  <Badge variant="outline" class="mt-1">
                    {{ item.category }}
                  </Badge>
                </div>
                <div class="text-right text-sm">
                  <p class="font-medium text-foreground">
                    Date: {{ item.maintenance_date }}
                  </p>
                  <p class="text-muted-foreground italic">
                    Next: {{ item.next_maintenance_date }}
                  </p>
                </div>
              </div>

              <div
                class="mt-3 grid grid-cols-1 gap-4 border-t pt-3 text-sm md:grid-cols-3"
              >
                <div>
                  <span class="block font-semibold">Specification:</span>
                  <p class="text-muted-foreground">{{ item.specification }}</p>
                </div>
                <div>
                  <span class="block font-semibold">Quantity</span>
                  <p class="text-muted-foreground">{{ item.quantity }}</p>
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
          <Button variant="outline" @click="closeMaintenanceHistory">
            Close
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Create/Edit Dialog -->
    <Dialog v-model:open="showDialog">
      <DialogContent class="flex max-h-[90vh] flex-col sm:max-w-lg">
        <DialogHeader class="border-b pb-2">
          <DialogTitle>
            {{ dialogMode === 'create' ? 'Add Vehicle' : 'Edit Vehicle' }}
          </DialogTitle>
        </DialogHeader>

        <div class="custom-scrollbar overflow-y-auto">
          <div class="grid gap-4">
            <div class="grid gap-1">
              <Label class="p-1">Plate Number</Label>
              <Input
                v-model="plate_number"
                placeholder="Enter Plate Number"
                :class="{ 'border-red-500': errors.plate_number }"
                @input="handlePlateInput"
              />
              <p v-if="errors.plate_number" class="px-1 text-xs text-red-500">
                {{ errors.plate_number }}
              </p>
            </div>

            <div class="grid gap-1">
              <Label class="p-1">VIN (Chassis Number)</Label>
              <Input
                v-model="vin"
                placeholder="17-character VIN"
                maxlength="17"
                :class="{ 'border-red-500': errors.vin }"
                @input="handleVinInput"
                class="font-mono uppercase"
              />
              <p v-if="errors.vin" class="px-1 text-xs text-red-500">
                {{ errors.vin }}
              </p>
              <p class="px-1 text-[10px] text-muted-foreground">
                Standard 17-digit Chassis Number (Excludes I, O, Q)
              </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div class="grid gap-1">
                <Label class="p-1">Brand</Label>
                <Input
                  v-model="brand"
                  placeholder="Enter Brand"
                  :class="{ 'border-red-500': errors.brand }"
                />
                <p v-if="errors.brand" class="px-1 text-xs text-red-500">
                  {{ errors.brand }}
                </p>
              </div>
              <div class="grid gap-1">
                <Label class="p-1">Model</Label>
                <Input
                  v-model="model"
                  placeholder="Enter Model"
                  :class="{ 'border-red-500': errors.model }"
                />
                <p v-if="errors.model" class="px-1 text-xs text-red-500">
                  {{ errors.model }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div class="grid gap-1">
                <Label class="p-1">Color</Label>
                <Select
                  v-model="color"
                  @update:model-value="clearError('color')"
                >
                  <SelectTrigger :class="{ 'border-red-500': errors.color }">
                    <SelectValue placeholder="Select Color" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="c in vehicleColors" :key="c" :value="c">
                      <div class="flex items-center gap-2">
                        <!-- <div
                          class="h-3 w-3 rounded-full border border-gray-300"
                          :style="{
                            backgroundColor: c.toLowerCase().replace(' ', ''),
                          }"
                        ></div> -->
                        {{ c }}
                      </div>
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="errors.color" class="px-1 text-xs text-red-500">
                  {{ errors.color }}
                </p>
              </div>
              <div class="grid gap-1">
                <Label class="p-1">Year</Label>
                <Input
                  v-model="year"
                  type="number"
                  placeholder="Enter Year (e.g. 2024)"
                  :class="{ 'border-red-500': errors.year }"
                  @input="handleYearInput"
                />
                <p v-if="errors.year" class="px-1 text-xs text-red-500">
                  {{ errors.year }}
                </p>
              </div>
            </div>

            <div class="grid gap-1">
              <Label class="p-1">Status</Label>
              <Select v-model="statusId">
                <SelectTrigger :class="{ 'border-red-500': errors.status_id }">
                  <SelectValue placeholder="Select Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="s in statuses" :key="s.id" :value="s.id">
                    {{ s.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="errors.status_id" class="px-1 text-xs text-red-500">
                {{ errors.status_id }}
              </p>
            </div>

            <div class="grid gap-1">
              <Label class="p-1 font-semibold">OR CR</Label>
              <Input
                type="file"
                accept="image/*,.pdf"
                @change="handleFileUpload"
                :class="{
                  'border-red-500 focus-visible:ring-red-500': errors.or_cr,
                  'cursor-pointer file:cursor-pointer file:text-primary': true,
                }"
              />

              <p
                v-if="errors.or_cr"
                class="px-1 text-xs font-medium text-red-500"
              >
                {{ errors.or_cr }}
              </p>

              <div
                v-if="or_cr_file && !errors.or_cr"
                class="mt-1 flex items-center gap-2 px-1"
              >
                <Badge
                  variant="secondary"
                  class="bg-green-100 text-green-700 hover:bg-green-100"
                >
                  New: {{ or_cr_file.name }}
                </Badge>
              </div>

              <div
                v-else-if="existing_or_cr && !errors.or_cr"
                class="mt-1 flex items-center gap-2 px-1"
              >
                <span class="text-[11px] text-gray-500">Current file:</span>
                <a
                  :href="`${existing_or_cr}`"
                  target="_blank"
                  class="text-[11px] text-blue-600 underline hover:text-blue-800"
                >
                  View Document
                </a>
              </div>
            </div>
          </div>
        </div>
        <DialogFooter class="flex justify-end gap-2 border-t pt-6">
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

    <Dialog v-model:open="showMaintenanceDialog">
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Add Maintenance Record</DialogTitle>
        </DialogHeader>

        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label>Select Vehicle</Label>
            <Select
              v-model="maintenanceForm.vehicle_id"
              @update:model-value="clearError('vehicle_id')"
            >
              <SelectTrigger :class="{ 'border-red-500': errors.vehicle_id }">
                <SelectValue placeholder="Choose Vehicle" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="v in vehicles.data"
                  :key="v.id"
                  :value="v.id"
                >
                  {{ v.plate_number }} - {{ v.model }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p
              v-if="errors.vehicle_id"
              class="text-xs font-medium text-red-500"
            >
              {{ errors.vehicle_id }}
            </p>
          </div>

          <div class="grid gap-2">
            <Label>Part / Inventory Item</Label>
            <Select
              v-model="maintenanceForm.inventory_id"
              @update:model-value="clearError('inventory_id')"
            >
              <SelectTrigger :class="{ 'border-red-500': errors.inventory_id }">
                <SelectValue
                  :placeholder="
                    inventories.length
                      ? 'Select Item'
                      : 'No inventory records found'
                  "
                />
              </SelectTrigger>
              <SelectContent>
                <div
                  v-if="inventories.length === 0"
                  class="p-2 text-center text-sm text-muted-foreground"
                >
                  No inventory available
                </div>
                <SelectItem
                  v-for="item in inventories"
                  :key="item.id"
                  :value="item.id.toString()"
                >
                  {{ item.name }} (Stock: {{ item.quantity }})
                </SelectItem>
              </SelectContent>
            </Select>
            <p
              v-if="errors.inventory_id"
              class="text-xs font-medium text-red-500"
            >
              {{ errors.inventory_id }}
            </p>
          </div>

          <div
            v-if="selectedInventory"
            class="grid grid-cols-2 gap-4 rounded-md bg-muted p-3 text-sm"
          >
            <div>
              <span class="text-muted-foreground">Unit Price:</span>
              <p class="font-bold">₱{{ selectedInventory.unit_price }}</p>
            </div>
            <div>
              <span class="text-muted-foreground">Available Stock:</span>
              <p
                :class="
                  selectedInventory.quantity < maintenanceForm.quantity
                    ? 'text-red-500'
                    : 'text-green-600'
                "
              >
                {{ selectedInventory.quantity }} units
              </p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="grid gap-2">
              <Label>Quantity Used</Label>
              <Input
                type="number"
                v-model="maintenanceForm.quantity"
                min="1"
                :class="{ 'border-red-500': errors.quantity }"
                @input="clearError('quantity')"
              />
              <p
                v-if="errors.quantity"
                class="text-xs font-medium text-red-500"
              >
                {{ errors.quantity }}
              </p>
            </div>
            <div class="grid gap-2">
              <Label>Date</Label>
              <Input
                type="date"
                v-model="maintenanceForm.maintenance_date"
                :class="{ 'border-red-500': errors.maintenance_date }"
                @input="clearError('maintenance_date')"
              />
              <p
                v-if="errors.maintenance_date"
                class="text-xs font-medium text-red-500"
              >
                {{ errors.maintenance_date }}
              </p>
            </div>
          </div>

          <div class="grid gap-2">
            <Label>Next Maintenance Date</Label>
            <Input
              type="date"
              v-model="maintenanceForm.next_maintenance_date"
              :class="{ 'border-red-500': errors.next_maintenance_date }"
              @input="clearError('next_maintenance_date')"
            />
            <p
              v-if="errors.next_maintenance_date"
              class="text-xs font-medium text-red-500"
            >
              {{ errors.next_maintenance_date }}
            </p>
          </div>

          <div class="grid gap-2">
            <Label>Work Description</Label>
            <Textarea
              v-model="maintenanceForm.description"
              placeholder="What was fixed?"
              :class="{ 'border-red-500': errors.description }"
              @input="clearError('description')"
            />
            <p
              v-if="errors.description"
              class="text-xs font-medium text-red-500"
            >
              {{ errors.description }}
            </p>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="showMaintenanceDialog = false"
            >Cancel</Button
          >
          <Button
            @click="submitMaintenance"
            :disabled="
              isSubmittingMaintenance ||
              (selectedInventory &&
                maintenanceForm.quantity > selectedInventory.quantity)
            "
          >
            <Spinner v-if="isSubmittingMaintenance" class="mr-2 h-4 w-4" />
            Save Maintenance
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
