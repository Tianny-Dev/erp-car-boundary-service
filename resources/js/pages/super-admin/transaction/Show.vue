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
import { computed } from 'vue';

type Franchise = {
  id: number;
  name: string;
};

type Revenue = {
  id: number;
  invoice_no: string;
  amount: number;
  currency: string;
  service_type: string;
  payment_date: string;
};

const props = defineProps<{
  franchise: Franchise;
  revenues: Revenue[];
}>();

// Filter only "Trips" revenues
const tripsRevenues = computed(() =>
  props.revenues.filter((rev) => rev.service_type === 'Trips'),
);

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Franchise Transactions',
    href: superAdmin.transaction().url,
  },
  {
    title: props.franchise.name,
    href: '',
  },
];
</script>

<template>
  <Head :title="`Transactions - ${franchise.name}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="m-5 rounded-2xl bg-white p-6 shadow-md">
      <!-- Header -->
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">
          {{ franchise.name }} – Trips Transactions
        </h2>

        <a :href="superAdmin.transaction().url">
          <Button>Back</Button>
        </a>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow>
              <TableCell>ID</TableCell>
              <TableCell>Invoice No.</TableCell>
              <TableCell>Amount</TableCell>
              <TableCell>Service Type</TableCell>
              <TableCell>Payment Date</TableCell>
            </TableRow>
          </TableHeader>

          <TableBody>
            <!-- If there are Trips revenues -->
            <template v-if="tripsRevenues.length > 0">
              <TableRow
                v-for="rev in tripsRevenues"
                :key="rev.id"
                class="transition hover:bg-gray-50"
              >
                <TableCell>{{ rev.id }}</TableCell>
                <TableCell>{{ rev.invoice_no }}</TableCell>
                <TableCell>{{ rev.amount }} {{ rev.currency }}</TableCell>
                <TableCell>{{ rev.service_type }}</TableCell>
                <TableCell>{{ rev.payment_date }}</TableCell>
              </TableRow>
            </template>

            <!-- If no Trips revenues -->
            <TableRow v-else>
              <TableCell colspan="5" class="py-4 text-center text-gray-500">
                No Trips transactions found for this franchise.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>
  </AppLayout>
</template>
