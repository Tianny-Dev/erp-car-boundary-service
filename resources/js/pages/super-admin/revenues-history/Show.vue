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
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

type Revenue = {
  date?: string;
  start_date?: string;
  end_date?: string;
  month?: string;
  total: number;
  service_type?: string;
};

const props = defineProps<{
  franchise: { id: number; name: string };
  revenues: Revenue[];
  period: string;
}>();

const { franchise, revenues, period } = props;

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Revenues', href: '/super-admin/revenues' },
  { title: franchise.name, href: `/super-admin/revenues/${franchise.id}` },
];

// Modal state
const showExportModal = ref(false);
const exportFormat = ref<'pdf' | 'excel' | 'csv'>('pdf');
const selectAll = ref(true);
const selectedRevenues = ref<Revenue[]>([...revenues]);

watch(selectAll, (newVal) => {
  selectedRevenues.value = newVal ? [...revenues] : [];
});

function formatDate(dateStr: string) {
  const date = new Date(dateStr!);
  return date.toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
  });
}

function formatMonth(monthStr: string) {
  const [year, month] = monthStr.split('-');
  return new Date(Number(year), Number(month) - 1).toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric',
  });
}

function formatWeeklyDate(rev: Revenue) {
  if (rev.start_date && rev.end_date) {
    const start = new Date(rev.start_date);
    const end = new Date(rev.end_date);
    const options: Intl.DateTimeFormatOptions = {
      month: 'long',
      day: 'numeric',
    };
    return `${start.toLocaleDateString('en-US', options)} – ${end.toLocaleDateString(
      'en-US',
      { ...options, year: 'numeric' },
    )}`;
  }
  return '';
}

function formatRevenueDate(rev: Revenue) {
  if (period === 'daily' && rev.date) return formatDate(rev.date);
  if (period === 'weekly' && rev.start_date && rev.end_date)
    return formatWeeklyDate(rev);
  if (period === 'monthly' && rev.month) return formatMonth(rev.month);
  return '';
}

function toggleRevenueSelection(rev: Revenue) {
  const index = selectedRevenues.value.indexOf(rev);
  if (index > -1) selectedRevenues.value.splice(index, 1);
  else selectedRevenues.value.push(rev);
}

function openExportModal(format: 'pdf' | 'excel' | 'csv') {
  exportFormat.value = format;
  showExportModal.value = true;
}

function confirmExport() {
  const payload = selectedRevenues.value
    .map((rev) => {
      if (period === 'daily') return rev.date;
      if (period === 'weekly') return `${rev.start_date}|${rev.end_date}`;
      if (period === 'monthly') return rev.month;
      return '';
    })
    .filter(Boolean);

  // ALWAYS send service type (All removed)
  const serviceParam = `&service_type=${encodeURIComponent(selectedOption.value)}`;

  const params = payload.length
    ? `?dates=${encodeURIComponent(payload.join(','))}&period=${period}${serviceParam}`
    : `?period=${period}${serviceParam}`;

  window.open(
    `/super-admin/revenues/${franchise.id}/export/${exportFormat.value}${params}`,
    '_blank',
  );
  showExportModal.value = false;
}

// Year filtering
const availableYears: number[] = Array.from(
  new Set(
    revenues
      .map((rev) => {
        if (period === 'daily' && rev.date)
          return new Date(rev.date).getFullYear();
        if (period === 'weekly' && rev.start_date)
          return new Date(rev.start_date).getFullYear();
        if (period === 'monthly' && rev.month)
          return Number(rev.month.split('-')[0]);
        return undefined;
      })
      .filter((year): year is number => typeof year === 'number'),
  ),
).sort((a, b) => b - a);

const selectedYear = ref<'all' | number>('all');
const selectedMonth = ref<'all' | string>('all');

