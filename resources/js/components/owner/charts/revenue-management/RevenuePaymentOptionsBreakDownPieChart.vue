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

// 1. Detect if the system/app is in dark mode
const isDark = ref(false);

const updateTheme = () => {
  isDark.value = document.documentElement.classList.contains('dark');
};

onMounted(() => {
  updateTheme();
  // Optional: Observe changes if your theme switcher doesn't reload the page
  const observer = new MutationObserver(updateTheme);
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class'],
  });
});

// 2. Computed Data to react to theme changes
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
      // Border color shifts based on mode
      borderColor: isDark.value ? '#1e293b' : '#ffffff',
      borderWidth: 2,
      hoverOffset: 12,
    },
  ],
}));

// 3. Computed Options for text colors
const options = computed<ChartOptions<'doughnut'>>(() => {
  const textColor = isDark.value ? '#f8fafc' : '#1e293b';

  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          color: textColor,
          padding: 20,
          font: { size: 12, weight: '500' },
          usePointStyle: true,
        },
      },
      title: {
        display: !!props.title,
        text: props.title,
        color: textColor,
        font: { size: 16, weight: 'bold' },
      },
      tooltip: {
        backgroundColor: isDark.value ? '#334155' : '#ffffff',
        titleColor: isDark.value ? '#ffffff' : '#000000',
        bodyColor: isDark.value ? '#cbd5e1' : '#475569',
        borderColor: isDark.value ? '#475569' : '#e2e8f0',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 8,
      },
    },
    cutout: '70%', // Makes it look more modern/sleek
  };
});
</script>

<template>
  <div class="mx-auto h-[350px] w-full max-w-md">
    <Doughnut :data="chartData" :options="options" />
  </div>
</template>
