<script setup lang="ts">
import {
  CategoryScale,
  ChartData,
  Chart as ChartJS,
  ChartOptions,
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
);

interface DataItem {
  year: number;
  'Growth Rate': number;
}

const props = defineProps<{
  data: DataItem[];
  colors?: string[];
  yFormatter?: (value: number) => string;
}>();

// Default color
const chartColor = props.colors?.[0] ?? '#3b82f6';

// Formatter
const formatY = props.yFormatter ?? ((val: number) => `$ ${val.toFixed(2)}`);

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
const chartData = computed<ChartData<'line'>>(() => ({
  labels: props.data.map((item) => item.year),
  datasets: [
    {
      label: 'Growth Rate',
      data: props.data.map((item) => item['Growth Rate']),
      borderColor: chartColor,
      backgroundColor: chartColor + '33',
      tension: 0.3,
      fill: true,
      pointRadius: 4,
      pointHoverRadius: 6,
    },
  ],
}));

// Options (dynamic)
const chartOptions = computed<ChartOptions<'line'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: { color: textColor.value },
    },
    tooltip: {
      backgroundColor: tooltipBg.value,
      titleColor: '#f9fafb',
      bodyColor: '#f9fafb',
      borderColor: chartColor,
      borderWidth: 1,
      callbacks: {
        label: (context) => {
          const value = context.parsed.y;
          if (value === null) return 'Growth Rate: N/A';
          return `Growth Rate: ${formatY(value)}`;
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
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>
