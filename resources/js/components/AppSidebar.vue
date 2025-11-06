<script setup lang="ts">
import NavUser from '@/components/NavUser.vue';

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import finance from '@/routes/finance';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
  BarChart3,
  Bell,
  DollarSign,
  FileSpreadsheet,
  FileText,
  HelpCircle,
  LayoutGrid,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import NavMain from './NavMain.vue';

// const page = usePage();
// const user = page.props.auth.user;

const allNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: finance.dashboard(),
    icon: LayoutGrid,
    group: 'Overview',
  },
  {
    title: 'Notifications',
    href: finance.notifications(),
    icon: Bell,
    group: 'Overview',
  },

  {
    title: 'Boundary Contracts',
    href: finance.boundaryContracts(),
    icon: FileText,
    group: 'Operations',
  },
  {
    title: 'Reports & Analytics',
    href: finance.reportsAndAnalytics(),
    icon: BarChart3,
    group: 'Operations',
  },

  {
    title: 'Revenue Management',
    href: finance.revenueManagement(),
    icon: DollarSign,
    group: 'Finance',
  },
  {
    title: 'Expense Management',
    href: finance.expenseManagement(),
    icon: FileSpreadsheet,
    group: 'Finance',
  },

  {
    title: 'Support Center',
    href: finance.supportCenter(),
    icon: HelpCircle,
    group: 'Support',
  },

  // This is still in progress
  //   ...(user.role === 'admin'
  //   ? [
  //       {
  //         title: 'Admin Panel',
  //         href: '/admin',
  //         icon: Shield,
  //       },
  //     ]
  //   : []),
  // ...(user.role === 'user'
  //   ? [
  //       {
  //         title: 'Profile',
  //         href: '/profile',
  //         icon: User,
  //       },
  //     ]
  //   : []),
];
</script>

<template>
  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="dashboard()">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain :items="allNavItems" />
    </SidebarContent>

    <SidebarFooter>
      <!-- <NavFooter :items="footerNavItems" /> -->
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
