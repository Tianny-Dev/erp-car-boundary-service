<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import {
  Pagination,
  PaginationContent,
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
import owner from '@/routes/owner';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Calendar, Eye, Pencil, PlusIcon, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Contract {
  id: number;
  name: string;
  amount: string;
  coverage_area: string;
  contract_terms: string;
  start_date: string;
  end_date: string;
  status: string;
  driver_username: string;
  driver_email: string;
  driver_phone: string;
  franchise: string | null;
  vehicle_info: string; // Added this
}

const visibleContractFields = [
  'name',
  'amount',
  'vehicle_info', // Added to dialog
  'coverage_area',
  'contract_terms',
  'start_date',
  'end_date',
  'status',
  'driver_username',
  'driver_email',
  'driver_phone',
  'franchise',
] as const;

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

watch(
  () => contracts,
  (newContracts) => {
    paginator.value = newContracts;
  },
  { deep: true },
);

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Boundary Contracts', href: owner.boundaryContracts.index().url },
];

const globalFilter = ref('');
const statusFilter = ref('all');

const selectedContract = ref<Contract | null>(null);
const showDialog = ref(false);

const openDialog = (contract: Contract) => {
  selectedContract.value = contract;
  showDialog.value = true;
};

const getBadgeClass = (status: string) => {
  switch (status.toLowerCase()) {
    case 'active':
      return 'bg-blue-500 hover:bg-blue-600';
    case 'suspended':
    case 'retired':
      return 'bg-rose-500 hover:bg-rose-600';
    default:
      return 'bg-gray-500 hover:bg-gray-600';
  }
};

const filteredData = computed(() => {
  if (!globalFilter.value) return paginator.value.data;
  const search = globalFilter.value.toLowerCase();
  return paginator.value.data.filter((item) =>
    Object.values(item)
      .filter((v) => v !== null && v !== undefined)
      .some((v) => v.toString().toLowerCase().includes(search)),
  );
});

const paginationLinks = computed(() =>
  paginator.value.links.filter(
    (link) => link.label !== 'Previous' && link.label !== 'Next',
  ),
);

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

watch([statusFilter, globalFilter], () => {
  router.get(
    paginator.value.path,
    {
      status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
      search: globalFilter.value || undefined,
      per_page: paginator.value.per_page,
    },
    { preserveState: true, preserveScroll: true },
  );
});

const createContract = () => router.get(owner.boundaryContracts.create().url);
const editContract = (id: number) =>
  router.get(owner.boundaryContracts.edit({ boundary_contract: id }).url);
</script>

<template>
  <Head title="Boundary Contracts" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div>
        <h1 class="mb-1 text-3xl font-bold">Boundary Contracts</h1>
        <p class="text-gray-600">
          Manage Contracts for drivers assigned to vehicles.
        </p>
      </div>

      <div
        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
      >
        <div class="relative w-full sm:max-w-md sm:flex-1">
          <Search
            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
          />
          <input
            v-model="globalFilter"
            placeholder="Search contracts..."
            class="w-full rounded-md border px-10 py-2"
          />
        </div>
        <div
          class="grid w-full grid-cols-2 gap-3 sm:flex sm:w-auto sm:flex-row sm:items-center sm:gap-4"
        >
          <Select v-model="statusFilter">
            <SelectTrigger class="w-full sm:w-[150px] md:w-48">
              <SelectValue>{{
                statusFilter === 'all' ? 'Filter by status' : statusFilter
              }}</SelectValue>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Status</SelectItem>
              <SelectItem value="active">Active</SelectItem>
              <SelectItem value="retired">Retired</SelectItem>
              <SelectItem value="suspended">Suspended</SelectItem>
            </SelectContent>
          </Select>
          <Button class="w-full sm:w-auto" @click="createContract">
            <PlusIcon class="mr-2 h-4 w-4" /> Add Contract
          </Button>
        </div>
      </div>

      <div class="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Contract Name</TableHead>
              <TableHead>Driver</TableHead>
              <TableHead>Vehicle</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Duration</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="filteredData.length === 0">
              <TableCell colspan="7" class="py-6 text-center text-gray-500"
                >No results found.</TableCell
              >
            </TableRow>
            <TableRow v-for="contract in filteredData" :key="contract.id">
              <TableCell class="font-medium">{{ contract.name }}</TableCell>
              <TableCell>{{ contract.driver_username || '—' }}</TableCell>
              <TableCell>{{ contract.vehicle_info }}</TableCell>
              <TableCell class="font-medium">₱{{ contract.amount }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-2 text-xs">
                  <Calendar class="h-3 w-3 text-gray-400" />
                  {{ new Date(contract.start_date).toLocaleDateString() }} -
                  {{ new Date(contract.end_date).toLocaleDateString() }}
                </div>
              </TableCell>
              <TableCell>
                <Badge
                  :class="getBadgeClass(contract.status)"
                  class="text-white capitalize"
                  >{{ contract.status }}</Badge
                >
              </TableCell>
              <TableCell class="flex gap-2">
                <Button
                  size="sm"
                  variant="outline"
                  @click="openDialog(contract)"
                  ><Eye class="h-4 w-4"
                /></Button>
                <Button
                  v-if="contract.status === 'active'"
                  size="sm"
                  variant="outline"
                  @click="editContract(contract.id)"
                  ><Pencil class="h-4 w-4"
                /></Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

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

      <Dialog v-model:open="showDialog">
        <DialogContent class="flex max-h-[90vh] flex-col sm:max-w-lg">
          <DialogHeader class="border-b pb-2"
            ><DialogTitle>Contract Details</DialogTitle></DialogHeader
          >
          <div class="overflow-y-auto py-4">
            <div
              v-if="selectedContract"
              class="grid grid-cols-1 gap-y-3 sm:grid-cols-2"
            >
              <template v-for="key in visibleContractFields" :key="key">
                <div class="text-xs font-bold text-gray-500 uppercase">
                  {{ key.replace(/_/g, ' ') }}:
                </div>
                <div class="pb-2 text-sm">
                  {{ selectedContract[key] || 'N/A' }}
                </div>
              </template>
            </div>
          </div>
          <DialogFooter
            ><Button variant="outline" @click="showDialog = false"
              >Close</Button
            ></DialogFooter
          >
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
