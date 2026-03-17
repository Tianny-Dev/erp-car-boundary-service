<script setup lang="ts">
import {
  BarElement,
  CategoryScale,
  ChartData,
  Chart as ChartJS,
  ChartOptions,
  Legend,
  LinearScale,
  Title,
  Tooltip,
} from 'chart.js';
import { computed, ref, onMounted } from 'vue';
import { Bar } from 'vue-chartjs';

// Register Chart.js modules
ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
);

interface DataItem {
  name: string;
  expenses: number;
  revenue: number;
}

const props = defineProps<{
  data: DataItem[];
  categories?: string[];
  colors?: string[];
  yFormatter?: (value: number) => string;
}>();

// Defaults
const colors = props.colors ?? ['#ef4444', '#22c55e'];
const categories = props.categories ?? ['expenses', 'revenue'];
const formatY = props.yFormatter ?? ((v: number) => `$${v.toLocaleString()}`);

// 🌙 Detect dark mode
const isDark = ref(false);

onMounted(() => {
  isDark.value = document.documentElement.classList.contains('dark');
});

// Theme colors
const textColor = computed(() => (isDark.value ? '#e5e7eb' : '#374151'));
const gridColor = computed(() => (isDark.value ? '#374151' : '#e5e7eb'));
const tooltipBg = computed(() => (isDark.value ? '#1f2937' : '#111827'));

// Data
const chartData = computed<ChartData<'bar'>>(() => ({
  labels: props.data.map((d) => d.name),
  datasets: categories.map((cat, i) => ({
    label: cat.charAt(0).toUpperCase() + cat.slice(1),
    data: props.data.map((d) => d[cat as keyof DataItem] as number),
    backgroundColor: colors[i] ?? colors[0],
    borderRadius: 6,
    barThickness: 28,
  })),
}));

// Options (dynamic)
const chartOptions = computed<ChartOptions<'bar'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: {
        color: textColor.value,
        font: { size: 12 },
      },
    },
    title: {
      display: true,
      text: 'Revenue vs Expenses',
      color: textColor.value,
      font: { size: 16, weight: 'bold' },
    },
    tooltip: {
      backgroundColor: tooltipBg.value,
      titleColor: '#f9fafb',
      bodyColor: '#f9fafb',
      borderColor: gridColor.value,
      borderWidth: 1,
      callbacks: {
        label: (context) => {
          const label = context.dataset.label || '';
          const value = context.parsed.y;
          if (value === null) return `${label}: N/A`;
          return `${label}: ${formatY(value)}`;
        },
      },
    },
  },
  interaction: {
    mode: 'index',
    intersect: false,
  },
  scales: {
    x: {
      ticks: { color: textColor.value },
      grid: { display: false },
      stacked: false,
    },
    y: {
      beginAtZero: true,
      ticks: {
        color: textColor.value,
        callback: (val) => formatY(Number(val)),
      },
      grid: { color: gridColor.value },
    },
  },
}));
</script>

<template>
  <div class="h-[400px] w-full">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>
