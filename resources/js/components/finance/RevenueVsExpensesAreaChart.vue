<script setup lang="ts">
import CustomChartTooltip from '@/components/finance/CustomChartTooltip.vue';
import { AreaChart } from '@/components/ui/chart-area';

const data = [
  {
    name: 'Jan',
    Revenue: Math.floor(Math.random() * 2000) + 500,
    Expenses: Math.floor(Math.random() * 2000) + 500,
  },
  {
    name: 'Feb',
    Revenue: Math.floor(Math.random() * 2000) + 500,
    Expenses: Math.floor(Math.random() * 2000) + 500,
  },
  {
    name: 'Mar',
    Revenue: Math.floor(Math.random() * 2000) + 500,
    Expenses: Math.floor(Math.random() * 2000) + 500,
  },
  {
    name: 'Apr',
    Revenue: Math.floor(Math.random() * 2000) + 500,
    Expenses: Math.floor(Math.random() * 2000) + 500,
  },
  {
    name: 'May',
    Revenue: Math.floor(Math.random() * 2000) + 500,
    Expenses: Math.floor(Math.random() * 2000) + 500,
  },
  {
    name: 'Jun',
    Revenue: Math.floor(Math.random() * 2000) + 500,
    Expenses: Math.floor(Math.random() * 2000) + 500,
  },
  {
    name: 'Jul',
    Revenue: Math.floor(Math.random() * 2000) + 500,
    Expenses: Math.floor(Math.random() * 2000) + 500,
  },
];
</script>

<template>
  <AreaChart
    index="name"
    :data="data"
    :colors="['blue', 'green']"
    :categories="['Expenses', 'Revenue']"
    :custom-tooltip="CustomChartTooltip"
  />
</template>
