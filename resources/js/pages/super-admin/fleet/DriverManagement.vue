<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Driver Management',
    href: superAdmin.driver.index().url,
  },
];
</script>

<template>
  <Head title="Super Admin Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    ></div>
  </AppLayout>
</template>
