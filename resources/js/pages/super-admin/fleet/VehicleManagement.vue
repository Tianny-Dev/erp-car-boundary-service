<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Vehicle Management',
    href: superAdmin.vehicle.index().url,
  },
];
</script>

<template>
  <Head title="Vehicle Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    ></div>
  </AppLayout>
</template>
