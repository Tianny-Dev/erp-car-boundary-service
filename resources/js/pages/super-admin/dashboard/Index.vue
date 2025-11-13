<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { Skeleton } from '@/components/ui/skeleton';
import { useDetailsModal } from '@/composables/useDetailsModal';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { type ColumnDef } from '@tanstack/vue-table';
import {
  AlertCircleIcon,
  BanknoteArrowDownIcon,
  BanknoteArrowUpIcon,
  LandmarkIcon,
  MoreHorizontal,
  WarehouseIcon,
} from 'lucide-vue-next';
import { computed, h, ref } from 'vue';
import { toast } from 'vue-sonner';

interface FranchiseRow {
  id: number;
  name: string;
  email: string;
  phone: string;
  status_name: string;
  owner_name: string;
  owner_id: number;
}

interface BranchRow {
  id: number;
  name: string;
  email: string;
  phone: string;
  status_name: string;
  manager_name: string;
  manager_id: number;
}

defineProps<{
  franchises: {
    data: FranchiseRow[];
  };
  branches: {
    data: BranchRow[];
  };
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: superAdmin.dashboard().url,
  },
];

interface FranchiseModal {
  id: number;
  status: string;
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
  dti_registration_attachment?: string;
  mayor_permit_attachment?: string;
  proof_agreement_attachment?: string;
}
const franchiseDetails = computed(() => {
  const data = franchiseModal.data.value;
  if (!data) return [];

  return [
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
    {
      label: 'DTI Registration',
      value: data.dti_registration_attachment,
      type: 'link',
    },
    {
      label: "Mayor's Permit",
      value: data.mayor_permit_attachment,
      type: 'link',
    },
    {
      label: 'Proof of Agreement',
      value: data.proof_agreement_attachment,
      type: 'link',
    },
  ].filter((item) => item.value);
});

interface OwnerModal {
  id: number;
  status: string;
  name: string;
  email: string;
  phone: string;
  address: string;
  region: string;
  city: string;
  barangay: string;
  province?: string;
  postal_code: string;
  valid_id_type: string;
  valid_id_number: string;
  front_valid_id_picture?: string;
  back_valid_id_picture?: string;
  created_at?: string;
}
const ownerDetails = computed(() => {
  const data = ownerModal.data.value;
  if (!data) return [];

  return [
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
    { label: 'Valid ID Type', value: data.valid_id_type, type: 'text' },
    { label: 'Valid ID Number', value: data.valid_id_number, type: 'text' },
    {
      label: 'Front Valid ID Picture',
      value: data.front_valid_id_picture,
      type: 'link',
    },
    {
      label: 'Back Valid ID Picture',
      value: data.back_valid_id_picture,
      type: 'link',
    },
  ].filter((item) => item.value);
});

interface BranchModal {
  id: number;
  status: string;
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
  dti_registration_attachment?: string;
  mayor_permit_attachment?: string;
  proof_agreement_attachment?: string;
}
const branchDetails = computed(() => {
  const data = branchModal.data.value;
  if (!data) return [];

  return [
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
    {
      label: 'DTI Registration',
      value: data.dti_registration_attachment,
      type: 'link',
    },
    {
      label: "Mayor's Permit",
      value: data.mayor_permit_attachment,
      type: 'link',
    },
    {
      label: 'Proof of Agreement',
      value: data.proof_agreement_attachment,
      type: 'link',
    },
  ].filter((item) => item.value);
});

interface ManagerModal {
  id: number;
  status: string;
  name: string;
  email: string;
  phone: string;
  address: string;
  region: string;
  city: string;
  barangay: string;
  province?: string;
  postal_code: string;
  created_at?: string;
}
const managerDetails = computed(() => {
  const data = managerModal.data.value;
  if (!data) return [];

  return [
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
  ].filter((item) => item.value);
});

// --- Modal State ---
const franchiseModal = useDetailsModal<FranchiseModal>({
  baseUrl: '/super-admin/franchise',
});

const ownerModal = useDetailsModal<OwnerModal>({
  baseUrl: '/super-admin/owner',
});

const branchModal = useDetailsModal<BranchModal>({
  baseUrl: '/super-admin/branch',
});

const managerModal = useDetailsModal<ManagerModal>({
  baseUrl: '/super-admin/manager',
});

// --- Accept Modal State ---
const isAcceptModalOpen = ref(false);
const selectedFranchise = ref<Partial<FranchiseRow>>({});
const isAcceptingFranchise = ref(false);

