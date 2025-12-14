<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
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
  DropdownMenuTrigger,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useDetailsModal } from '@/composables/useDetailsModal';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import { debounce } from 'lodash-es';
import { AlertCircleIcon, MoreHorizontal } from 'lucide-vue-next';
import { computed, h, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

// --- Define Props ---
const props = defineProps<{
  drivers: {
    data: DriverRow[];
  };
  franchises: { id: number; name: string }[];
  branches: { id: number; name: string }[];
  filters: {
    tab: 'franchise' | 'branch';
    franchise: string | null;
    branch: string | null;
    status: 'active' | 'retired' | 'suspended';
  };
}>();

// --- Define DriverRow Interface ---
interface DriverRow {
  id: number;
  franchise_name?: string;
  branch_name?: string;
  username: string;
  email: string;
  phone: string;
  status_name: string;
}

// --- 3. Setup Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Driver Management',
    href: superAdmin.driver.index().url,
  },
];

// --- 4. Setup Reactive State for Filters ---
const activeTab = ref(props.filters.tab || 'franchise');
const selectedFranchise = ref(props.filters.franchise || 'all');
const selectedBranch = ref(props.filters.branch || 'all');
const selectedStatus = ref(props.filters.status || 'active');

// --- 5. Computed Properties for UI ---
const title = computed(() => {
  return activeTab.value === 'franchise'
    ? 'Franchise Drivers'
    : 'Branch Drivers';
});

// Computed list for the select dropdown based on the active tab
const selectOptions = computed(() => {
  return activeTab.value === 'franchise' ? props.franchises : props.branches;
});

// A computed v-model for the *single* select component
const selectedFilter = computed({
  get() {
    return activeTab.value === 'franchise'
      ? selectedFranchise.value
      : selectedBranch.value;
  },
  set(value: string) {
    if (activeTab.value === 'franchise') {
      selectedFranchise.value = value;
    } else {
      selectedBranch.value = value;
    }
  },
});

interface DriverModal {
  id: number;
  status: string;
  code_number: string;
  username: string;
  name: string;
  email: string;
  phone: string;
  address: string;
  region: string;
  city: string;
  barangay: string;
  province?: string;
  postal_code: string;
  created_at: string;
  license_number: string;
  license_expiry: string;
  front_license_picture: string;
  back_license_picture: string;
  nbi_clearance: string;
  selfie_picture: string;
  shift: string;
  hire_date?: string;
}
const driverDetails = computed(() => {
  const data = driverModal.data.value;
  if (!data) return [];

  return [
    { label: 'Username', value: data.username, type: 'text' },
    { label: 'Code Number', value: data.code_number, type: 'text' },
    { label: 'Name', value: data.name, type: 'text' },
    { label: 'Email', value: data.email, type: 'text' },
    { label: 'Phone', value: data.phone, type: 'text' },
    { label: 'Status', value: data.status, type: 'text' },
    { label: 'Region', value: data.region, type: 'text' },
    { label: 'Province', value: data.province, type: 'text' },
    { label: 'City', value: data.city, type: 'text' },
    { label: 'Barangay', value: data.barangay, type: 'text' },
    { label: 'Postal Code', value: data.postal_code, type: 'text' },
    { label: 'Address', value: data.address, type: 'text' },
    { label: 'Registered At', value: data.created_at, type: 'text' },
    { label: 'Hire Date', value: data.hire_date, type: 'text' },
    { label: 'Shift', value: data.shift, type: 'text' },
    { label: 'License Number', value: data.license_number, type: 'text' },
    { label: 'License Expiry', value: data.license_expiry, type: 'text' },
    {
      label: 'Front License Picture',
      value: data.front_license_picture,
      type: 'link',
    },
    {
      label: 'Back License Picture',
      value: data.back_license_picture,
      type: 'link',
    },
    { label: 'NBI Clearance', value: data.nbi_clearance, type: 'link' },
    { label: 'Selfie Picture', value: data.selfie_picture, type: 'link' },
  ].filter((item) => item.value);
});

