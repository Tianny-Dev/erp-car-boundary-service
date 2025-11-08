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
import superAdmin from '@/routes/super-admin';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
  BarChart3,
  Bell,
  Clock,
  DollarSign,
  FileSpreadsheet,
  FileText,
  HelpCircle,
  LayoutGrid,
  Settings,
  Wrench,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import NavMain from './NavMain.vue';

// 🧠 1. Get the logged-in user
const page = usePage();
const user = page.props.auth.user;

// 🧭 2. Map user_type_id to role name
const typeMap: Record<number, string> = {
  1: 'super_admin',
  2: 'owner',
  3: 'manager',
  4: 'driver',
  5: 'technician',
  6: 'passenger',
};

const userType = typeMap[user?.user_type_id] || 'guest';

// 🧱 3. Define navigation sets per user type
const navConfig: Record<string, NavItem[]> = {
  super_admin: [
    {
      title: 'Dashboard',
      href: superAdmin.dashboard(),
      icon: LayoutGrid,
      group: 'Overview',
    },
    {
      title: 'Settings',
      href: finance.dashboard?.() ?? '/settings',
      icon: Settings,
      group: 'Admin',
    },
  ],

  manager: [
    {
      title: 'Dashboard',
      href: finance.dashboard(),
      icon: LayoutGrid,
      group: 'Overview',
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
  ],

  owner: [
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
  ],

  driver: [
    {
      title: 'Boundary Contracts',
      href: finance.boundaryContracts(),
      icon: FileText,
      group: 'Operations',
    },
    {
      title: 'Support Center',
      href: finance.supportCenter(),
      icon: HelpCircle,
      group: 'Support',
    },
  ],

  technician: [
    {
      title: 'Maintenance Logs',
      href: '/maintenance/logs',
      icon: Wrench,
      group: 'Operations',
    },
  ],

  passenger: [
    {
      title: 'Trip History',
      href: '/trips/history',
      icon: Clock,
      group: 'Overview',
    },
  ],

  guest: [], // fallback
};

// 🧩 4. Load the correct nav items for the user
const allNavItems = navConfig[userType] || [];
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
      <NavUser />
    </SidebarFooter>
  </Sidebar>

  <slot />
</template>
