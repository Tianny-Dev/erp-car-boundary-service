<script setup lang="ts">
import {
  ArcElement,
  ChartData,
  Chart as ChartJS,
  ChartOptions,
  Legend,
  Title,
  Tooltip,
} from 'chart.js';
import { computed, onMounted, ref } from 'vue';
import { Doughnut } from 'vue-chartjs';

ChartJS.register(Title, Tooltip, Legend, ArcElement);

interface Props {
  data: { name: string; total: number; predicted?: number }[];
  category?: string;
  title?: string;
}

const props = defineProps<Props>();

// --- Theme Detection ---
const isDark = ref(false);

const updateTheme = () => {
  isDark.value = document.documentElement.classList.contains('dark');
};

onMounted(() => {
  updateTheme();
  // Keeps the chart in sync if the user toggles a theme switch
  const observer = new MutationObserver(updateTheme);
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class'],
  });
});

// --- Reactive Chart Data ---
const chartData = computed<ChartData<'doughnut'>>(() => ({
  labels: props.data.map((item) => item.name),
  datasets: [
    {
      label: props.category ?? 'Total',
      data: props.data.map((item) => item.total),
      backgroundColor: [
        '#3b82f6', // blue
        '#22c55e', // green
        '#f59e0b', // amber
        '#ef4444', // red
        '#a855f7', // purple
        '#06b6d4', // cyan
      ],
      // Border color matches the background to create a "gap" effect
      borderColor: isDark.value ? '#111827' : '#ffffff',
      borderWidth: 2,
      hoverOffset: 15,
    },
  ],
}));

// --- Reactive Options (Fixed Red Lines) ---
const options = computed<ChartOptions<'doughnut'>>(() => {
  const textColor = isDark.value ? '#94a3b8' : '#4b5563';
  const titleColor = isDark.value ? '#f8fafc' : '#111827';

  return {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '75%', // Thinner doughnut for a more modern look
    plugins: {
      legend: {
        position: 'bottom' as const, // "as const" fixes the red line
        labels: {
          color: textColor,
          usePointStyle: true,
          padding: 20,
          font: {
            size: 12,
            weight: '500' as const, // "as const" fixes the red line
          },
        },
      },
      tooltip: {
        backgroundColor: isDark.value ? '#1e293b' : '#ffffff',
        titleColor: titleColor,
        bodyColor: textColor,
        borderColor: isDark.value ? '#334155' : '#e2e8f0',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 8,
      },
      title: {
        display: !!props.title,
        text: props.title,
        color: titleColor,
        font: {
          size: 16,
          weight: 'bold' as const, // "as const" fixes the red line
        },
      },
    },
  };
});
</script>

<template>
  <div class="mx-auto h-[350px] w-full max-w-md">
    <Doughnut :data="chartData" :options="options" />
  </div>
</template>