// FILTERING (NO MORE ALL OPTION)
const filteredRevenues = computed(() => {
  let filtered = revenues;

  if (selectedYear.value !== 'all') {
    filtered = filtered.filter((rev) => {
      const revYear =
        period === 'daily' && rev.date
          ? new Date(rev.date).getFullYear()
          : period === 'weekly' && rev.start_date
            ? new Date(rev.start_date).getFullYear()
            : period === 'monthly' && rev.month
              ? Number(rev.month.split('-')[0])
              : null;
      return revYear === selectedYear.value;
    });
  }

  if (period === 'daily' && selectedMonth.value !== 'all') {
    filtered = filtered.filter((rev) => {
      if (!rev.date) return false;
      const d = new Date(rev.date);
      const monthKey = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(
        2,
        '0',
      )}`;
      return monthKey === selectedMonth.value;
    });
  }

  // ALWAYS filter by selected service type
  filtered = filtered.filter(
    (rev) => rev.service_type === selectedOption.value,
  );

  return filtered;
});

const availableMonths = computed(() => {
  if (period !== 'daily') return [];
  const months = new Set(
    revenues
      .filter((rev) => {
        if (selectedYear.value === 'all') return true;
        if (!rev.date) return false;
        const year = new Date(rev.date).getFullYear();
        return year === selectedYear.value;
      })
      .map((rev) => {
        if (!rev.date) return '';
        const d = new Date(rev.date);
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
      }),
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

watch([selectAll, selectedYear, selectedMonth], () => {
  selectedRevenues.value = selectAll.value ? [...filteredRevenues.value] : [];
});

// DROPDOWN — UPDATED
const isOpen = ref(false);

// Default = Trips
const selectedOption = ref('Trips');

// Options WITHOUT "All"
const options = ['Trips', 'Boundary'];

function toggleDropdown() {
  isOpen.value = !isOpen.value;
}

function selectOption(option: string) {
  selectedOption.value = option;
  isOpen.value = false;
}

function handleClickOutside(event: MouseEvent) {
  const dropdown = document.querySelector('.relative.inline-block');
  if (dropdown && !dropdown.contains(event.target as Node)) {
    isOpen.value = false;
  }
}

onMounted(() => {
  window.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  window.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <Head :title="`${franchise.name} Revenues`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="m-5 rounded-2xl bg-white p-6 shadow-md">
      <!-- Header Start -->
      <div class="flex justify-between pb-5">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">
          Revenues for {{ franchise.name }} (<span class="capitalize">{{
            period
          }}</span
          >)
        </h2>

        <div class="flex gap-3">
          <div class="relative inline-block text-left">
            <Button
              @click="toggleDropdown"
              type="button"
              class="inline-flex w-32 justify-between gap-x-1.5 rounded-md border-1 border-auth-blue bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50"
            >
              {{ selectedOption }}
              <svg
                class="-mr-1 h-5 w-5 text-auth-blue"
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

            <!-- Dropdown menu -->
            <transition name="fade-scale">
              <div
                v-if="isOpen"
                class="ring-opacity-5 absolute right-0 z-10 mt-1 w-32 origin-top-right rounded-md border-1 border-auth-blue bg-white shadow-lg ring-1 ring-black focus:outline-none"
              >
                <div class="py-1">
                  <button
                    v-for="option in options"
                    :key="option"
                    @click="selectOption(option)"
                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                  >
                    {{ option }}
                  </button>
                </div>
              </div>
            </transition>
          </div>

          <Button @click="openExportModal('pdf')"> Export PDF </Button>
          <Button @click="openExportModal('excel')"> Export Excel </Button>
          <Button @click="openExportModal('csv')"> Export CSV </Button>
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
              :key="rev.date || rev.start_date || rev.month"
            >
              <TableCell>{{ index + 1 }}</TableCell>
              <TableCell>{{ formatRevenueDate(rev) }}</TableCell>
              <TableCell>
                ₱{{
                  Number(rev.total).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                  })
                }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <!-- Table End -->
    </div>

    <!-- Export Modal Start -->
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
          <!-- Year Filter -->
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

          <!-- Month Filter for Daily -->
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

          <!-- Select All -->
          <label
            class="flex cursor-pointer items-center gap-2 rounded border px-2 py-[4px] font-bold"
          >
            <input type="checkbox" v-model="selectAll" />All
          </label>

          <!-- Revenue Table for Selection -->
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
                  :key="rev.date || rev.start_date || rev.month"
                  :class="selectedRevenues.includes(rev) ? 'bg-blue-50' : ''"
                >
                  <TableCell>{{ index + 1 }}</TableCell>
                  <TableCell>
                    {{
                      period === 'daily'
                        ? formatDate(rev.date!)
                        : period === 'weekly'
                          ? formatWeeklyDate(rev)
                          : formatMonth(rev.month!)
                    }}
                  </TableCell>
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
    <!-- Export Modal End -->
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
