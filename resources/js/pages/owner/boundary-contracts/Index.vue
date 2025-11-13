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
import { Head, router } from '@inertiajs/vue3';
import { Calendar, Check, Search, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

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

interface ContractsPaginator {
  current_page: number;
  data: Contract[];
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

interface Props {
  contracts: ContractsPaginator;
}

const { contracts } = defineProps<Props>();
const paginator = ref(contracts);

// -------------------------
// Watcher: update paginator when props change
// -------------------------
watch(
  () => contracts,
  (newContracts) => {
    paginator.value = newContracts;
  },
  { deep: true },
);

// -------------------------
// Breadcrumbs
// -------------------------
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Boundary Contracts', href: finance.boundaryContracts().url },
];

// -------------------------
// Filters / Search
// -------------------------
const globalFilter = ref('');
const statusFilter = ref('all');
const depositFilter = ref('all');

// -------------------------
// Dialog
// -------------------------
const selectedContract = ref<Contract | null>(null);
const showDepositDialog = ref(false);

const openDepositDialog = (contract: Contract) => {
  selectedContract.value = contract;
  showDepositDialog.value = true;
};

const handleDepositAction = (action: 'approved' | 'flagged') => {
  if (selectedContract.value) {
    const index = paginator.value.data.findIndex(
      (item) => item.id === selectedContract.value!.id,
    );
    if (index !== -1) {
      paginator.value.data[index].depositStatus = action;
    }
  }
  showDepositDialog.value = false;
};

// -------------------------
// Helpers
// -------------------------
const getStatusVariant = (status: string | undefined) => {
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

const getDepositVariant = (status?: string) => {
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

// -------------------------
// Computed: Filtered data for client-side search
// -------------------------
const filteredData = computed(() => {
  if (!globalFilter.value) return paginator.value.data;
  const search = globalFilter.value.toLowerCase();
  return paginator.value.data.filter((item) =>
    Object.values(item)
      .filter((v) => v !== null && v !== undefined)
      .some((v) => v.toString().toLowerCase().includes(search)),
  );
});

// -------------------------
// Pagination links without Previous/Next
// -------------------------
const paginationLinks = computed(() =>
  paginator.value.links.filter(
    (link) => link.label !== 'Previous' && link.label !== 'Next',
  ),
);

// -------------------------
// Pagination / server-side navigation
// -------------------------
const goToPage = (url: string | null) => {
  if (!url) return;
  router.get(
    url,
    {
      status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
      depositStatus:
        depositFilter.value !== 'all' ? depositFilter.value : undefined,
      search: globalFilter.value || undefined,
    },
    { preserveState: true, preserveScroll: true },
  );
};

// Watch filters / search and reload table
watch([statusFilter, depositFilter, globalFilter], () => {
  router.get(
    paginator.value.path,
    {
      status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
      depositStatus:
        depositFilter.value !== 'all' ? depositFilter.value : undefined,
      search: globalFilter.value || undefined,
      per_page: paginator.value.per_page,
    },
    { preserveState: true, preserveScroll: true },
  );
});
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
            <SelectValue>{{
              statusFilter === 'all' ? 'Filter by status' : statusFilter
            }}</SelectValue>
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Status</SelectItem>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="paid">Paid</SelectItem>
            <SelectItem value="overdue">Overdue</SelectItem>
          </SelectContent>
        </Select>

        <!-- <Select v-model="depositFilter">
          <SelectTrigger class="w-full md:w-48">
            <SelectValue>{{
              depositFilter === 'all' ? 'Deposit status' : depositFilter
            }}</SelectValue>
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Deposits</SelectItem>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="approved">Approved</SelectItem>
            <SelectItem value="flagged">Flagged</SelectItem>
          </SelectContent>
        </Select> -->
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
              <TableCell colspan="6" class="py-6 text-center text-gray-500">
                No results found.
              </TableCell>
            </TableRow>

            <TableRow v-for="contract in filteredData" :key="contract.id">
              <TableCell class="font-medium">{{ contract.name }}</TableCell>
              <TableCell>{{ contract.franchise || '—' }}</TableCell>
              <TableCell class="max-w-xs truncate">{{
                contract.coverage_area
              }}</TableCell>
              <TableCell class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-gray-400" />
                {{ new Date(contract.start_date).toLocaleDateString() }} -
                {{ new Date(contract.end_date).toLocaleDateString() }}
              </TableCell>
              <TableCell>
                <Badge :variant="getStatusVariant(contract.status)">
                  {{ contract.status }}
                </Badge>
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
                  :class="{ 'bg-gray-100': link.active }"
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

      <!-- Deposit Dialog -->
      <Dialog v-model:open="showDepositDialog">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Review Deposit</DialogTitle>
            <DialogDescription
              >Contract: {{ selectedContract?.name }}</DialogDescription
            >
          </DialogHeader>

          <div v-if="selectedContract" class="space-y-2 py-4">
            <p>
              <strong>Franchise:</strong>
              {{ selectedContract.franchise || '—' }}
            </p>
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
