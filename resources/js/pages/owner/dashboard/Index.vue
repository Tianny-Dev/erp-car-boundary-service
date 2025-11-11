<script setup lang="ts">
import RevenueVsExpensesAreaChart from '@/components/finance/charts/dashboard/RevenueVsExpensesAreaChart.vue';
import NetProfitTrendSparkLine from '@/components/finance/charts/reports-and-analytics/NetProfitTrendSparkLine.vue';
import RevenueVsExpensesBarChart from '@/components/finance/charts/reports-and-analytics/RevenueVsExpensesBarChart.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import finance from '@/routes/finance';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
  CarTaxiFront,
  CreditCard,
  DollarSign,
  Settings,
  User,
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: finance.dashboard().url,
  },
];

const data = [
  { name: 'Jan', Revenue: 1300, Expenses: 700 },
  { name: 'Feb', Revenue: 1100, Expenses: 800 },
  { name: 'Mar', Revenue: 1500, Expenses: 900 },
  { name: 'Apr', Revenue: 1800, Expenses: 1000 },
  { name: 'May', Revenue: 1700, Expenses: 1200 },
  { name: 'Jun', Revenue: 1900, Expenses: 1300 },
  { name: 'Jul', Revenue: 2100, Expenses: 1500 },
];

const revenueExpensesData = [
  { name: 'Jan', expenses: 1200, revenue: 1800 },
  { name: 'Feb', expenses: 1500, revenue: 1900 },
  { name: 'Mar', expenses: 1700, revenue: 2000 },
  { name: 'Apr', expenses: 1300, revenue: 1600 },
  { name: 'May', expenses: 1400, revenue: 1700 },
  { name: 'Jun', expenses: 1800, revenue: 2100 },
  { name: 'Jul', expenses: 1600, revenue: 2200 },
];

const netProfitData = [
  { year: 2018, 'Growth Rate': 2.45 },
  { year: 2019, 'Growth Rate': 2.47 },
  { year: 2020, 'Growth Rate': 2.48 },
  { year: 2021, 'Growth Rate': 2.51 },
  { year: 2022, 'Growth Rate': 2.55 },
  { year: 2023, 'Growth Rate': 2.58 },
  { year: 2024, 'Growth Rate': 2.6 },
  { year: 2025, 'Growth Rate': 2.63 },
];
</script>

<template>
  <Head title="Finance Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
      <!-- Header -->
      <div class="mb-2">
        <h1 class="mb-2 text-3xl font-bold">Dashboard</h1>
        <p class="text-gray-600">Central Hub for Tracking and Management</p>
      </div>
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- My Active Vehicles -->
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium"
              >My Active Vehicles</CardTitle
            >
            <CarTaxiFront class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">12</div>
            <p class="text-xs text-muted-foreground">+3% from yesterday</p>
          </CardContent>
        </Card>

        <!-- My Active Drivers -->
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium">My Active Drivers</CardTitle>
            <User class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">25</div>
            <p class="text-xs text-muted-foreground">+5% from yesterday</p>
          </CardContent>
        </Card>

        <!-- Daily Earnings -->
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium">Daily Earnings</CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">₱80,000</div>
            <p class="text-xs text-muted-foreground">+20% from yesterday</p>
          </CardContent>
        </Card>

        <!-- Pending Boundary Due -->
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium"
              >Pending Boundary Due</CardTitle
            >
            <CreditCard class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">₱15,000</div>
            <p class="text-xs text-muted-foreground">+10% from yesterday</p>
          </CardContent>
        </Card>

        <!-- Vehicles Under Maintenance -->
        <Card>
          <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
          >
            <CardTitle class="text-sm font-medium"
              >Vehicles Under Maintenance</CardTitle
            >
            <Settings class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">3</div>
            <p class="text-xs text-muted-foreground">-1 from yesterday</p>
          </CardContent>
        </Card>
      </div>

      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
        <Card class="col-span-7">
          <CardHeader>
            <CardTitle>Overview</CardTitle>
          </CardHeader>
          <CardContent class="pl-2">
            <RevenueVsExpensesAreaChart
              :data="data"
              :categories="['Expenses', 'Revenue']"
              :colors="['#005dcf', '#33cc66']"
            />
          </CardContent>
        </Card>
      </div>

      <!-- Charts Section -->
      <div class="grid gap-6 md:grid-cols-2">
        <!-- Revenue vs Expenses Chart -->
        <Card>
          <CardHeader>
            <CardTitle>Revenue vs Expenses</CardTitle>
          </CardHeader>
          <CardContent>
            <RevenueVsExpensesBarChart
              :data="revenueExpensesData"
              :colors="['#ef4444', '#22c55e']"
              :categories="['expenses', 'revenue']"
              :y-formatter="(val) => `$ ${val.toLocaleString()}`"
            />
          </CardContent>
        </Card>

        <!-- Net Profit Trend Chart -->
        <Card>
          <CardHeader>
            <CardTitle>Net Profit Trend</CardTitle>
          </CardHeader>
          <CardContent>
            <NetProfitTrendSparkLine
              :data="netProfitData"
              :colors="['#3b82f6']"
              :y-formatter="(val) => `$ ${val.toFixed(2)}`"
            />
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
