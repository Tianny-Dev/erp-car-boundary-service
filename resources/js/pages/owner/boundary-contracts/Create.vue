<script setup lang="ts">
import DatePicker from '@/components/DatePicker.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import owner from '@/routes/owner';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3'; // Added router
import { computed, onMounted } from 'vue'; // Added onMounted
import { toast } from 'vue-sonner';

interface Vehicle {
  id: number;
  plate_number: string;
  brand: string;
  model: string;
  status: string;
}

interface Driver {
  id: number;
  username: string;
}

interface Props {
  vehicles: Vehicle[];
  drivers: Driver[];
  contract?: any;
}

const props = defineProps<Props>();

// --- Security Logic: Check status on load ---
onMounted(() => {
  if (props.contract && props.contract.status !== 'active') {
    toast.error('Only active contracts can be edited.');
    router.visit(owner.boundaryContracts.index().url);
  }
});

const isEditing = computed(() => !!props.contract);

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Boundary Contract', href: owner.boundaryContracts.index().url },
  { title: isEditing.value ? 'Edit Contract' : 'Create Contract', href: '#' },
];

const form = useForm({
  driver: props.contract?.driver || '',
  vehicle: props.contract?.vehicle || '',
  name: props.contract?.name || '',
  amount: props.contract?.amount || '',
  coverage_area: props.contract?.coverage_area || '',
  contract_terms: props.contract?.contract_terms || '',
  start_date: props.contract?.start_date || '',
  end_date: props.contract?.end_date || '',
  renewal_terms: props.contract?.renewal_terms || '',
});

const disableSubmit = computed(() => {
  return (
    !form.driver ||
    !form.vehicle ||
    !form.name ||
    !form.amount ||
    !form.start_date ||
    !form.end_date
  );
});

const submit = () => {
  if (isEditing.value) {
    form.put(
      owner.boundaryContracts.update({ boundary_contract: props.contract.id })
        .url,
      {
        onSuccess: () => toast.success('Contract updated successfully!'),
      },
    );
  } else {
    form.post(owner.boundaryContracts.store().url, {
      onSuccess: () => {
        form.reset();
        toast.success('Contract created successfully!');
      },
    });
  }
};
</script>

<template>
  <Head :title="isEditing ? 'Edit Contract' : 'Create Contract'" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="m-6 max-w-3xl rounded-xl border p-6 shadow-sm">
      <h2 class="mb-6 font-mono text-2xl font-bold">
        {{
          isEditing ? 'Edit Boundary Contract' : 'Create New Boundary Contract'
        }}
      </h2>

      <form @submit.prevent="submit" class="flex flex-col gap-6">
        <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2">
          <div class="grid gap-2">
            <Label :class="{ 'text-muted-foreground': isEditing }"
              >Driver Username</Label
            >

            <Select v-model="form.driver" :disabled="isEditing">
              <SelectTrigger
                :class="{ 'cursor-not-allowed bg-muted opacity-70': isEditing }"
              >
                <SelectValue
                  :placeholder="
                    isEditing ? 'Loading driver...' : 'Select Driver'
                  "
                />
              </SelectTrigger>

              <SelectContent>
                <div
                  v-if="drivers.length === 0"
                  class="p-2 text-sm text-gray-500"
                >
                  <p>No available active drivers found</p>
                </div>
                <SelectItem
                  v-else
                  v-for="driver in drivers"
                  :key="driver.id"
                  :value="driver.id"
                >
                  {{ driver.username }}
                </SelectItem>
              </SelectContent>
            </Select>

            <p v-if="isEditing" class="text-[0.8rem] text-muted-foreground">
              Driver cannot be changed for an existing active contract.
            </p>

            <InputError :message="form.errors.driver" />
          </div>

          <div class="grid gap-2">
            <Label>Vehicle</Label>
            <Select v-model="form.vehicle">
              <SelectTrigger :class="{ 'border-red-500': form.errors.vehicle }">
                <SelectValue placeholder="Select Vehicle" />
              </SelectTrigger>

              <SelectContent>
                <div
                  v-if="vehicles.length === 0"
                  class="p-2 text-sm text-gray-500"
                >
                  <p>No available vehicles found</p>
                </div>
                <SelectItem
                  v-for="vehicle in vehicles"
                  :key="vehicle.id"
                  :value="vehicle.id"
                >
                  {{ vehicle.plate_number }} - {{ vehicle.brand }}
                  {{ vehicle.model }}
                </SelectItem>
              </SelectContent>
            </Select>
            <InputError :message="form.errors.vehicle" />
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div class="grid gap-2">
            <Label>Contract Name</Label>
            <Input
              v-model="form.name"
              placeholder="e.g. Standard Agreement"
              :class="{ 'border-red-500': form.errors.name }"
            />
            <InputError :message="form.errors.name" />
          </div>
          <div class="grid gap-2">
            <Label>Amount (PHP)</Label>
            <Input
              v-model="form.amount"
              type="number"
              step="0.01"
              :class="{ 'border-red-500': form.errors.amount }"
            />
            <InputError :message="form.errors.amount" />
          </div>
          <div class="grid gap-2">
            <Label>Start Date</Label>
            <DatePicker v-model="form.start_date" />
            <InputError :message="form.errors.start_date" />
          </div>
          <div class="grid gap-2">
            <Label>End Date</Label>
            <DatePicker v-model="form.end_date" :min-date="form.start_date" />
            <InputError :message="form.errors.end_date" />
          </div>
        </div>

        <div class="grid gap-2">
          <Label>Coverage Area</Label>
          <Textarea
            v-model="form.coverage_area"
            placeholder="Operational area..."
          />
        </div>

        <div class="grid gap-2">
          <Label>Contract Terms</Label>
          <Textarea v-model="form.contract_terms" class="h-24" />
        </div>

        <div class="grid gap-2">
          <Label>Renewal Terms</Label>
          <Textarea v-model="form.renewal_terms" />
        </div>

        <div class="flex justify-end gap-4">
          <Button type="button" variant="outline" @click="form.reset()"
            >Reset</Button
          >
          <Button type="submit" :disabled="form.processing || disableSubmit">
            {{
              form.processing
                ? 'Saving...'
                : isEditing
                  ? 'Update Contract'
                  : 'Create Contract'
            }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