const openAcceptModal = (franchise: FranchiseRow) => {
  selectedFranchise.value = franchise;
  isAcceptModalOpen.value = true;
};

const handleAcceptFranchise = () => {
  if (!selectedFranchise.value?.id) return;
  isAcceptingFranchise.value = true;
  router.patch(
    superAdmin.franchise.accept(selectedFranchise.value.id).url,
    {},
    {
      onSuccess: () => {
        isAcceptModalOpen.value = false;
        toast.success('Franchise accepted successfully!');
      },
      onFinish: () => {
        setTimeout(() => {
          isAcceptingFranchise.value = false;
        }, 500);
      },
    },
  );
};

const franchiseColumns: ColumnDef<FranchiseRow>[] = [
  {
    accessorKey: 'name',
    header: () => h('div', { class: 'text-center' }, 'Franchise'),
    cell: ({ row }) => h('div', { class: 'text-center' }, row.getValue('name')),
  },
  {
    accessorKey: 'owner_name',
    header: () => h('div', { class: 'text-center' }, 'Owner'),
    cell: ({ row }) =>
      h('div', { class: 'text-center' }, row.getValue('owner_name')),
  },
  {
    accessorKey: 'email',
    header: () => h('div', { class: 'text-center' }, 'Email'),
    cell: ({ row }) =>
      h('div', { class: 'text-center' }, row.getValue('email')),
  },
  {
    accessorKey: 'phone',
    header: () => h('div', { class: 'text-center' }, 'Phone'),
    cell: ({ row }) =>
      h('div', { class: 'text-center' }, row.getValue('phone')),
  },
  {
    accessorKey: 'status_name',
    header: () => h('div', { class: 'text-center' }, 'Status'),
    cell: ({ row }) => {
      const status = row.getValue('status_name') as string;
      const badgeClass = {
        'bg-blue-500 hover:bg-blue-600': status === 'active',
        'bg-amber-500 hover:bg-amber-600': status === 'pending',
      };
      return h('div', { class: 'text-center' }, [
        h(Badge, { class: [badgeClass, 'text-white'] }, () => status || 'N/A'),
      ]);
    },
  },
  {
    id: 'actions',
    header: () => h('div', { class: 'text-center' }, 'Actions'),
    cell: ({ row }) => {
      const franchise = row.original;

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
                onClick: () => franchiseModal.open(franchise.id),
              },
              () => 'View Franchise Details',
            ),
            h(
              DropdownMenuItem,
              {
                class: 'cursor-pointer',
                onClick: () => ownerModal.open(franchise.owner_id),
              },
              () => 'View Owner Details',
            ),
            franchise.status_name === 'pending'
              ? [
                  h(DropdownMenuSeparator),
                  h(
                    DropdownMenuItem,
                    {
                      class: 'cursor-pointer text-blue-500 focus:text-blue-600',
                      onClick: () => openAcceptModal(franchise),
                    },
                    () => 'Accept Franchise',
                  ),
                ]
              : null,
          ]),
        ]),
      ]);
    },
  },
];

const branchColumns: ColumnDef<BranchRow>[] = [
  {
    accessorKey: 'name',
    header: () => h('div', { class: 'text-center' }, 'Branch'),
    cell: ({ row }) => h('div', { class: 'text-center' }, row.getValue('name')),
  },
  {
    accessorKey: 'manager_name',
    header: () => h('div', { class: 'text-center' }, 'Manager'),
    cell: ({ row }) =>
      h('div', { class: 'text-center' }, row.getValue('manager_name')),
  },
  {
    accessorKey: 'email',
    header: () => h('div', { class: 'text-center' }, 'Email'),
    cell: ({ row }) =>
      h('div', { class: 'text-center' }, row.getValue('email')),
  },
  {
    accessorKey: 'phone',
    header: () => h('div', { class: 'text-center' }, 'Phone'),
    cell: ({ row }) =>
      h('div', { class: 'text-center' }, row.getValue('phone')),
  },
  {
    accessorKey: 'status_name',
    header: () => h('div', { class: 'text-center' }, 'Status'),
    cell: ({ row }) => {
      const status = row.getValue('status_name') as string;
      return h('div', { class: 'text-center' }, [
        h(
          Badge,
          { class: ['bg-blue-500 hover:bg-blue-600', 'text-white'] },
          () => status || 'N/A',
        ),
      ]);
    },
  },
  {
    id: 'actions',
    header: () => h('div', { class: 'text-center' }, 'Actions'),
    cell: ({ row }) => {
      const branch = row.original;

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
                onClick: () => branchModal.open(branch.id),
              },
              () => 'View Branch Details',
            ),
            h(
              DropdownMenuItem,
              {
                class: 'cursor-pointer',
                onClick: () => managerModal.open(branch.manager_id),
              },
              () => 'View Manager Details',
            ),
          ]),
        ]),
      ]);
    },
  },
];
</script>

