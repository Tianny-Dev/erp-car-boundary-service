<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import owner from '@/routes/owner';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Maintenance Requests',
    href: owner.maintenanceRequests.index().url,
  },
];

const props = defineProps<{
  requests: Array<any>;
}>();
</script>

<template>
  <Head title="Maintenance Requests" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
      <div
        class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 p-6 md:min-h-min dark:border-sidebar-border"
      >
        <h2 class="mb-6 text-xl font-semibold">Maintenance Requests</h2>

        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Vehicle</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Description</TableHead>
              <TableHead>Date</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="item in props.requests" :key="item.id">
              <TableCell>{{ item.vehicle?.plate_number }}</TableCell>
              <TableCell>{{ item.maintenance_type }}</TableCell>
              <TableCell>{{ item.description }}</TableCell>
              <TableCell>{{ item.maintenance_date }}</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>
  </AppLayout>
</template>
