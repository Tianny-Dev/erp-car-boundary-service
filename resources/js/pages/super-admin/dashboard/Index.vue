<script setup lang="ts">
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
import { Skeleton } from '@/components/ui/skeleton';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { useDetailsModal } from '@/composables/useDetailsModal';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { AlertCircleIcon, MoreHorizontal } from 'lucide-vue-next';
import { computed, ref } from 'vue';
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

defineProps<{
  franchises: {
    data: FranchiseRow[];
    links: Record<string, any>;
    meta: Record<string, any>;
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

// --- Modal State ---
const franchiseModal = useDetailsModal<FranchiseModal>({
  baseUrl: '/super-admin/franchise',
});

const ownerModal = useDetailsModal<OwnerModal>({
  baseUrl: '/super-admin/owner',
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
</script>

<template>
  <Head title="Super Admin Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
      <div
        class="relative rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
      >
        <h2 class="mb-4 font-mono text-xl font-semibold">
          Franchise Management
        </h2>

        <Table class="w-full table-fixed">
          <TableHeader>
            <TableRow>
              <TableHead class="w-1/5 truncate text-center font-semibold"
                >Franchise</TableHead
              >
              <TableHead class="w-1/5 truncate text-center font-semibold"
                >Owner</TableHead
              >
              <TableHead class="w-1/5 truncate text-center font-semibold"
                >Email</TableHead
              >
              <TableHead class="w-1/5 truncate text-center font-semibold"
                >Phone</TableHead
              >
              <TableHead class="w-1/10 truncate text-center font-semibold"
                >Status</TableHead
              >
              <TableHead class="w-1/10 truncate text-center font-semibold"
                >Actions</TableHead
              >
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="franchises.data.length > 0">
              <TableRow
                v-for="franchise in franchises.data"
                :key="franchise.id"
              >
                <TableCell class="w-1/5 truncate text-center">{{
                  franchise.name
                }}</TableCell>

                <TableCell class="w-1/5 truncate text-center">{{
                  franchise.owner_name
                }}</TableCell>
                <TableCell class="w-1/5 truncate text-center">{{
                  franchise.email
                }}</TableCell>
                <TableCell class="w-1/5 truncate text-center">{{
                  franchise.phone
                }}</TableCell>
                <TableCell class="truncates w-1/10 text-center">
                  <Badge
                    v_if="franchise.status_name"
                    :class="{
                      'bg-blue-500 hover:bg-blue-600':
                        franchise.status_name === 'active',
                      'bg-amber-500 hover:bg-amber-600':
                        franchise.status_name === 'pending',
                    }"
                    class="text-white"
                  >
                    {{ franchise.status_name }}
                  </Badge>
                </TableCell>

                <TableCell class="w-1/10 text-center">
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child class="cursor-pointer">
                      <Button variant="ghost" class="h-8 w-8 p-0">
                        <span class="sr-only">Open menu</span>
                        <MoreHorizontal class="h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="border-2">
                      <DropdownMenuLabel>Actions</DropdownMenuLabel>
                      <DropdownMenuItem
                        class="cursor-pointer"
                        @click="franchiseModal.open(franchise.id)"
                      >
                        View Franchise Details
                      </DropdownMenuItem>
                      <DropdownMenuItem
                        class="cursor-pointer"
                        @click="ownerModal.open(franchise.owner_id)"
                      >
                        View Owner Details
                      </DropdownMenuItem>
                      <div v-if="franchise.status_name === 'pending'">
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                          class="cursor-pointer text-blue-500 focus:text-blue-600"
                          @click="openAcceptModal(franchise)"
                        >
                          Accept Franchise
                        </DropdownMenuItem>
                      </div>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </TableCell>
              </TableRow>
            </template>

            <TableRow v-else>
              <TableCell
                colspan="6"
                class="py-4 text-center text-sm text-muted-foreground"
              >
                No franchises found.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
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
</template>
