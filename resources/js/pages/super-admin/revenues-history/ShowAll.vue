<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

type Revenue = {
  date?: string;
  month?: string;
  start_date?: string;
  end_date?: string;
  formatted_date: string;
  total: number;
  service_type?: string;
};

const props = defineProps<{
  revenues: Revenue[];
  period: 'daily' | 'weekly' | 'monthly';
}>();

// --- Page Title & Breadcrumbs ---
const title = computed(() => {
  switch (props.period) {
    case 'daily':
      return 'Revenues for All Franchises (Daily)';
    case 'weekly':
      return 'Revenues for All Franchises (Weekly)';
    case 'monthly':
      return 'Revenues for All Franchises (Monthly)';
    default:
      return 'Revenues for All Franchises';
  }
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Revenues', href: superAdmin.revenues().url },
  {
    title: 'All Franchises',
    href: `/super-admin/revenues/all?period=${props.period}`,
  },
];

// --- Filtered Data ---
const availableRevenues = computed(() =>
  props.revenues.filter((r) => r.formatted_date !== 'Grand Total'),
);

// --- Year / Month Filters ---
const availableYears: number[] = Array.from(
  new Set(
    availableRevenues.value
      .map((rev) => {
        if (props.period === 'daily' && rev.date)
          return new Date(rev.date).getFullYear();
        if (props.period === 'weekly' && rev.start_date)
          return new Date(rev.start_date).getFullYear();
        if (props.period === 'monthly' && rev.month)
          return Number(rev.month.split('-')[0]);
      })
      .filter((y): y is number => y !== undefined),
  ),
).sort((a, b) => b - a);

const selectedYear = ref<'all' | number>('all');
const selectedMonth = ref<'all' | string>('all');

// --- Service Type Dropdown ---
const serviceOptions = ['Trips', 'Boundary'];
const selectedServiceType = ref('Trips'); // default Trips
const isDropdownOpen = ref(false);
function toggleDropdown() {
  isDropdownOpen.value = !isDropdownOpen.value;
}
function selectOption(option: string) {
  selectedServiceType.value = option;
  isDropdownOpen.value = false;
}
function handleClickOutside(event: MouseEvent) {
  const dropdown = document.querySelector('.relative.inline-block');
  if (dropdown && !dropdown.contains(event.target as Node))
    isDropdownOpen.value = false;
}
onMounted(() => window.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => window.removeEventListener('click', handleClickOutside));

// --- Filtered Revenues ---
const filteredRevenues = computed(() => {
  let filtered = availableRevenues.value;

  if (selectedYear.value !== 'all') {
    filtered = filtered.filter((rev) => {
      const revYear =
        props.period === 'daily' && rev.date
          ? new Date(rev.date).getFullYear()
          : props.period === 'weekly' && rev.start_date
            ? new Date(rev.start_date).getFullYear()
            : props.period === 'monthly' && rev.month
              ? Number(rev.month.split('-')[0])
              : null;
      return revYear === selectedYear.value;
    });
  }

  if (props.period === 'daily' && selectedMonth.value !== 'all') {
    filtered = filtered.filter((rev) => {
      if (!rev.date) return false;
      const d = new Date(rev.date);
      return (
        `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}` ===
        selectedMonth.value
      );
    });
  }

  filtered = filtered.filter(
    (rev) => rev.service_type === selectedServiceType.value,
  );

  return filtered;
});

// --- Available Months ---
const availableMonths = computed(() => {
  if (props.period !== 'daily') return [];
  const months = new Set(
    availableRevenues.value
      .filter(
        (rev) =>
          selectedYear.value === 'all' ||
          new Date(rev.date!).getFullYear() === selectedYear.value,
      )
      .map((rev) =>
        rev.date
          ? `${new Date(rev.date).getFullYear()}-${String(new Date(rev.date).getMonth() + 1).padStart(2, '0')}`
          : '',
      ),
  );
  return Array.from(months)
    .map((m) => ({
      value: m,
      label: new Date(m + '-01').toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric',
      }),
    }))
    .sort((a, b) => (a.value > b.value ? -1 : 1));
});

// --- Export Modal ---
const showExportModal = ref(false);
const exportFormat = ref<'pdf' | 'excel' | 'csv'>('pdf');
const selectAll = ref(true);
const selectedRevenues = ref<Revenue[]>([]);

watch(
  [selectAll, selectedYear, selectedMonth, selectedServiceType],
  () => {
    selectedRevenues.value = selectAll.value ? [...filteredRevenues.value] : [];
  },
  { immediate: true },
);

function toggleRevenueSelection(rev: Revenue) {
  const index = selectedRevenues.value.indexOf(rev);
  if (index > -1) selectedRevenues.value.splice(index, 1);
  else selectedRevenues.value.push(rev);
}

function openExportModal(format: 'pdf' | 'excel' | 'csv') {
  exportFormat.value = format;
  showExportModal.value = true;
  selectedRevenues.value = selectAll.value ? [...filteredRevenues.value] : [];
}

function confirmExport() {
  const payload = selectedRevenues.value
    .map((rev) => {
      if (props.period === 'daily') return rev.date;
      if (props.period === 'weekly') return `${rev.start_date}|${rev.end_date}`;
      if (props.period === 'monthly') return rev.month;
      return '';
    })
    .filter(Boolean);

  const params = payload.length
    ? `?dates=${encodeURIComponent(payload.join(','))}&period=${props.period}&service_type=${selectedServiceType.value}`
    : `?period=${props.period}&service_type=${selectedServiceType.value}`;

  window.open(
    `/super-admin/revenues/all/export/${exportFormat.value}${params}`,
    '_blank',
  );
  showExportModal.value = false;
}
</script>

