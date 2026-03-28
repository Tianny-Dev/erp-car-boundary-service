<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Clock, CreditCard, Pencil, Route } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

// --- Types ---
interface TaxiMetricType {
  id: number;
  flag: string | number;
  per_minute: string | number;
  per_km: string | number;
}

const props = defineProps<{
  taxiMetric: TaxiMetricType;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Trip Matrix',
    href: superAdmin.taxiMetric.index().url,
  },
];

// --- State Management ---
const isDialogOpen = ref(false);

// --- Form Setup ---
const form = useForm({
  flag: props.taxiMetric?.flag ?? 0,
  per_minute: props.taxiMetric?.per_minute ?? 0,
  per_km: props.taxiMetric?.per_km ?? 0,
});

// --- Actions ---
const openEditModal = () => {
  form.flag = props.taxiMetric.flag;
  form.per_minute = props.taxiMetric.per_minute;
  form.per_km = props.taxiMetric.per_km;
  form.clearErrors();
  isDialogOpen.value = true;
};

const submitForm = () => {
  form.patch(superAdmin.taxiMetric.update(props.taxiMetric.id).url, {
    onSuccess: () => {
      isDialogOpen.value = false;
      toast.success('Trip Matrix updated successfully!');
    },
  });
};

const isFormUnchanged = computed(() => {
  return (
    Number(form.flag) === Number(props.taxiMetric.flag) &&
    Number(form.per_minute) === Number(props.taxiMetric.per_minute) &&
    Number(form.per_km) === Number(props.taxiMetric.per_km)
  );
});
</script>

<template>
  <Head title="Trip Matrix Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-4">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="font-mono text-2xl font-bold tracking-tight">
            Trip Matrix Management
          </h2>
          <p class="font-mono text-muted-foreground">Current trip matrix.</p>
        </div>
        <div class="me-5">
          <Button variant="outline" @click="openEditModal">
            <Pencil class="mr-1 h-4 w-4" />
            Edit Rates
          </Button>
        </div>
      </div>

      <!-- Grid Content -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <Card
          class="flex flex-col justify-between transition-all hover:shadow-md"
        >
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="font-mono text-sm">Flag Fall (Base)</CardTitle>
            <CreditCard class="h-5 w-5 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="font-mono text-2xl font-bold">
              ₱{{ taxiMetric?.flag }}
            </div>
            <p class="font-mono text-xs text-muted-foreground">
              Initial charge upon pickup
            </p>
          </CardContent>
        </Card>

        <Card
          class="flex flex-col justify-between transition-all hover:shadow-md"
        >
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="font-mono text-sm">Distance Rate</CardTitle>
            <Route class="h-5 w-5 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="font-mono text-2xl font-bold">
              ₱{{ taxiMetric?.per_km }}
            </div>
            <p class="font-mono text-xs text-muted-foreground">
              Charge per kilometer traveled
            </p>
          </CardContent>
        </Card>

        <Card
          class="flex flex-col justify-between transition-all hover:shadow-md"
        >
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="font-mono text-sm">Time Rate</CardTitle>
            <Clock class="h-5 w-5 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="font-mono text-2xl font-bold">
              ₱{{ taxiMetric?.per_minute }}
            </div>
            <p class="font-mono text-xs text-muted-foreground">
              Charge per minute of trip duration
            </p>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Edit Dialog -->
    <Dialog v-model:open="isDialogOpen">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Update Trip Matrix</DialogTitle>
          <DialogDescription>
            Adjust global rates. Changes will apply to all new trip calculations
            immediately.
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="submitForm" class="space-y-4 py-4">
          <div class="grid gap-2">
            <Label for="flag">Flag Fall (PHP)</Label>
            <Input
              id="flag"
              type="number"
              step="0.01"
              v-model="form.flag"
              :class="{ 'border-red-500': form.errors.flag }"
            />
            <span v-if="form.errors.flag" class="text-xs text-red-500">{{
              form.errors.flag
            }}</span>
          </div>

          <div class="grid gap-2">
            <Label for="per_km">Rate per Kilometer (PHP)</Label>
            <Input
              id="per_km"
              type="number"
              step="0.01"
              v-model="form.per_km"
              :class="{ 'border-red-500': form.errors.per_km }"
            />
            <span v-if="form.errors.per_km" class="text-xs text-red-500">{{
              form.errors.per_km
            }}</span>
          </div>

          <div class="grid gap-2">
            <Label for="per_minute">Rate per Minute (PHP)</Label>
            <Input
              id="per_minute"
              type="number"
              step="0.01"
              v-model="form.per_minute"
              :class="{ 'border-red-500': form.errors.per_minute }"
            />
            <span v-if="form.errors.per_minute" class="text-xs text-red-500">{{
              form.errors.per_minute
            }}</span>
          </div>
        </form>

        <DialogFooter>
          <Button variant="outline" @click="isDialogOpen = false"
            >Cancel</Button
          >
          <Button
            type="submit"
            @click="submitForm"
            :disabled="form.processing || isFormUnchanged"
          >
            <span v-if="form.processing">Updating...</span>
            <span v-else>Update Pricing</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
