<script setup lang="ts">
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ items: NavItem[] }>();

const page = usePage();

const groupedItems = computed(() => {
  const groups: Record<string, NavItem[]> = {};
  for (const item of props.items) {
    const group = item.group || 'Other';
    if (!groups[group]) groups[group] = [];
    groups[group].push(item);
  }
  return groups;
});
</script>

<template>
  <div>
    <template v-for="(groupItems, groupName) in groupedItems" :key="groupName">
      <SidebarGroup class="overflow-visible px-2 py-0">
        <SidebarGroupLabel v-if="groupName" class="pointer-events-none">
          {{ groupName }}
        </SidebarGroupLabel>

        <SidebarMenu>
          <SidebarMenuItem
            v-for="(item, index) in groupItems"
            :key="item.title + index"
          >
            <SidebarMenuButton
              as-child
              :is-active="urlIsActive(item.href, page.url)"
              :tooltip="item.title"
            >
              <Link :href="item.href" class="flex w-full items-center gap-2">
                <component :is="item.icon" class="size-4 shrink-0" />
                <span>{{ item.title }}</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>
    </template>
  </div>
</template>
