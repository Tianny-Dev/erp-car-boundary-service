<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
// Import shadcn-vue table components
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
// Import Button for styling
import PendingDriverController from '@/actions/App/Http/Controllers/SuperAdmin/PendingDriverController';
import { Button } from '@/components/ui/button';

// 1. Define the prop passed from the controller
defineProps<{
  pendingDrivers: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: superAdmin.dashboard().url,
  },
];
</script>

<template>
  <Head title="Super Admin Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
      <div
        class="relative flex-1 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
      >
        <h2 class="mb-4 text-xl font-semibold">Pending Driver Applications</h2>

        <Table>
          <TableCaption v-if="pendingDrivers.length === 0">
            No pending applications found.
          </TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead>Name</TableHead>
              <TableHead>Email</TableHead>
              <TableHead>License Number</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="driver in pendingDrivers" :key="driver.id">
              <TableCell class="font-medium">
                {{ driver.user.name }}
              </TableCell>
              <TableCell>{{ driver.user.email }}</TableCell>
              <TableCell>{{ driver.license_number }}</TableCell>
              <TableCell class="flex justify-end gap-2">
                <Link
                  :href="PendingDriverController.accept(driver.id)"
                  method="post"
                  as="button"
                  preserve-scroll
                >
                  <Button size="sm" variant="outline"> Accept </Button>
                </Link>

                <Link
                  :href="PendingDriverController.deny(driver.id)"
                  method="post"
                  as="button"
                  preserve-scroll
                >
                  <Button size="sm" variant="destructive"> Deny </Button>
                </Link>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>
  </AppLayout>
</template>
