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
import owner from '@/routes/owner';
import superAdmin from '@/routes/super-admin';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
  BarChart3,
  Bell,
  CarTaxiFront,
  Clock,
  DollarSign,
  FileSpreadsheet,
  FileText,
  HelpCircle,
  History,
  LayoutGrid,
  Octagon,
  Users,
  Wrench,
  XSquare,
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
      title: 'Driver Management',
      href: superAdmin.driver.index(),
      icon: Users,
      group: 'Fleet',
    },
    {
      title: 'Revenues History',
      href: superAdmin.revenues(),
      icon: History,
      group: 'History',
    },
    {
      title: 'Vehicle Management',
      href: superAdmin.vehicle.index(),
      icon: CarTaxiFront,
      group: 'Fleet',
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
      href: owner.dashboard(),
      icon: LayoutGrid,
      group: 'Overview',
    },
    {
      title: 'Notifications',
      href: owner.notifications(),
      icon: Bell,
      group: 'Overview',
    },

    {
      title: 'Assign To Franchise',
      href: owner.drivers.index(),
      icon: Users,
      group: 'Driver Management',
    },
    {
      title: 'Suspend Drivers',
      href: owner.suspendDrivers.index(),
      icon: XSquare,
      group: 'Driver Management',
    },

    {
      title: 'Vehicle Management',
      href: owner.vehicles.index(),
      icon: CarTaxiFront,
      group: 'Management',
    },
    {
      title: 'Assign Drivers',
      href: owner.vehicleDrivers.index(),
      icon: Octagon,
      group: 'Management',
    },

    {
      title: 'Boundary Contracts',
      href: owner.boundaryContracts(),
      icon: FileText,
      group: 'Operations',
    },
    {
      title: 'Reports & Analytics',
      href: owner.reportsAndAnalytics(),
      icon: BarChart3,
      group: 'Operations',
    },

    {
      title: 'Revenue Management',
      href: owner.revenueManagement(),
      icon: DollarSign,
      group: 'Finance',
    },
    {
      title: 'Expense Management',
      href: owner.expenseManagement(),
      icon: FileSpreadsheet,
      group: 'Finance',
    },

    {
      title: 'Support Center',
      href: owner.supportCenter(),
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
