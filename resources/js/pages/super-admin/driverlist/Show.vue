<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
  drivers: any[];
  franchiseId: number;
}>();

// Breadcrumbs (same format as your transaction page)
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Driver History',
    href: superAdmin.driverlist().url,
  },
  {
    title: `Franchise #${props.franchiseId}`,
    href: '',
  },
];
</script>

<template>
  <Head :title="`Drivers – Franchise #${franchiseId}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="m-5 rounded-2xl bg-white p-6 shadow-md">
      <!-- Header -->
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">
          Drivers Assigned to Franchise #{{ franchiseId }}
        </h2>

        <a :href="superAdmin.driverlist().url">
          <Button>Back</Button>
        </a>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow>
              <TableCell>ID</TableCell>
              <TableCell>Name</TableCell>
              <TableCell>Email</TableCell>
              <TableCell>Phone</TableCell>
              <TableCell>Address</TableCell>
              <TableCell class="text-center">Action</TableCell>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow
              v-for="driver in drivers"
              :key="driver.id"
              class="transition hover:bg-gray-50"
            >
              <TableCell>{{ driver.id }}</TableCell>
              <TableCell>{{ driver.user?.name }}</TableCell>
              <TableCell>{{ driver.user?.email }}</TableCell>
              <TableCell>{{ driver.user?.phone }}</TableCell>
              <TableCell class="w-[500px]">
                {{ driver.user?.address }}
              </TableCell>
              <TableCell class="text-center">
                <div class="flex justify-center gap-3">
                  <a
                    :href="`/super-admin/driverlist/driver=${driver.id}?period=monthly`"
                  >
                    <Button> Monthly </Button>
                  </a>
                  <a
                    :href="`/super-admin/driverlist/driver=${driver.id}?period=weekly`"
                  >
                    <Button> Weekly </Button>
                  </a>
                  <a
                    :href="`/super-admin/driverlist/driver=${driver.id}?period=daily`"
                  >
                    <Button> Daily </Button>
                  </a>
                </div>
              </TableCell>
            </TableRow>

            <!-- If no drivers -->
            <TableRow v-if="drivers.length === 0">
              <TableCell colspan="5" class="py-4 text-center text-gray-500">
                No drivers assigned to this franchise.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>
  </AppLayout>
</template>