<template>
  <Head title="Super Admin Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="font-mono text-lg"
              >Total Revenue
              <span class="font-mono text-sm text-muted-foreground"
                >(Today)</span
              ></CardTitle
            >
            <BanknoteArrowUpIcon class="h-6 w-6 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="font-mono text-2xl font-semibold">₱80,000</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="font-mono text-lg"
              >Total Expenses
              <span class="font-mono text-sm text-muted-foreground"
                >(Today)</span
              ></CardTitle
            >
            <BanknoteArrowDownIcon class="h-6 w-6 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="font-mono text-2xl font-semibold">₱80,000</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="font-mono text-lg">Total Franchise </CardTitle>
            <LandmarkIcon class="h-6 w-6 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="font-mono text-2xl font-semibold">5</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="font-mono text-lg">Total Branch </CardTitle>
            <WarehouseIcon class="h-6 w-6 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="font-mono text-2xl font-semibold">21</div>
          </CardContent>
        </Card>
      </div>
      <div
        class="relative rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
      >
        <h2 class="mb-4 font-mono text-xl font-semibold">
          Franchise Management
        </h2>
        <DataTable
          :columns="franchiseColumns"
          :data="franchises.data"
          search-placeholder="Search franchises..."
        />
      </div>

      <div
        class="relative rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
      >
        <h2 class="mb-4 font-mono text-xl font-semibold">Branch Management</h2>
        <DataTable
          :columns="branchColumns"
          :data="branches.data"
          search-placeholder="Search branches..."
        />
      </div>
    </div>
  </AppLayout>

  <Dialog v-model:open="isAcceptModalOpen">
    <DialogContent class="max-w-md font-mono">
      <DialogHeader>
        <DialogTitle class="text-2xl">Accept Franchise?</DialogTitle>
        <DialogDescription class="text-md font-semibold">
          Are you sure you want to accept the franchise
          <strong class="text-blue-500">{{ selectedFranchise.name }}</strong
          >? This will also activate the owner's account.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button
          variant="outline"
          @click="isAcceptModalOpen = false"
          class="cursor-pointer"
          >Cancel</Button
        >
        <Button
          variant="default"
          class="cursor-pointer"
          @click="handleAcceptFranchise"
          :disabled="isAcceptingFranchise"
        >
          {{ isAcceptingFranchise ? 'Accepting...' : 'Yes, Accept' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <Dialog v-model:open="franchiseModal.isOpen.value">
    <DialogContent class="max-w-2xl overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Franchise Details</DialogTitle>
      </DialogHeader>
      <DialogDescription>
        <div
          v-if="franchiseModal.isLoading.value"
          class="grid grid-cols-2 gap-4"
        >
          <template v-for="item in 10" :key="item">
            <Skeleton class="h-5 w-24" />
            <Skeleton class="h-5 w-3/4" />
          </template>
        </div>

        <div
          v-else-if="franchiseDetails.length > 0"
          class="grid grid-cols-2 gap-4"
        >
          <template v-for="item in franchiseDetails" :key="item.label">
            <div class="font-medium">{{ item.label }}:</div>

            <div v-if="item.type === 'link'">
              <a
                :href="item.value"
                target="_blank"
                class="text-blue-500 hover:underline"
                >View</a
              >
            </div>

            <div v-else>
              {{ item.value }}
            </div>
          </template>
        </div>

        <div v-else-if="franchiseModal.isError.value">
          <Alert
            variant="destructive"
            class="border-2 border-red-500 shadow-lg"
          >
            <AlertCircleIcon class="h-4 w-4" />
            <AlertTitle class="font-bold">Error</AlertTitle>
            <AlertDescription class="font-semibold">
              Failed to load franchise details.
            </AlertDescription>
          </Alert>
        </div>
      </DialogDescription>
      <DialogFooter class="mt-5">
        <Button
          variant="outline"
          class="cursor-pointer"
          @click="franchiseModal.close"
          >Close</Button
        >
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <Dialog v-model:open="ownerModal.isOpen.value">
    <DialogContent class="max-w-3xl overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Owner Details</DialogTitle>
      </DialogHeader>
      <DialogDescription>
        <div v-if="ownerModal.isLoading.value" class="grid grid-cols-2 gap-4">
          <template v-for="item in 10" :key="item">
            <Skeleton class="h-5 w-24" />
            <Skeleton class="h-5 w-3/4" />
          </template>
        </div>

        <div v-else-if="ownerDetails.length > 0" class="grid grid-cols-2 gap-4">
          <template v-for="item in ownerDetails" :key="item.label">
            <div class="font-medium">{{ item.label }}:</div>

            <div v-if="item.type === 'link'">
              <a
                :href="item.value"
                target="_blank"
                class="text-blue-500 hover:underline"
                >View</a
              >
            </div>

            <div v-else>
              {{ item.value }}
            </div>
          </template>
        </div>

        <div v-else-if="ownerModal.isError.value">
          <Alert
            variant="destructive"
            class="border-2 border-red-500 shadow-lg"
          >
            <AlertCircleIcon class="h-4 w-4" />
            <AlertTitle class="font-bold">Error</AlertTitle>
            <AlertDescription class="font-semibold">
              Failed to load owner details.
            </AlertDescription>
          </Alert>
        </div>
      </DialogDescription>

      <DialogFooter class="mt-5">
        <Button
          variant="outline"
          class="cursor-pointer"
          @click="franchiseModal.close"
          >Close</Button
        >
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <Dialog v-model:open="branchModal.isOpen.value">
    <DialogContent class="max-w-2xl overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Branch Details</DialogTitle>
      </DialogHeader>
      <DialogDescription>
        <div v-if="branchModal.isLoading.value" class="grid grid-cols-2 gap-4">
          <template v-for="item in 10" :key="item">
            <Skeleton class="h-5 w-24" />
            <Skeleton class="h-5 w-3/4" />
          </template>
        </div>

        <div
          v-else-if="branchDetails.length > 0"
          class="grid grid-cols-2 gap-4"
        >
          <template v-for="item in branchDetails" :key="item.label">
            <div class="font-medium">{{ item.label }}:</div>

            <div v-if="item.type === 'link'">
              <a
                :href="item.value"
                target="_blank"
                class="text-blue-500 hover:underline"
                >View</a
              >
            </div>

            <div v-else>
              {{ item.value }}
            </div>
          </template>
        </div>

        <div v-else-if="branchModal.isError.value">
          <Alert
            variant="destructive"
            class="border-2 border-red-500 shadow-lg"
          >
            <AlertCircleIcon class="h-4 w-4" />
            <AlertTitle class="font-bold">Error</AlertTitle>
            <AlertDescription class="font-semibold">
              Failed to load branch details.
            </AlertDescription>
          </Alert>
        </div>
      </DialogDescription>
      <DialogFooter class="mt-5">
        <Button
          variant="outline"
          class="cursor-pointer"
          @click="branchModal.close"
          >Close</Button
        >
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <Dialog v-model:open="managerModal.isOpen.value">
    <DialogContent class="max-w-3xl overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Manager Details</DialogTitle>
      </DialogHeader>
      <DialogDescription>
        <div v-if="managerModal.isLoading.value" class="grid grid-cols-2 gap-4">
          <template v-for="item in 10" :key="item">
            <Skeleton class="h-5 w-24" />
            <Skeleton class="h-5 w-3/4" />
          </template>
        </div>

        <div
          v-else-if="managerDetails.length > 0"
          class="grid grid-cols-2 gap-4"
        >
          <template v-for="item in managerDetails" :key="item.label">
            <div class="font-medium">{{ item.label }}:</div>

            <div v-if="item.type === 'link'">
              <a
                :href="item.value"
                target="_blank"
                class="text-blue-500 hover:underline"
                >View</a
              >
            </div>

            <div v-else>
              {{ item.value }}
            </div>
          </template>
        </div>

        <div v-else-if="managerModal.isError.value">
          <Alert
            variant="destructive"
            class="border-2 border-red-500 shadow-lg"
          >
            <AlertCircleIcon class="h-4 w-4" />
            <AlertTitle class="font-bold">Error</AlertTitle>
            <AlertDescription class="font-semibold">
              Failed to load manager details.
            </AlertDescription>
          </Alert>
        </div>
      </DialogDescription>

      <DialogFooter class="mt-5">
        <Button
          variant="outline"
          class="cursor-pointer"
          @click="managerModal.close"
          >Close</Button
        >
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
