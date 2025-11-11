<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  // DialogDescription,
  // DialogFooter,
  DialogContent,
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
import { MoreHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';

interface FranchiseRow {
  id: number;
  name: string;
  email: string;
  phone: string;
  status_name: string;
  owner_name: string;
}

defineProps<{
  franchises: FranchiseRow[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: superAdmin.dashboard().url,
  },
];

interface User {
  id: number;
  name: string;
  email: string;
  phone?: string /* ... */;
}
interface Status {
  id: number;
  name: string;
}
interface UserOwner {
  id: number;
  user: User;
  status: Status /* ... */;
}
interface FullFranchise {
  id: number;
  name: string;
  email: string;
  phone: string;
  address: string;
  status: Status;
}

// --- Modal State ---
const franchiseModal = useDetailsModal<FullFranchise>({
  baseUrl: '/super-admin/franchises',
});

const ownerModal = useDetailsModal<UserOwner>({
  baseUrl: '/super-admin/owners',
});

// --- Franchise Modal State ---
const isFranchiseModalOpen = ref(false);
const openFranchiseModal = (franchise: FranchiseRow) => {
  selectedFranchise.value = franchise;
  isFranchiseModalOpen.value = true;
};

// --- Owner Modal State ---
const isOwnerModalOpen = ref(false);
const openOwnerModal = (franchise: FranchiseRow) => {
  selectedFranchise.value = franchise;
  isOwnerModalOpen.value = true;
};

// --- Accept Modal State ---
const isAcceptModalOpen = ref(false);
const selectedFranchise = ref<Partial<FranchiseRow>>({});

const openAcceptModal = (franchise: FranchiseRow) => {
  selectedFranchise.value = franchise;
  isAcceptModalOpen.value = true;
};

const handleAcceptFranchise = () => {
  if (!selectedFranchise.value?.id) return;
  router.patch(
    superAdmin.dashboard.franchise.accept(selectedFranchise.value.id).url,
    {},
    {
      onSuccess: () => {
        isAcceptModalOpen.value = false;
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
            <template v-if="franchises.length > 0">
              <TableRow v-for="franchise in franchises" :key="franchise.id">
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
                      'bg-orange-500 hover:bg-orange-600':
                        franchise.status_name === 'ending',
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
                      <div v-if="franchise.status_name === 'active'">
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

  <!-- <Dialog v-model:open="isAcceptModalOpen">
    <DialogContent class="max-w-md">
      <DialogHeader>
        <DialogTitle>Accept Franchise?</DialogTitle>
        <DialogDescription>
          Are you sure you want to accept the franchise
          <strong>{{ selectedFranchise.name }}</strong
          >? This will also activate the owner's account. This action cannot be
          undone.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button variant="outline" @click="isAcceptModalOpen = false"
          >Cancel</Button
        >
        <Button
          variant="default"
          class="bg-blue-500 hover:bg-blue-600"
          @click="handleAcceptFranchise"
          :disabled="router.processing"
        >
          {{ router.processing ? 'Accepting...' : 'Yes, Accept' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog> -->

  <Dialog v-model:open="franchiseModal.isOpen.value">
    <DialogContent class="max-w-2xl overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Franchise Details</DialogTitle>
      </DialogHeader>

      <div v-if="franchiseModal.isLoading.value" class="grid grid-cols-2 gap-4">
        <Skeleton class="h-5 w-24" />
        <Skeleton class="h-5 w-3/4" />
        <Skeleton class="h-5 w-24" />
        <Skeleton class="h-5 w-3/4" />
        <Skeleton class="h-5 w-24" />
        <Skeleton class="h-5 w-3/4" />
        <Skeleton class="h-5 w-24" />
        <Skeleton class="h-5 w-3/4" />
      </div>

      <div v-else-if="franchiseModal.data.value" class="grid grid-cols-2 gap-4">
        <div class="font-medium">Name:</div>
        <div>{{ franchiseModal.data.value.name }}</div>
        <div class="font-medium">Email:</div>
        <div>{{ franchiseModal.data.value.email }}</div>
        <div class="font-medium">Phone:</div>
        <div>{{ franchiseModal.data.value.phone }}</div>
        <div class="font-medium">Status:</div>
        <div>{{ franchiseModal.data.value.status?.name }}</div>
        <div class="font-medium">Address:</div>
        <div>{{ franchiseModal.data.value.address }}</div>
      </div>

      <div v-else-if="franchiseModal.isError.value">
        <p class="text-red-500">Failed to load franchise details.</p>
      </div>
    </DialogContent>
  </Dialog>

  <Dialog v-model:open="ownerModal.isOpen.value">
    <DialogContent class="max-w-3xl overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Owner Details</DialogTitle>
      </DialogHeader>

      <div
        v-if="ownerModal.isLoading.value"
        class="grid grid-cols-1 gap-6 md:grid-cols-2"
      >
        <section class="space-y-3 rounded-lg border p-4">
          <Skeleton class="h-6 w-32" />
          <div class="grid grid-cols-2 gap-2">
            <Skeleton class="h-5 w-20" /><Skeleton class="h-5 w-3/4" />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <Skeleton class="h-5 w-20" /><Skeleton class="h-5 w-3/4" />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <Skeleton class="h-5 w-20" /><Skeleton class="h-5 w-3/4" />
          </div>
        </section>
        <section class="space-y-3 rounded-lg border p-4">
          <Skeleton class="h-6 w-40" />
          <div class="grid grid-cols-2 gap-2">
            <Skeleton class="h-5 w-24" /><Skeleton class="h-5 w-3/4" />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <Skeleton class="h-5 w-24" /><Skeleton class="h-5 w-3/4" />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <Skeleton class="h-5 w-24" /><Skeleton class="h-5 w-3/4" />
          </div>
        </section>
      </div>

      <div
        v-else-if="ownerModal.data.value"
        class="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2"
      >
        <section class="rounded-lg border p-4">
          <h3 class="mb-2 text-lg font-semibold">User Info</h3>
          <div class="grid grid-cols-2 gap-2">
            <div class="font-medium">Name:</div>
            <div>{{ ownerModal.data.value.user?.name }}</div>
          </div>
        </section>
        <section class="rounded-lg border p-4">
          <h3 class="mb-2 text-lg font-semibold">Owner Verification</h3>
          <div class="grid grid-cols-2 gap-2">
            <div class="font-medium">Owner Status:</div>
            <div>{{ ownerModal.data.value.status?.name }}</div>
          </div>
        </section>
      </div>

      <div v-else-if="ownerModal.isError.value">
        <p class="text-red-500">Failed to load owner details.</p>
      </div>
    </DialogContent>
  </Dialog>

  <Dialog v-model:open="isAcceptModalOpen">
    <DialogContent class="max-w-md"> </DialogContent>
  </Dialog>
</template>
