<script setup lang="ts">
import CustomChartTooltip from '@/components/finance/CustomChartTooltip.vue';
import { BarChart } from '@/components/ui/chart-bar';

const data = [
  {
    name: 'Jan',
    expenses: Math.floor(Math.random() * 2000) + 500,
    revenue: Math.floor(Math.random() * 2000) + 500,
  },
  {
    name: 'Feb',
    expenses: Math.floor(Math.random() * 2000) + 500,
    revenue: Math.floor(Math.random() * 2000) + 500,
  },
  {
    name: 'Mar',
    expenses: Math.floor(Math.random() * 2000) + 500,
    revenue: Math.floor(Math.random() * 2000) + 500,
  },
  {
    name: 'Apr',
    expenses: Math.floor(Math.random() * 2000) + 500,
    revenue: Math.floor(Math.random() * 2000) + 500,
  },
  {
    name: 'May',
    expenses: Math.floor(Math.random() * 2000) + 500,
    revenue: Math.floor(Math.random() * 2000) + 500,
  },
  {
    name: 'Jun',
    expenses: Math.floor(Math.random() * 2000) + 500,
    revenue: Math.floor(Math.random() * 2000) + 500,
  },
  {
    name: 'Jul',
    expenses: Math.floor(Math.random() * 2000) + 500,
    revenue: Math.floor(Math.random() * 2000) + 500,
  },
];
</script>

<template>
  <BarChart
    :data="data"
    index="name"
    :colors="['red', 'green']"
    :categories="['expenses', 'revenue']"
    :y-formatter="
      (tick, i) => {
        return typeof tick === 'number'
          ? `$ ${new Intl.NumberFormat('us').format(tick).toString()}`
          : '';
      }
    "
    :custom-tooltip="CustomChartTooltip"
  />
</template>
