<script setup lang="ts">
import {
  CategoryScale,
  ChartData,
  Chart as ChartJS,
  ChartOptions,
  Filler,
  Legend,
  LinearScale,
  LineElement,
  PointElement,
  Title,
  Tooltip,
} from 'chart.js';
import { computed, ref, onMounted } from 'vue';
import { Line } from 'vue-chartjs';

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
);

// Props
interface DataItem {
  date: string;
  Revenue: number;
  Expenses: number;
}

const props = defineProps<{
  data: DataItem[];
  categories?: string[];
  colors?: string[];
}>();

const colors = props.colors ?? ['#3b82f6', '#22c55e'];
const categories = props.categories ?? ['Revenue', 'Expenses'];

// 🌙 Detect dark mode
const isDark = ref(false);

onMounted(() => {
  isDark.value = document.documentElement.classList.contains('dark');
});

// Dynamic theme colors
const textColor = computed(() => (isDark.value ? '#e5e7eb' : '#374151'));
const gridColor = computed(() => (isDark.value ? '#374151' : '#e5e7eb'));
const tooltipBg = computed(() => (isDark.value ? '#1f2937' : '#111827'));

// Chart Data
const chartData = computed<ChartData<'line'>>(() => ({
  labels: props.data.map((d) => d.date),
  datasets: categories.map((cat, i) => ({
    label: cat,
    data: props.data.map((d) => d[cat as keyof DataItem] as number),
    borderColor: colors[i] ?? colors[0],
    backgroundColor: (colors[i] ?? colors[0]) + '33',
    tension: 0.4,
    fill: true,
    pointRadius: 4,
    pointHoverRadius: 6,
  })),
}));

// Chart Options (dynamic)
const chartOptions = computed<ChartOptions<'line'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
  },
  plugins: {
    legend: {
      position: 'top',
      labels: { color: textColor.value },
    },
    title: {
      display: true,
      text: 'Revenue vs Expenses',
      color: textColor.value,
      font: { size: 16, weight: 'bold' },
    },
    tooltip: {
      backgroundColor: tooltipBg.value,
      borderColor: gridColor.value,
      borderWidth: 1,
      titleColor: '#f9fafb',
      bodyColor: '#f9fafb',
      callbacks: {
        label: (context) => {
          const label = context.dataset.label || '';
          const value = context.parsed.y;
          if (value === null) return `${label}: N/A`;
          return `${label}: ₱${value.toLocaleString()}`;
        },
      },
    },
  },
  scales: {
    x: {
      ticks: { color: textColor.value },
      grid: { display: false },
    },
    y: {
      beginAtZero: true,
      ticks: {
        color: textColor.value,
        callback: (val) => `₱${Number(val).toLocaleString()}`,
      },
      grid: { color: gridColor.value },
    },
  },
}));
</script>

<template>
  <div class="h-[400px] w-full">
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>
