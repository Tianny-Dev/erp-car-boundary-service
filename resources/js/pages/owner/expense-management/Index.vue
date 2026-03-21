<script setup lang="ts">
import ExpenseTrendSparkLine from '@/components/owner/charts/expense-management/ExpenseTrendSparkLine.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import owner from '@/routes/owner';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { debounce } from 'lodash';

interface ExpensesPaginator<T = any> {
  current_page: number;
  data: T[];
  links: Array<{ url: string | null; label: string; active: boolean }>;
  next_page_url: string | null;
  per_page: number;
  prev_page_url: string | null;
  from: number | null;
  to: number | null;
  total: number;
}

interface Props {
  expenses: ExpensesPaginator;
  expenseTrendData: { year: number; expense: number }[];
}

const props = defineProps<Props>();
const paginator = ref(props.expenses);

watch(
  () => props.expenses,
  (newVal) => {
    paginator.value = newVal;
  },
  { deep: true },
);

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Expense Management', href: owner.expenseManagement().url },
];

const filters = ref({
  search: '',
  timePeriod: 'all',
});

const isGrouped = computed(() => {
  return ['daily', 'weekly', 'monthly', 'yearly'].includes(
    filters.value.timePeriod,
  );
});

const paginationLinks = computed(() => {
  return (
    paginator.value?.links?.filter(
      (l) => l.label !== 'Previous' && l.label !== 'Next',
    ) || []
  );
});

const applyFilters = debounce((url?: string) => {
  const baseUrl = owner.expenseManagement().url;

  if (!url && filters.value.timePeriod === 'all' && !filters.value.search) {
    router.get(baseUrl, {}, { preserveState: true, preserveScroll: true });
    return;
  }

  router.get(
    url || baseUrl,
    {
      search: filters.value.search || undefined,
      timePeriod:
        filters.value.timePeriod !== 'all'
          ? filters.value.timePeriod
          : undefined,
      per_page:
        paginator.value.per_page !== 10 ? paginator.value.per_page : undefined,
    },
    { preserveState: true, preserveScroll: true },
  );
}, 300);

const goToPage = (pageUrl: string | null) => {
  if (pageUrl) applyFilters(pageUrl);
};

watch(
  () => filters.value.timePeriod,
  () => {
    filters.value.search = '';
    applyFilters();
  },
);

watch(
  () => filters.value.search,
  () => {
    if (!isGrouped.value) applyFilters();
  },
);
</script>

<template>
  <Head title="Expense Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div>
        <h1 class="mb-1 text-3xl font-bold">Expense Records</h1>
        <p class="text-muted-foreground">Monitor and review your expenses.</p>
      </div>

      <div class="flex flex-col gap-4 md:flex-row md:items-center">
        <div class="relative flex-1">
          <Search
            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
          />
          <input
            v-model="filters.search"
            placeholder="Search by invoice or notes..."
            class="w-full rounded-md border bg-transparent px-10 py-2 focus:ring-2 focus:ring-brand-blue focus:outline-none"
            :disabled="isGrouped"
          />
        </div>

        <Select v-model="filters.timePeriod">
          <SelectTrigger class="w-full md:w-48">
            <SelectValue placeholder="Select Period" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Time (Detailed)</SelectItem>
            <SelectItem value="daily">Daily</SelectItem>
            <SelectItem value="weekly">Weekly</SelectItem>
            <SelectItem value="monthly">Monthly</SelectItem>
            <SelectItem value="yearly">Yearly</SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div class="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow v-if="!isGrouped">
              <TableHead>Invoice No</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Date Recorded</TableHead>
              <TableHead>Notes</TableHead>
            </TableRow>
            <TableRow v-else>
              <TableHead>
                {{
                  filters.timePeriod === 'daily'
                    ? 'Date'
                    : filters.timePeriod === 'weekly'
                      ? 'Week Range'
                      : filters.timePeriod === 'monthly'
                        ? 'Month'
                        : 'Year'
                }}
              </TableHead>
              <TableHead>Total Expense</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-if="!paginator.data || paginator.data.length === 0">
              <TableCell
                :colspan="isGrouped ? 2 : 5"
                class="py-10 text-center text-gray-500"
              >
                No records found.
              </TableCell>
            </TableRow>

            <template v-if="!isGrouped">
              <TableRow
                v-for="expense in paginator.data"
                :key="'exp-' + expense.id"
                class="transition-colors hover:bg-slate-100/50 dark:hover:bg-slate-800/50"
              >
                <TableCell class="font-medium">{{
                  expense.invoice_no
                }}</TableCell>
                <TableCell
                  >{{ expense.currency }}
                  {{ (expense.amount || 0).toLocaleString() }}</TableCell
                >
                <TableCell>{{ expense.created_at }}</TableCell>
                <TableCell
                  class="max-w-[200px] truncate text-xs text-muted-foreground"
                >
                  {{ expense.notes || '—' }}
                </TableCell>
              </TableRow>
            </template>

            <template v-else>
              <TableRow
                v-for="(row, idx) in paginator.data"
                :key="'grp-' + idx"
              >
                <TableCell class="font-medium">{{
                  row.display_date
                }}</TableCell>
                <TableCell class="font-bold">
                  ₱
                  {{
                    (row.total || 0).toLocaleString(undefined, {
                      minimumFractionDigits: 2,
                    })
                  }}
                </TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>
      </div>

      <div
        v-if="paginator.data?.length > 0"
        class="flex items-center justify-between pt-4"
      >
        <span class="text-sm text-muted-foreground">
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
            <template
              v-for="(link, index) in paginationLinks"
              :key="'link-' + index"
            >
              <PaginationItem
                v-if="!isNaN(Number(link.label))"
                :value="Number(link.label)"
              >
                <Button
                  variant="ghost"
                  size="sm"
                  :class="{
                    'bg-slate-200 font-bold dark:bg-slate-800': link.active,
                  }"
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

      <Card>
        <CardHeader><CardTitle>Expense Trend</CardTitle></CardHeader>
        <CardContent>
          <ExpenseTrendSparkLine
            :data="
              expenseTrendData.map((item) => ({
                year: item.year,
                value: item.expense,
              }))
            "
            label="Expenses"
            :colors="['#3b82f6']"
            :y-formatter="(val) => `₱ ${val.toLocaleString()}`"
          />
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
