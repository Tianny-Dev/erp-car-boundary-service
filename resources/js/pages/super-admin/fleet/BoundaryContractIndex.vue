<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import MultiSelect from '@/components/MultiSelect.vue';
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
  // DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
import axios from 'axios';
import { debounce } from 'lodash-es';
import { AlertCircleIcon, MoreHorizontal, PlusIcon } from 'lucide-vue-next';
import { computed, h, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

// --- Define Props ---
const props = defineProps<{
  contracts: {
    data: ContractRow[];
  };
  franchises: { id: number; name: string }[];
  filters: {
    franchise: string[];
    status: 'active' | 'retired' | 'suspended';
  };
}>();

// --- Define ContractRow Interface ---
interface ContractRow {
  id: number;
  name: string;
  amount: number;
  coverage_area: string;
  start_date: string;
  end_date: string;
  franchise_name?: string;
  driver_username: string;
  status_name: string;
}

// --- 3. Setup Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Boundary Contract',
    href: superAdmin.boundaryContract.index().url,
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

interface ContractModal {
  id: number;
  name: string;
  amount: number;
  coverage_area: string;
  contract_terms: string;
  renewal_terms: string;
  start_date: string;
  end_date: string;
  status_name: string;
  driver_name: string;
  driver_username: string;
  driver_email: string;
  driver_phone: string;
  franchise_name?: string;
  franchise_email?: string;
  franchise_phone?: string;
  vehicle_plate_number?: string;
}
const contractDetails = computed(() => {
  const data = contractModal.data.value;
  if (!data) return [];

  return [
    { label: 'Contract', value: data.name, type: 'text' },
    { label: 'Status', value: data.status_name, type: 'text' },
    { label: 'Amount', value: formatCurrency(data.amount), type: 'text' },
    { label: 'Coverage Area', value: data.coverage_area, type: 'text' },
    { label: 'Contract Terms', value: data.contract_terms, type: 'text' },
    { label: 'Renewal Terms', value: data.renewal_terms, type: 'text' },
    { label: 'Start Date', value: data.start_date, type: 'text' },
    { label: 'End Date', value: data.end_date, type: 'text' },
    { label: 'Driver Name', value: data.driver_name, type: 'text' },
    { label: 'Driver Username', value: data.driver_username, type: 'text' },
    { label: 'Driver Email', value: data.driver_email, type: 'text' },
    { label: 'Driver Phone', value: data.driver_phone, type: 'text' },
    { label: 'Franchise Name', value: data.franchise_name, type: 'text' },
    { label: 'Franchise Email', value: data.franchise_email, type: 'text' },
    { label: 'Franchise Phone', value: data.franchise_phone, type: 'text' },
    {
      label: 'Vehicle Plate Number',
      value: data.vehicle_plate_number || '(No Vehicle Assigned)',
      type: 'text',
      class: !data.vehicle_plate_number ? 'text-rose-500 text-xs italic' : '',
    },
  ].filter((item) => item.value);
});

// --- Modal State ---
const contractModal = useDetailsModal<ContractModal>({
  baseUrl: '/super-admin/boundary-contract',
});

const createContract = () => {
  router.get(superAdmin.boundaryContract.create().url);
};

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
  }).format(amount);
};

// --- Assign Vehicle State ---
const isAssignMode = ref(false);
const selectedVehicleId = ref<string>('');
const availableVehicles = ref<
  {
    id: number;
    plate_number: string;
    brand: string;
    model: string;
    year: number;
  }[]
>([]);
const isFetchingVehicles = ref(false);

const assignForm = useForm({
  vehicle_id: null as number | null,
});

const fetchAvailableVehicles = async (contractId: number) => {
  isFetchingVehicles.value = true;
  availableVehicles.value = [];
  try {
    const response = await axios.get(
      superAdmin.boundaryContract.availableVehicles({ contract: contractId })
        .url,
    );
    availableVehicles.value = response.data;
  } catch {
    availableVehicles.value = [];
  } finally {
    isFetchingVehicles.value = false;
  }
};

const openAssignMode = () => {
  isAssignMode.value = true;
  selectedVehicleId.value = '';
  const contractId = contractModal.data.value?.id;
  if (contractId) fetchAvailableVehicles(contractId);
};

const cancelAssign = () => {
  isAssignMode.value = false;
  selectedVehicleId.value = '';
  assignForm.reset();
};

const confirmAssignVehicle = () => {
  const contractId = contractModal.data.value?.id;
  if (!contractId || !selectedVehicleId.value) return;

  assignForm.vehicle_id = Number(selectedVehicleId.value);

  assignForm.patch(
    superAdmin.boundaryContract.assignVehicle({ contract: contractId }).url,
    {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Vehicle assigned successfully!');
        // Refresh modal data to reflect the new vehicle
        contractModal.open(contractId);
        isAssignMode.value = false;
        selectedVehicleId.value = '';
        assignForm.reset();
      },
      onError: () => {
        toast.error('Failed to assign vehicle.');
      },
    },
  );
};

// Reset assign mode when dialog closes
watch(
  () => contractModal.isOpen.value,
  (open) => {
    if (!open) cancelAssign();
  },
);

