<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';

interface Franchise {
  id: number;
  name: string;
  email: string;
  phone: string;
  status: {
    name: string;
  } | null;
  owner: {
    user: {
      name: string;
    } | null;
  } | null;
}

defineProps<{
  franchises: Franchise[];
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
        class="relative rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
      >
        <h2 class="mb-4 font-mono text-xl font-semibold">
          Franchise Management
        </h2>

        <Table class="w-full table-fixed">
          <TableHeader>
            <TableRow>
              <TableHead class="w-1/5 truncate text-center font-semibold"
                >Franchise</TableHead
              >
              <TableHead class="w-1/5 truncate text-center font-semibold"
                >Owner</TableHead
              >
              <TableHead class="w-1/5 truncate text-center font-semibold"
                >Email</TableHead
              >
              <TableHead class="w-1/5 truncate text-center font-semibold"
                >Phone</TableHead
              >
              <TableHead class="w-1/10 truncate text-center font-semibold"
                >Status</TableHead
              >
              <TableHead class="w-1/10 truncate text-center font-semibold"
                >Actions</TableHead
              >
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="franchises.length > 0">
              <TableRow v-for="franchise in franchises" :key="franchise.id">
                <TableCell class="w-1/5 truncate text-center">{{
                  franchise.name
                }}</TableCell>

                <TableCell class="w-1/5 truncate text-center">{{
                  franchise.owner?.user?.name ?? 'N/A'
                }}</TableCell>
                <TableCell class="w-1/5 truncate text-center">{{
                  franchise.email
                }}</TableCell>
                <TableCell class="w-1/5 truncate text-center">{{
                  franchise.phone
                }}</TableCell>
                <TableCell class="truncates w-1/10 text-center">{{
                  franchise.status?.name ?? 'N/A'
                }}</TableCell>

                <TableCell class="w-1/10 text-center">
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child class="cursor-pointer">
                      <Button variant="ghost" class="h-8 w-8 p-0">
                        <span class="sr-only">Open menu</span>
                        <MoreHorizontal class="h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="border-2">
                      <DropdownMenuLabel>Actions</DropdownMenuLabel>
                      <DropdownMenuItem class="cursor-pointer">
                        View Franchise Details
                      </DropdownMenuItem>
                      <DropdownMenuItem class="cursor-pointer">
                        View Owner Details
                      </DropdownMenuItem>
                      <div v-if="franchise.status?.name === 'active'">
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                          class="cursor-pointer text-blue-500 focus:text-blue-600"
                        >
                          Accept Franchise
                        </DropdownMenuItem>
                      </div>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </TableCell>
              </TableRow>
            </template>

            <TableRow v-else>
              <TableCell
                colspan="6"
                class="py-4 text-center text-sm text-muted-foreground"
              >
                No franchises found.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>
  </AppLayout>
</template>
