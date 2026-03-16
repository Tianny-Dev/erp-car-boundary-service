<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { AppPageProps } from '@/types';

const page = usePage<AppPageProps>();

const userRoleDisplay = computed(() => {
  const role = page.props.auth.user?.role_name;

  // Condition logic
  if (role === 'super_admin') return 'Super Admin';
  if (role === 'owner') return 'Franchise Owner';
  if (role === 'franchise') return 'Franchise';
  return role || 'User';
});
</script>

<template>
  <div class="grid flex-1 text-left text-sm">
    <span class="mb-0.5 truncate leading-tight font-semibold">
      {{ userRoleDisplay }}
    </span>
    <span class="truncate text-xs text-muted-foreground">
      DDGNS Car Boundary Service
    </span>
  </div>
</template>
