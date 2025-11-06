<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import finance from '@/routes/finance';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Notifications',
    href: finance.notifications().url,
  },
];

interface Notification {
  id: number;
  title: string;
  description: string;
  type: 'alert' | 'reminder';
  date: string;
}

const notifications = ref<Notification[]>([
  {
    id: 1,
    title: 'Unpaid boundary alert',
    description:
      'Franchise boundary exceeded for Region A. Please review outstanding payment.',
    type: 'alert',
    date: '2025-11-06 10:30 AM',
  },
  {
    id: 2,
    title: 'Expense approval reminder',
    description:
      'There are 3 pending expense approvals waiting for your review.',
    type: 'reminder',
    date: '2025-11-06 09:10 AM',
  },
  {
    id: 3,
    title: 'New franchise request',
    description:
      'A new franchise applicant in Quezon City submitted requirements.',
    type: 'reminder',
    date: '2025-11-05 05:22 PM',
  },
  {
    id: 4,
    title: 'Inventory threshold alert',
    description:
      'Stock on Franchise #214 is below critical level. Restock needed.',
    type: 'alert',
    date: '2025-11-05 02:45 PM',
  },
  {
    id: 5,
    title: 'Profile update requested',
    description:
      'Owner of Franchise #102 requested changes to company profile.',
    type: 'reminder',
    date: '2025-11-04 11:30 AM',
  },
  {
    id: 6,
    title: 'System maintenance upcoming',
    description: 'Scheduled maintenance on Nov 10, 11:00 PM — Expect downtime.',
    type: 'reminder',
    date: '2025-11-04 09:00 AM',
  },
  {
    id: 7,
    title: 'Unpaid invoice warning',
    description: 'Invoice INV-88932 is overdue for more than 30 days.',
    type: 'alert',
    date: '2025-11-03 01:18 PM',
  },
  {
    id: 8,
    title: 'Financial report ready',
    description: 'October sales analytics are now available for download.',
    type: 'reminder',
    date: '2025-11-03 10:52 AM',
  },
  {
    id: 9,
    title: 'New expense request',
    description: 'A ₱12,800 expense request from Franchise #88 needs approval.',
    type: 'reminder',
    date: '2025-11-02 02:20 PM',
  },
  {
    id: 10,
    title: 'Security alert',
    description:
      'Multiple failed login attempts detected for user admin@corp.ph',
    type: 'alert',
    date: '2025-11-02 08:44 AM',
  },
  {
    id: 11,
    title: 'Expired business permit alert',
    description:
      'Permit for Franchise #309 expired last week. Renewal required.',
    type: 'alert',
    date: '2025-11-01 03:13 PM',
  },
  {
    id: 12,
    title: 'New message received',
    description: 'Support team replied to ticket #7711 — open to view details.',
    type: 'reminder',
    date: '2025-10-31 06:10 PM',
  },
]);

function dismissNotification(id: number) {
  notifications.value = notifications.value.filter((n) => n.id !== id);
}
</script>

<template>
  <Head title="Notifications" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
      <Card v-if="notifications.length" class="p-0">
        <CardContent class="divide-y p-0">
          <div
            v-for="notif in notifications"
            :key="notif.id"
            class="flex cursor-pointer items-start justify-between p-4 transition hover:opacity-80"
            :class="{
              'bg-red-50 dark:bg-red-950/30': notif.type === 'alert',
              'bg-blue-50 dark:bg-blue-950/30': notif.type === 'reminder',
            }"
          >
            <div>
              <h2
                class="text-sm font-medium"
                :class="
                  notif.type === 'alert'
                    ? 'text-red-700 dark:text-red-400'
                    : 'text-blue-700 dark:text-blue-400'
                "
              >
                {{ notif.title }}
              </h2>
              <p class="text-xs text-muted-foreground">
                {{ notif.description }}
              </p>
              <p class="mt-1 text-[10px] text-muted-foreground">
                {{ notif.date }}
              </p>
            </div>

            <div class="flex gap-2">
              <Button size="sm" variant="outline">View</Button>
              <Button
                size="sm"
                variant="ghost"
                @click="dismissNotification(notif.id)"
                >Dismiss</Button
              >
            </div>
          </div>
        </CardContent>
      </Card>

      <div v-else class="py-10 text-center text-sm text-muted-foreground">
        🎉 No new notifications
      </div>
    </div>
  </AppLayout>
</template>
