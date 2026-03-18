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
  TooltipItem, // Added this for Type Safety
} from 'chart.js';
import { computed, onMounted, ref } from 'vue';
import { Line } from 'vue-chartjs';

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

interface DataItem {
  year: number;
  value: number;
}

const props = defineProps<{
  data: DataItem[];
  label?: string;
  colors?: string[];
  yFormatter?: (value: number) => string;
}>();

const isDark = ref(false);
const updateTheme = () => {
  isDark.value = document.documentElement.classList.contains('dark');
};

onMounted(() => {
  updateTheme();
  const observer = new MutationObserver(updateTheme);
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class'],
  });
});

const chartColor = props.colors?.[0] ?? '#3b82f6';
const chartLabel = props.label ?? 'Value';
const formatY =
  props.yFormatter ?? ((val: number) => `$${val.toLocaleString()}`);

const chartData = computed<ChartData<'line'>>(() => ({
  labels: props.data.map((item) => item.year),
  datasets: [
    {
      label: chartLabel,
      data: props.data.map((item) => item.value),
      borderColor: chartColor,
      backgroundColor: isDark.value ? `${chartColor}22` : `${chartColor}33`,
      tension: 0.4,
      fill: true,
      pointRadius: 4,
      pointHoverRadius: 6,
      pointBackgroundColor: chartColor,
      pointBorderColor: isDark.value ? '#1e293b' : '#ffffff',
      pointBorderWidth: 2,
    },
  ],
}));

// We explicitly type the return as ChartOptions<'line'>
const chartOptions = computed<ChartOptions<'line'>>(() => {
  const textColor = isDark.value ? '#94a3b8' : '#4b5563';
  const titleColor = isDark.value ? '#f8fafc' : '#111827';
  const gridColor = isDark.value
    ? 'rgba(255, 255, 255, 0.05)'
    : 'rgba(0, 0, 0, 0.05)';

  // Explicitly defining the object helps TS validate the nested properties
  const options: ChartOptions<'line'> = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: 'index',
      intersect: false,
    },
    plugins: {
      legend: {
        display: true,
        position: 'top' as const, // 'as const' fixes alignment/position type errors
        align: 'end' as const,
        labels: {
          color: textColor,
          boxWidth: 10,
          usePointStyle: true,
        },
      },
      tooltip: {
        enabled: true,
        backgroundColor: isDark.value ? '#1e293b' : '#ffffff',
        titleColor: titleColor,
        bodyColor: textColor,
        borderColor: isDark.value ? '#334155' : '#e2e8f0',
        borderWidth: 1,
        padding: 12,
        displayColors: false,
        callbacks: {
          label: (context: TooltipItem<'line'>) => {
            const val = context.parsed.y;
            return val !== null ? `${chartLabel}: ${formatY(val)}` : chartLabel;
          },
        },
      },
      title: {
        display: !!props.label,
        text: `${chartLabel} Over Years`,
        color: titleColor,
        font: {
          size: 16,
          weight: '600' as const, // Adding 'as const' here fixes the red line
        },
        align: 'start' as const,
        padding: { bottom: 20 },
      },
    },
    scales: {
      x: {
        ticks: { color: textColor, font: { size: 11 } },
        grid: { display: false },
      },
      y: {
        ticks: {
          color: textColor,
          font: { size: 11 },
          callback: (value) => formatY(Number(value)),
        },
        grid: { color: gridColor },
      },
    },
  };

  return options;
});
</script>

<template>
  <div class="h-[400px] w-full p-4">
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>
