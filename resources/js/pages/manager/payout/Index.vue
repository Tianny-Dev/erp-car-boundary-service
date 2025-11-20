<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { payout } from '@/routes/owner';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Props {
  dailyEarnings: number;
}

const { dailyEarnings } = defineProps<Props>();

// Static data (mock)
const dailyPayout = {
  date: '2025-11-14',
  cutoffTime: '12:00 AM',
  totalGrossFare: 18250,
  totalNetPayout: 16425,
  totalTrips: 136,

  computedDistribution: {
    driverAmount: 9855,
    ownerAmount: 4106.25,
    platformAmount: 1642.5,
    maintenanceAmount: 821.25,
  },
};

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Payout',
    href: payout().url,
  },
];
</script>

<template>
  <Head title="Payout" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
      <!-- Header -->
      <div class="mb-2">
        <h1 class="mb-2 text-3xl font-bold">Daily Payout Distribution</h1>
        <p class="text-gray-600">
          Automated earnings breakdown for bank disbursement
        </p>
      </div>

      <!-- Top Statistics -->
      <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <!-- Total Gross Fare -->
        <div class="rounded-xl border p-4 dark:border-sidebar-border">
          <p class="text-sm text-gray-500">Total Gross Fare</p>
          <h2 class="mt-2 text-3xl font-bold">
            ₱{{ dailyEarnings.toLocaleString() }}
          </h2>
          <p class="mt-1 text-xs text-gray-400">
            Cutoff: {{ dailyPayout.date }} • {{ dailyPayout.cutoffTime }}
          </p>
        </div>

        <!-- Driver Share -->
        <div class="rounded-xl border p-4 dark:border-sidebar-border">
          <p class="text-sm text-gray-500">Driver Share</p>
          <h2 class="mt-2 text-3xl font-bold">
            ₱{{
              dailyPayout.computedDistribution.driverAmount.toLocaleString()
            }}
          </h2>
          <p class="mt-1 text-xs text-gray-400">60% of total net earnings</p>
        </div>

        <!-- Owner Share -->
        <div class="rounded-xl border p-4 dark:border-sidebar-border">
          <p class="text-sm text-gray-500">Franchise Owner Share</p>
          <h2 class="mt-2 text-3xl font-bold">
            ₱{{ dailyPayout.computedDistribution.ownerAmount.toLocaleString() }}
          </h2>
          <p class="mt-1 text-xs text-gray-400">25% of total net earnings</p>
        </div>
      </div>

      <!-- Distribution breakdown panel -->
      <div
        class="relative flex-1 rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
      >
        <h2 class="mb-4 text-xl font-semibold">Distribution Breakdown</h2>

        <div class="space-y-4">
          <!-- Driver -->
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium">Driver Share</p>
              <p class="text-sm text-gray-500">60% of total net earnings</p>
            </div>
            <p class="font-semibold">
              ₱{{
                dailyPayout.computedDistribution.driverAmount.toLocaleString()
              }}
            </p>
          </div>

          <!-- Owner -->
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium">Franchise Owner Share</p>
              <p class="text-sm text-gray-500">25% of total net earnings</p>
            </div>
            <p class="font-semibold">
              ₱{{
                dailyPayout.computedDistribution.ownerAmount.toLocaleString()
              }}
            </p>
          </div>

          <!-- Platform -->
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium">Platform Fee</p>
              <p class="text-sm text-gray-500">10% platform charge</p>
            </div>
            <p class="font-semibold">
              ₱{{
                dailyPayout.computedDistribution.platformAmount.toLocaleString()
              }}
            </p>
          </div>

          <!-- Maintenance -->
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium">Maintenance & Ops</p>
              <p class="text-sm text-gray-500">5% service allocation</p>
            </div>
            <p class="font-semibold">
              ₱{{
                dailyPayout.computedDistribution.maintenanceAmount.toLocaleString()
              }}
            </p>
          </div>
        </div>

        <hr class="my-6" />

        <div class="flex items-center justify-between">
          <p class="text-lg font-semibold">Total Net Distribution</p>
          <p class="text-lg font-bold">
            ₱{{ dailyPayout.totalNetPayout.toLocaleString() }}
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
