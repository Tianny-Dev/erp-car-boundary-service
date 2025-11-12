<script setup lang="ts">
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
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import finance from '@/routes/finance';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Calendar, Check, Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Contract {
  id: number;
  name: string;
  coverage_area: string;
  contract_terms: string;
  start_date: string;
  end_date: string;
  status: 'pending' | 'paid' | 'overdue' | string;
  franchise: string | null;
  branch: string | null;
  depositStatus?: 'pending' | 'approved' | 'flagged';
}

const props = defineProps<{ contracts: Contract[] }>();
const data = ref(props.contracts);

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Boundary Contracts',
    href: finance.boundaryContracts().url,
  },
];

const globalFilter = ref('');
const statusFilter = ref('all');
const depositFilter = ref('all');
const showDepositDialog = ref(false);
const selectedContract = ref<Contract | null>(null);

// Filter logic
const filteredData = computed(() => {
  let filtered = data.value;

  // Status filter
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter((item) => item.status === statusFilter.value);
  }

  // Deposit filter
  if (depositFilter.value !== 'all') {
    filtered = filtered.filter(
      (item) => item.depositStatus === depositFilter.value,
    );
  }

  // Global search
  if (globalFilter.value) {
    const search = globalFilter.value.toLowerCase();
    filtered = filtered.filter(
      (item) =>
        item.name.toLowerCase().includes(search) ||
        item.coverage_area.toLowerCase().includes(search) ||
        item.contract_terms.toLowerCase().includes(search) ||
        (item.franchise?.toLowerCase() ?? '').includes(search) ||
        (item.branch?.toLowerCase() ?? '').includes(search),
    );
  }

  return filtered;
});

// Helpers
const getStatusVariant = (status: 'pending' | 'paid' | 'overdue' | string) => {
  switch (status) {
    case 'pending':
      return 'secondary';
    case 'paid':
      return 'default';
    case 'overdue':
      return 'destructive';
    default:
      return 'secondary';
  }
};

const getDepositVariant = (status?: 'pending' | 'approved' | 'flagged') => {
  switch (status) {
    case 'pending':
      return 'secondary';
    case 'approved':
      return 'default';
    case 'flagged':
      return 'destructive';
    default:
      return 'secondary';
  }
};

const openDepositDialog = (contract: Contract) => {
  selectedContract.value = contract;
  showDepositDialog.value = true;
};

const handleDepositAction = (action: 'approved' | 'flagged') => {
  if (selectedContract.value) {
    const index = data.value.findIndex(
      (item) => item.id === selectedContract.value!.id,
    );
    if (index !== -1) {
      data.value[index].depositStatus = action;
    }
  }
  showDepositDialog.value = false;
};
</script>

<template>
  <Head title="Boundary Contracts" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div>
        <h1 class="mb-1 text-3xl font-bold">Boundary Contracts</h1>
        <p class="text-gray-600">
          Monitor due payments, set schedules, and approve boundary deposits
        </p>
      </div>

      <!-- Filters -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center">
        <div class="relative flex-1">
          <Search
            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
          />
          <input
            v-model="globalFilter"
            placeholder="Search contracts..."
            class="w-full rounded-md border px-10 py-2"
          />
        </div>

        <Select v-model="statusFilter">
          <SelectTrigger class="w-full md:w-48">
            <SelectValue placeholder="Filter by status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Status</SelectItem>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="paid">Paid</SelectItem>
            <SelectItem value="overdue">Overdue</SelectItem>
          </SelectContent>
        </Select>

        <Select v-model="depositFilter">
          <SelectTrigger class="w-full md:w-48">
            <SelectValue placeholder="Deposit status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Deposits</SelectItem>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="approved">Approved</SelectItem>
            <SelectItem value="flagged">Flagged</SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Table -->
      <div class="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Contract Name</TableHead>
              <TableHead>Franchise</TableHead>
              <TableHead>Coverage Area</TableHead>
              <TableHead>Duration</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-if="filteredData.length === 0">
              <TableCell colspan="8" class="py-6 text-center text-gray-500">
                No results found.
              </TableCell>
            </TableRow>

            <TableRow v-for="contract in filteredData" :key="contract.id">
              <TableCell class="font-medium">{{ contract.name }}</TableCell>
              <TableCell>{{ contract.franchise }}</TableCell>
              <TableCell class="max-w-xs truncate">{{
                contract.coverage_area
              }}</TableCell>
              <TableCell class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-gray-400" />
                {{ new Date(contract.start_date).toLocaleDateString() }} -
                {{ new Date(contract.end_date).toLocaleDateString() }}
              </TableCell>
              <TableCell>
                <Badge :variant="getStatusVariant(contract.status)">{{
                  contract.status
                }}</Badge>
              </TableCell>
              <TableCell>
                <Button
                  size="sm"
                  variant="outline"
                  @click="openDepositDialog(contract)"
                >
                  Review Deposit
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Deposit Review Dialog -->
      <Dialog v-model:open="showDepositDialog">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Review Deposit</DialogTitle>
            <DialogDescription>
              Contract: {{ selectedContract?.name }}
            </DialogDescription>
          </DialogHeader>

          <div v-if="selectedContract" class="space-y-2 py-4">
            <p><strong>Franchise:</strong> {{ selectedContract.franchise }}</p>
            <p>
              <strong>Coverage Area:</strong>
              {{ selectedContract.coverage_area }}
            </p>
            <p>
              <strong>Current Status:</strong>
              <Badge
                :variant="getDepositVariant(selectedContract.depositStatus)"
              >
                {{ selectedContract.depositStatus ?? 'N/A' }}
              </Badge>
            </p>
          </div>

          <DialogFooter>
            <Button variant="outline" @click="showDepositDialog = false"
              >Cancel</Button
            >
            <Button
              variant="destructive"
              @click="handleDepositAction('flagged')"
            >
              <X class="mr-2 h-4 w-4" /> Flag
            </Button>
            <Button @click="handleDepositAction('approved')">
              <Check class="mr-2 h-4 w-4" /> Approve
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
