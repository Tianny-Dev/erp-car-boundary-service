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

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Revenues',
    href: superAdmin.dashboard().url,
  },
];

type Franchise = {
  id: number;
  name: string;
  email: string;
  phone: string;
  address: string;
  barangay: string;
  city: string;
  province: string;
};

defineProps<{
  franchises: Franchise[];
}>();
</script>

<template>
  <Head title="Super Admin Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="m-5 rounded-2xl bg-white p-6 shadow-md">
      <!-- header Start -->
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Franchises List</h2>
        <div class="flex items-center justify-center gap-3">
          <p class="text-lg font-bold">All Franchises:</p>
          <a :href="`/super-admin/revenues/all?period=monthly`">
            <Button class="cursor-pointer"> Monthly </Button>
          </a>
          <a :href="`/super-admin/revenues/all?period=weekly`">
            <Button class="cursor-pointer"> Weekly </Button>
          </a>
          <a :href="`/super-admin/revenues/all?period=daily`">
            <Button class="cursor-pointer"> Daily </Button>
          </a>
        </div>
      </div>
      <!-- header End -->

      <!-- Table Start -->
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
              v-for="franchise in franchises"
              :key="franchise.id"
              class="transition hover:bg-gray-50"
            >
              <TableCell>{{ franchise.id }}</TableCell>
              <TableCell>{{ franchise.name }}</TableCell>
              <TableCell>{{ franchise.email }}</TableCell>
              <TableCell>{{ franchise.phone }}</TableCell>
              <TableCell class="w-[500px]">
                {{ franchise.address }}, {{ franchise.barangay }},
                {{ franchise.city }}, {{ franchise.province }}
              </TableCell>
              <TableCell class="text-center">
                <div class="flex justify-center gap-3">
                  <a
                    :href="`/super-admin/revenues/${franchise.id}?period=monthly`"
                  >
                    <Button> Monthly </Button>
                  </a>
                  <a
                    :href="`/super-admin/revenues/${franchise.id}?period=weekly`"
                  >
                    <Button> Weekly </Button>
                  </a>
                  <a
                    :href="`/super-admin/revenues/${franchise.id}?period=daily`"
                  >
                    <Button> Daily </Button>
                  </a>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <!-- Table End -->
    </div>
  </AppLayout>
</template>
