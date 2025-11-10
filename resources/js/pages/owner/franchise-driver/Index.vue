<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
  FlexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
  type ColumnDef,
} from '@tanstack/vue-table';
import { ArrowUpDown, Search } from 'lucide-vue-next';
import { computed, h, ref } from 'vue';

interface Driver {
  id: number;
  name: string;
  username: string;
  email: string;
  phone: string;
  status: string;
}

const props = defineProps<{ drivers: Driver[] }>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Driver Management', href: owner.drivers.index().url },
];

const globalFilter = ref('');
const statusFilter = ref('all');
const data = ref<Driver[]>(props.drivers);

const filteredData = computed(() => {
  let filtered = data.value;

  if (statusFilter.value !== 'all') {
    filtered = filtered.filter((d) => d.status === statusFilter.value);
  }

  if (globalFilter.value) {
    const search = globalFilter.value.toLowerCase();
    filtered = filtered.filter(
      (d) =>
        d.name.toLowerCase().includes(search) ||
        d.username.toLowerCase().includes(search) ||
        d.email.toLowerCase().includes(search),
    );
  }

  return filtered;
});

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'active':
      return 'success';
    case 'pending':
      return 'secondary';
    case 'retired':
      return 'destructive';
    default:
      return 'secondary';
  }
};

const columns: ColumnDef<Driver>[] = [
  { accessorKey: 'name', header: 'Name' },
  { accessorKey: 'username', header: 'Username' },
  { accessorKey: 'email', header: 'Email' },
  { accessorKey: 'phone', header: 'Phone' },
  {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ row }) =>
      h(
        Badge,
        { variant: getStatusVariant(row.getValue('status')) as any },
        () => row.getValue('status'),
      ),
  },
  {
    id: 'actions',
    header: 'Actions',
    cell: ({ row }) =>
      h(
        Button,
        {
          size: 'sm',
          variant: 'outline',
          onClick: () => router.put(`/owner/drivers/${row.original.id}/status`),
        },
        () => 'Toggle Status',
      ),
  },
];

const table = useVueTable({
  get data() {
    return filteredData.value;
  },
  columns,
  getCoreRowModel: getCoreRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  initialState: { pagination: { pageSize: 10 } },
});
</script>

<template>
  <Head title="Driver Management" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
      <!-- Header -->
      <div class="mb-8">
        <h1 class="mb-2 text-3xl font-bold">Driver Management</h1>
        <p class="text-gray-600">Manage all franchise drivers</p>
      </div>

      <!-- Filters -->
      <div class="mb-6 flex flex-col gap-4 md:flex-row">
        <div class="relative flex-1">
          <Search
            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400"
          />
          <input
            v-model="globalFilter"
            placeholder="Search by name, username or email"
            class="w-full rounded-md border px-10 py-2"
          />
        </div>

        <Select v-model="statusFilter">
          <SelectTrigger class="w-full md:w-48">
            <SelectValue placeholder="Filter by status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Status</SelectItem>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="retired">Retired</SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Table -->
      <div class="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow
              v-for="headerGroup in table.getHeaderGroups()"
              :key="headerGroup.id"
            >
              <TableHead v-for="header in headerGroup.headers" :key="header.id">
                <div
                  v-if="!header.isPlaceholder"
                  @click="header.column.getToggleSortingHandler()?.($event)"
                  :class="
                    header.column.getCanSort()
                      ? 'flex cursor-pointer items-center gap-2 select-none'
                      : ''
                  "
                >
                  <FlexRender
                    :render="header.column.columnDef.header"
                    :props="header.getContext()"
                  />
                  <ArrowUpDown
                    v-if="header.column.getCanSort()"
                    class="h-4 w-4"
                  />
                </div>
              </TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-if="table.getRowModel().rows.length === 0">
              <TableCell :colspan="columns.length" class="h-24 text-center">
                No results found.
              </TableCell>
            </TableRow>
            <TableRow v-for="row in table.getRowModel().rows" :key="row.id">
              <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                <FlexRender
                  :render="cell.column.columnDef.cell"
                  :props="cell.getContext()"
                />
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination -->
      <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-gray-600">
          Showing
          {{
            table.getState().pagination.pageIndex *
              table.getState().pagination.pageSize +
            1
          }}
          to
          {{
            Math.min(
              (table.getState().pagination.pageIndex + 1) *
                table.getState().pagination.pageSize,
              filteredData.length,
            )
          }}
          of {{ filteredData.length }} results
        </div>
        <div class="flex gap-2">
          <Button
            variant="outline"
            size="sm"
            @click="table.previousPage()"
            :disabled="!table.getCanPreviousPage()"
          >
            Previous
          </Button>
          <Button
            variant="outline"
            size="sm"
            @click="table.nextPage()"
            :disabled="!table.getCanNextPage()"
          >
            Next
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