// --- Modal State ---
const driverModal = useDetailsModal<DriverModal>({
  baseUrl: '/super-admin/driver',
});

// --- Change Status Modal State ---
const isChangeModalOpen = ref(false);
const selectedDriver = ref<Partial<DriverRow>>({});

const changeForm = useForm({
  status: '' as string,
});

const openChangeModal = (driver: DriverRow) => {
  selectedDriver.value = driver;
  isChangeModalOpen.value = true;
};

const handleChangeDriver = () => {
  if (!selectedDriver.value?.id) return;

  changeForm.patch(
    superAdmin.driver.change(selectedDriver.value.id).url,
    {
      onSuccess: () => {
        changeForm.reset();
        isChangeModalOpen.value = false;
        toast.success('Driver change status successfully!');
      },
    },
  );
};

const statuses = [
  { value: 'active', label: 'Active' },
  { value: 'retired', label: 'Retired' },
  { value: 'suspended', label: 'Suspended' },
];

// Computed columns for the data table
const driverColumns = computed<ColumnDef<DriverRow>[]>(() => {
  const baseColumns: ColumnDef<DriverRow>[] = [
    {
      accessorKey: 'username',
      header: 'Driver',
    },
    // Conditionally add the correct column
    activeTab.value === 'franchise'
      ? { accessorKey: 'franchise_name', header: 'Franchise' }
      : { accessorKey: 'branch_name', header: 'Branch' },
    {
      accessorKey: 'email',
      header: 'Email',
    },
    {
      accessorKey: 'phone',
      header: 'Phone',
    },
    {
      accessorKey: 'status_name',
      header: () => h('div', { class: 'text-center' }, 'Status'),
      cell: ({ row }) => {
        const status = row.getValue('status_name') as string;
        const badgeClass = {
          'bg-blue-500 hover:bg-blue-600': status === 'active',
          'bg-rose-500 hover:bg-rose-600':
            status === 'suspended' || status === 'retired',
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
        const driver = row.original;

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
              h(DropdownMenuLabel, null, () => 'Actions'),
              h(
                DropdownMenuItem,
                {
                  class: 'cursor-pointer',
                  onClick: () => driverModal.open(driver.id),
                },
                () => 'View Driver Details',
              ),
              h(DropdownMenuSeparator),
              [
                h(
                  DropdownMenuItem,
                  {
                    class: 'cursor-pointer text-blue-500 focus:text-blue-600',
                    onClick: () => openChangeModal(driver),
                  },
                  () => 'Change Status',
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
  const queryParams: Record<string, string> = {
    tab: activeTab.value,
    status: selectedStatus.value,
  };

  // **This is the crucial part for "no conflicts"**
  // Only add the 'franchise' param if the tab is 'franchise'
  if (activeTab.value === 'franchise' && selectedFranchise.value !== 'all') {
    queryParams.franchise = selectedFranchise.value;
  }
  // Only add the 'branch' param if the tab is 'branch'
  else if (activeTab.value === 'branch' && selectedBranch.value !== 'all') {
    queryParams.branch = selectedBranch.value;
  }

  router.get(superAdmin.driver.index().url, queryParams, {
    preserveScroll: true,
    replace: true, // Doesn't pollute browser history
  });
};

// Watch for tab changes (instant update)
watch(activeTab, (newTab) => {
  // When tab switches, reset the *other* filter to 'all'
  // This helps keep the URL clean
  if (newTab === 'franchise') {
    selectedBranch.value = 'all';
  } else {
    selectedFranchise.value = 'all';
  }
});

// Watch for select filter changes (debounced)
watch(
  [selectedFranchise, selectedBranch, activeTab, selectedStatus],
  debounce(() => {
    updateFilters();
  }, 300), // Debounce to avoid firing on every keystroke/click
);
</script>

<template>

  <Head title="Driver Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <Tabs v-model="activeTab" class="w-full">
        <TabsList class="w-full justify-start p-1.5">
          <TabsTrigger value="franchise" class="cursor-pointer font-semibold"
            :class="{ 'pointer-events-none': activeTab === 'franchise' }">
            Franchise
          </TabsTrigger>
          <TabsTrigger value="branch" class="cursor-pointer font-semibold"
            :class="{ 'pointer-events-none': activeTab === 'branch' }">
            Branch
          </TabsTrigger>
        </TabsList>
      </Tabs>

      <div class="relative rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="font-mono text-xl font-semibold">
            {{ title }}
          </h2>

          <div class="flex gap-4">
            <Select v-model="selectedStatus">
              <SelectTrigger class="w-[150px] cursor-pointer">
                <SelectValue placeholder="Filter by..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active" class="cursor-pointer">
                  Active
                </SelectItem>
                <SelectItem value="retired" class="cursor-pointer">
                  Retired
                </SelectItem>
                <SelectItem value="suspended" class="cursor-pointer">
                  Suspended
                </SelectItem>
              </SelectContent>
            </Select>

            <Select v-model="selectedFilter">
              <SelectTrigger class="w-[240px] cursor-pointer">
                <SelectValue placeholder="Filter by..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all" class="cursor-pointer">
                  All
                  {{ activeTab === 'franchise' ? 'Franchises' : 'Branches' }}
                </SelectItem>
                <SelectItem class="cursor-pointer" v-for="option in selectOptions" :key="option.id"
                  :value="String(option.id)">
                  {{ option.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <DataTable :columns="driverColumns" :data="drivers.data" search-placeholder="Search drivers..." />
      </div>
    </div>
  </AppLayout>

  <Dialog v-model:open="driverModal.isOpen.value">
    <DialogContent class="max-w-3xl overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Driver Details</DialogTitle>
      </DialogHeader>
      <DialogDescription>
        <div v-if="driverModal.isLoading.value" class="grid grid-cols-2 gap-4">
          <template v-for="item in 10" :key="item">
            <Skeleton class="h-5 w-24" />
            <Skeleton class="h-5 w-3/4" />
          </template>
        </div>

        <div v-else-if="driverDetails.length > 0" class="grid grid-cols-2 gap-4">
          <template v-for="item in driverDetails" :key="item.label">
            <div class="font-medium">{{ item.label }}:</div>

            <div v-if="item.type === 'link'">
              <a :href="item.value" target="_blank" class="text-blue-500 hover:underline">View</a>
            </div>

            <div v-else>
              {{ item.value }}
            </div>
          </template>
        </div>

        <div v-else-if="driverModal.isError.value">
          <Alert variant="destructive" class="border-2 border-red-500 shadow-lg">
            <AlertCircleIcon class="h-4 w-4" />
            <AlertTitle class="font-bold">Error</AlertTitle>
            <AlertDescription class="font-semibold">
              Failed to load driver details.
            </AlertDescription>
          </Alert>
        </div>
      </DialogDescription>

      <DialogFooter class="mt-5">
        <Button variant="outline" @click="driverModal.close">Close</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <Dialog v-model:open="isChangeModalOpen">
    <DialogContent class="max-w-md font-mono">
      <DialogHeader>
        <DialogTitle class="text-xl">Change Driver Status</DialogTitle>
        <DialogDescription>
          Change the status of <strong class="text-blue-500">{{ selectedDriver?.username }}</strong>. From {{
            selectedDriver?.status_name
          }} to <em>"{{ changeForm.status }}"</em>.
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label>Status</Label>
          <Select v-model="changeForm.status">
            <SelectTrigger>
              <SelectValue placeholder="Select status" />
            </SelectTrigger>
            <SelectContent>
              <template v-for="s in statuses" :key="s.value">
                <SelectItem v-if="selectedDriver?.status_name !== s.value" :value="s.value">
                  {{ s.label }}
                </SelectItem>
              </template>
            </SelectContent>
          </Select>
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="isChangeModalOpen = false">Cancel</Button>
        <Button @click="handleChangeDriver" :disabled="changeForm.processing || !changeForm.status">
          {{ changeForm.processing ? 'Changing...' : 'Confirm Change' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