<template>
  <Head :title="title" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="m-5 rounded-2xl bg-white p-6 shadow-md">
      <!-- Header Start -->
      <div class="flex justify-between pb-5">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">
          {{ title }}
        </h2>

        <div class="relative flex gap-3">
          <!-- Dropdown Button -->
          <div class="relative inline-block">
            <Button
              @click="toggleDropdown"
              type="button"
              class="inline-flex w-32 justify-between gap-x-1.5 rounded-md border-1 border-black bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50"
            >
              {{ selectedServiceType }}
              <svg
                class="-mr-1 h-5 w-5 text-black"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </Button>

            <!-- Dropdown Menu -->
            <ul
              v-if="isDropdownOpen"
              class="absolute left-0 z-20 mt-1 w-32 rounded-md border bg-white shadow-lg"
            >
              <li
                v-for="option in serviceOptions"
                :key="option"
                @click="selectOption(option)"
                class="cursor-pointer px-4 py-2 hover:bg-gray-100"
              >
                {{ option }}
              </li>
            </ul>
          </div>

          <!-- Export Buttons -->
          <Button
            @click="openExportModal('pdf')"
            :disabled="availableRevenues.length === 0"
          >
            Export PDF
          </Button>
          <Button
            @click="openExportModal('excel')"
            :disabled="availableRevenues.length === 0"
          >
            Export Excel
          </Button>
          <Button
            @click="openExportModal('csv')"
            :disabled="availableRevenues.length === 0"
          >
            Export CSV
          </Button>
        </div>
      </div>
      <!-- Header End -->

      <!-- Table Start -->
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow>
              <TableCell>No</TableCell>
              <TableCell>Date/Period</TableCell>
              <TableCell>Total Amount</TableCell>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow
              v-for="(rev, index) in filteredRevenues"
              :key="rev.formatted_date"
            >
              <TableCell>{{ index + 1 }}</TableCell>
              <TableCell>{{ rev.formatted_date }}</TableCell>
              <TableCell>
                ₱{{
                  Number(rev.total).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                  })
                }}
              </TableCell>
            </TableRow>

            <TableRow v-if="filteredRevenues.length === 0">
              <TableCell colspan="3" class="text-center">
                No revenues found for this period.
              </TableCell>
            </TableRow>
          </TableBody>

          <TableBody v-if="availableRevenues.length === 0">
            <TableRow>
              <TableCell colspan="3" class="text-center">
                No revenues found for this period.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <!-- Table End -->
    </div>

    <!-- Modal Start -->
    <div
      v-if="showExportModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
      <div class="w-[600px] rounded-lg bg-white p-6">
        <h3 class="mb-2 text-lg font-semibold">
          Select {{ period }}(s) Before Export
        </h3>
        <hr />
        <div class="flex flex-col gap-3 pt-4">
          <label class="font-bold">Year</label>
          <select
            v-model="selectedYear"
            class="w-full rounded border px-2 py-1"
          >
            <option value="all">All</option>
            <option v-for="(year, i) in availableYears" :key="i" :value="year">
              {{ year }}
            </option>
          </select>

          <div v-if="period === 'daily'" class="flex flex-col gap-2">
            <label class="font-bold">Month</label>
            <select
              v-model="selectedMonth"
              class="w-full rounded border px-2 py-1"
            >
              <option value="all">All</option>
              <option
                v-for="(month, i) in availableMonths"
                :key="i"
                :value="month.value"
              >
                {{ month.label }}
              </option>
            </select>
          </div>

          <label
            class="flex cursor-pointer items-center gap-2 rounded border px-2 py-[4px] font-bold"
          >
            <input
              type="checkbox"
              v-model="selectAll"
              :disabled="filteredRevenues.length === 0"
            />
            All ({{ filteredRevenues.length }})
          </label>

          <div class="max-h-64 overflow-y-auto rounded border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableCell class="w-10">#</TableCell>
                  <TableCell>Date/Period</TableCell>
                  <TableCell>Total Amount</TableCell>
                  <TableCell>Select</TableCell>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="(rev, index) in filteredRevenues"
                  :key="rev.formatted_date"
                  :class="selectedRevenues.includes(rev) ? 'bg-blue-50' : ''"
                >
                  <TableCell>{{ index + 1 }}</TableCell>
                  <TableCell>{{ rev.formatted_date }}</TableCell>
                  <TableCell>
                    ₱{{
                      Number(rev.total).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                      })
                    }}
                  </TableCell>
                  <TableCell>
                    <input
                      type="checkbox"
                      :checked="selectedRevenues.includes(rev)"
                      @change="toggleRevenueSelection(rev)"
                    />
                  </TableCell>
                </TableRow>
                <TableRow v-if="filteredRevenues.length === 0">
                  <TableCell colspan="4" class="text-center text-gray-500">
                    No data to display with current filters.
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>

        <div class="mt-4 flex justify-end gap-3">
          <Button
            @click="showExportModal = false"
            class="bg-gray-400 hover:bg-gray-500"
          >
            Cancel
          </Button>
          <Button
            @click="confirmExport"
            class="bg-auth-blue text-white hover:bg-brand-blue"
            :disabled="selectedRevenues.length === 0"
          >
            Export {{ selectedRevenues.length }} item(s)
          </Button>
        </div>
      </div>
    </div>
    <!-- Modal Start -->
  </AppLayout>
</template>

<!-- Dropdown CSS -->
<style scoped>
.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: all 0.15s ease-out;
}
.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