// Computed columns for the data table
const contractColumns = computed<ColumnDef<ContractRow>[]>(() => {
  const baseColumns: ColumnDef<ContractRow>[] = [
    {
      accessorKey: 'name',
      header: 'Contract',
    },
    {
      accessorKey: 'driver_username',
      header: 'Driver',
    },
    {
      accessorKey: 'franchise_name',
      header: 'Franchise',
    },
    {
      accessorKey: 'amount',
      header: 'Amount',
      cell: (info) => formatCurrency(info.getValue() as number),
    },
    {
      accessorKey: 'coverage_area',
      header: 'Coverage Area',
    },
    {
      accessorKey: 'start_date',
      header: 'Start Date',
    },
    {
      accessorKey: 'end_date',
      header: 'End Date',
    },
    {
      accessorKey: 'status_name',
      header: () => h('div', { class: 'text-center' }, 'Status'),
      cell: ({ row }) => {
        const status = row.getValue('status_name') as string;
        const badgeClass = {
          'bg-blue-500 hover:bg-blue-600': status === 'active',
          'bg-rose-500 hover:bg-rose-600':
            status === 'retired' || status === 'suspended',
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
        const contract = row.original;

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
                  onClick: () => contractModal.open(contract.id),
                },
                () => 'View Contract Details',
              ),
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
    superAdmin.boundaryContract.index().url,
    {
      status: selectedStatus.value,
      franchise: selectedFranchise.value || [],
    },
    {
      preserveScroll: true,
      replace: true, // Doesn't pollute browser history
    },
  );
};

// Watch for select filter changes (debounced)
watch(
  [selectedStatus],
  debounce(() => {
    updateFilters();
  }, 300), // Debounce to avoid firing on every keystroke/click
);
</script>

<template>
  <Head title="Boundary Contract" />

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
          <h2 class="font-mono text-xl font-semibold">Franchise Contracts</h2>

          <div
            class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:gap-4"
          >
            <Select v-model="selectedStatus">
              <SelectTrigger class="w-full sm:w-[150px] sm:shrink-0">
                <SelectValue placeholder="Filter by..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active"> Active </SelectItem>
                <!-- <SelectItem value="pending"> Pending </SelectItem> -->
                <SelectItem value="retired">Retired</SelectItem>
                <SelectItem value="suspended">Suspended</SelectItem>
              </SelectContent>
            </Select>

            <MultiSelect
              class="w-full sm:w-auto sm:max-w-[300px] sm:min-w-[175px]"
              v-model="selectedContext"
              :options="contextOptions"
              placeholder="
        Select Franchises
        "
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
          :columns="contractColumns"
          :data="contracts.data"
          search-placeholder="Search contracts..."
        >
          <template #custom-actions>
            <Button
              class="flex w-full items-center justify-center gap-2 sm:me-5 sm:w-auto"
              @click="createContract"
            >
              <PlusIcon class="h-4 w-4" /> Add Contract
            </Button>
          </template>
        </DataTable>
      </div>
    </div>

    <Dialog v-model:open="contractModal.isOpen.value">
      <DialogContent
        class="flex max-h-[80vh] max-w-3xl flex-col overflow-hidden pe-3"
      >
        <DialogHeader>
          <DialogTitle>Contract Details</DialogTitle>
        </DialogHeader>
        <DialogDescription class="flex-1 overflow-y-auto">
          <div
            v-if="contractModal.isLoading.value"
            class="grid grid-cols-2 gap-4"
          >
            <template v-for="item in 10" :key="item">
              <Skeleton class="h-5 w-24" />
              <Skeleton class="h-5 w-3/4" />
            </template>
          </div>

          <div
            v-else-if="contractDetails.length > 0"
            class="grid grid-cols-2 gap-4"
          >
            <template v-for="item in contractDetails" :key="item.label">
              <div class="font-medium">{{ item.label }}:</div>
              <div :class="item.class">
                {{ item.value }}
              </div>
            </template>
          </div>

          <div v-else-if="contractModal.isError.value">
            <Alert
              variant="destructive"
              class="border-2 border-red-500 shadow-lg"
            >
              <AlertCircleIcon class="h-4 w-4" />
              <AlertTitle class="font-bold">Error</AlertTitle>
              <AlertDescription class="font-semibold">
                Failed to load contract details.
              </AlertDescription>
            </Alert>
          </div>
        </DialogDescription>

        <DialogFooter class="mt-5">
          <DialogFooter class="mt-5 flex-col gap-2 sm:flex-row sm:items-center">
            <!-- Assign Vehicle flow -->
            <template v-if="!contractModal.data.value?.vehicle_plate_number">
              <template v-if="isAssignMode">
                <Select
                  v-model="selectedVehicleId"
                  :disabled="isFetchingVehicles || assignForm.processing"
                >
                  <SelectTrigger class="w-full sm:w-[220px]">
                    <SelectValue
                      :placeholder="
                        isFetchingVehicles
                          ? 'Loading vehicles...'
                          : 'Select a vehicle'
                      "
                    />
                  </SelectTrigger>
                  <SelectContent>
                    <template v-if="availableVehicles.length > 0">
                      <SelectItem
                        v-for="v in availableVehicles"
                        :key="v.id"
                        :value="String(v.id)"
                      >
                        {{ v.plate_number }} — {{ v.brand }} {{ v.model }}
                        {{ v.year }}
                      </SelectItem>
                    </template>
                    <div
                      v-else-if="!isFetchingVehicles"
                      class="px-3 py-2 text-sm text-muted-foreground"
                    >
                      No available vehicles
                    </div>
                  </SelectContent>
                </Select>
                <Button
                  variant="default"
                  :disabled="!selectedVehicleId || assignForm.processing"
                  @click="confirmAssignVehicle"
                >
                  {{ assignForm.processing ? 'Assigning...' : 'Confirm' }}
                </Button>
                <Button
                  variant="ghost"
                  :disabled="assignForm.processing"
                  @click="cancelAssign"
                >
                  Cancel
                </Button>
              </template>
              <template v-else>
                <Button variant="default" @click="openAssignMode">
                  Assign Vehicle
                </Button>
                <Button variant="outline" @click="contractModal.close"
                  >Close</Button
                >
              </template>
            </template>
          </DialogFooter>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
