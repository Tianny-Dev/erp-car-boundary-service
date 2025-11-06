<script setup lang="ts">
import CustomChartTooltip from '@/components/finance/CustomChartTooltip.vue';
import { LineChart } from '@/components/ui/chart-line';

const data = [
  {
    year: 1970,
    'Growth Rate': 1.53,
  },
  {
    year: 1971,
    'Growth Rate': 1.58,
  },
  {
    year: 1972,
    'Growth Rate': 1.61,
  },
  {
    year: 1973,
    'Growth Rate': 1.61,
  },
  {
    year: 1974,
    'Growth Rate': 1.67,
  },
  {
    year: 1975,
    'Growth Rate': 1.64,
  },
  {
    year: 1976,
    'Growth Rate': 1.62,
  },
  {
    year: 1977,
    'Growth Rate': 1.69,
  },
  {
    year: 1978,
    'Growth Rate': 1.7,
  },
  {
    year: 1979,
    'Growth Rate': 1.67,
  },
  {
    year: 1980,
    'Growth Rate': 1.7,
  },
  {
    year: 1981,
    'Growth Rate': 1.72,
  },
  {
    year: 1982,
    'Growth Rate': 1.73,
  },
  {
    year: 1983,
    'Growth Rate': 1.73,
  },
  {
    year: 1984,
    'Growth Rate': 1.78,
  },
  {
    year: 1985,
    'Growth Rate': 1.81,
  },
  {
    year: 1986,
    'Growth Rate': 1.89,
  },
  {
    year: 1987,
    'Growth Rate': 1.91,
  },
  {
    year: 1988,
    'Growth Rate': 1.94,
  },
  {
    year: 1989,
    'Growth Rate': 1.94,
  },
  {
    year: 1990,
    'Growth Rate': 1.97,
  },
  {
    year: 1991,
    'Growth Rate': 1.99,
  },
  {
    year: 1992,
    'Growth Rate': 2.12,
  },
  {
    year: 1993,
    'Growth Rate': 2.13,
  },
  {
    year: 1994,
    'Growth Rate': 2.15,
  },
  {
    year: 1995,
    'Growth Rate': 2.17,
  },
  {
    year: 1996,
    'Growth Rate': 2.2,
  },
  {
    year: 1997,
    'Growth Rate': 2.15,
  },
  {
    year: 1998,
    'Growth Rate': 2.07,
  },
  {
    year: 1999,
    'Growth Rate': 2.05,
  },
  {
    year: 2000,
    'Growth Rate': 2.07,
  },
  {
    year: 2001,
    'Growth Rate': 2.08,
  },
  {
    year: 2002,
    'Growth Rate': 2.1,
  },
  {
    year: 2003,
    'Growth Rate': 2.15,
  },
  {
    year: 2004,
    'Growth Rate': 2.21,
  },
  {
    year: 2005,
    'Growth Rate': 2.23,
  },
  {
    year: 2006,
    'Growth Rate': 2.29,
  },
  {
    year: 2007,
    'Growth Rate': 2.34,
  },
  {
    year: 2008,
    'Growth Rate': 2.36,
  },
  {
    year: 2009,
    'Growth Rate': 2.36,
  },
  {
    year: 2010,
    'Growth Rate': 2.35,
  },
  {
    year: 2011,
    'Growth Rate': 2.34,
  },
  {
    year: 2012,
    'Growth Rate': 2.39,
  },
  {
    year: 2013,
    'Growth Rate': 2.3,
  },
  {
    year: 2014,
    'Growth Rate': 2.35,
  },
  {
    year: 2015,
    'Growth Rate': 2.39,
  },
  {
    year: 2016,
    'Growth Rate': 2.41,
  },
  {
    year: 2017,
    'Growth Rate': 2.44,
  },
  {
    year: 2018,
    'Growth Rate': 2.45,
  },
  {
    year: 2019,
    'Growth Rate': 2.47,
  },
  {
    year: 2020,
    'Growth Rate': 2.48,
  },
  {
    year: 2021,
    'Growth Rate': 2.51,
  },
];
</script>

<template>
  <LineChart
    index="year"
    :colors="['blue']"
    :data="data"
    :categories="['Growth Rate']"
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

interface NetProfitData { type: string; profit: number; }
