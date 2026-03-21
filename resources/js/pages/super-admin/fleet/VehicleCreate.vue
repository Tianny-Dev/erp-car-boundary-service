<script setup lang="ts">
import InputError from '@/components/InputError.vue';
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
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { toast } from 'vue-sonner';

// Props passed from Controller
defineProps<{
  franchises: { id: number; name: string }[];
}>();

const form = useForm({
  franchise_id: null as number | null,
  plate_number: '',
  vin: '',
  brand: '',
  model: '',
  year: '' as string | number,
  color: '',
  or_cr: null as File | null,
});

// --- STRICT INPUT HANDLERS ---

const handlePlateInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  let val = target.value.toUpperCase();

  // Strip non-alphanumeric then add space
  val = val.replace(/[^A-Z0-9]/g, '');
  if (val.length > 3 && isNaN(Number(val[2]))) {
    // 3 Letters + Numbers (Standard)
    val = val.slice(0, 3) + ' ' + val.slice(3, 7);
  } else if (val.length > 2) {
    // 2 Letters + Numbers (Motorcycles)
    val = val.slice(0, 2) + ' ' + val.slice(2, 7);
  }

  form.plate_number = val;
  form.errors.plate_number = '';
};

const handleVinInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  let val = target.value.toUpperCase();

  // Remove non-alphanumeric and prohibited letters (I, O, Q)
  val = val.replace(/[^A-Z0-9]/g, '').replace(/[IOQ]/g, '');

  if (val.length > 17) {
    val = val.slice(0, 17);
  }

  form.vin = val;
  form.errors.vin = '';
};

const handleYearInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  let val = target.value;

  if (val.length > 4) {
    val = val.slice(0, 4);
  }

  form.year = val;
  form.errors.year = '';
};

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    form.or_cr = target.files[0];
    form.errors.or_cr = '';
  }
};

const clearFile = () => {
  form.or_cr = null;
  const input = document.getElementById('or_cr_input') as HTMLInputElement;
  if (input) input.value = '';
};

const vehicleColors = [
  'White',
  'Black',
  'Silver',
  'Gray',
  'Red',
  'Blue',
  'Brown',
  'Green',
  'Yellow',
  'Orange',
  'Gold',
  'Beige',
  'Magenta',
  'Purple',
];

const disableSubmit = computed(() => {
  return (
    !form.franchise_id ||
    !form.plate_number ||
    !form.vin ||
    !form.brand ||
    !form.model ||
    !form.year ||
    !form.color ||
    !form.or_cr ||
    form.processing
  );
});

const submit = () => {
  form.post(superAdmin.vehicle.store().url, {
    forceFormData: true,
    onSuccess: () => {
      form.reset();
      toast.success('Vehicle created successfully!');
    },
  });
};

const breadcrumbs = [
  { title: 'Vehicle Management', href: superAdmin.vehicle.index().url },
  { title: 'Create Vehicle', href: superAdmin.vehicle.create().url },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="m-6 max-w-3xl rounded-xl border p-6 shadow-sm">
      <h2 class="mb-6 font-mono text-2xl font-bold">Create New Vehicle</h2>

      <form @submit.prevent="submit" class="space-y-8">
        <div class="grid grid-cols-1 gap-6">
          <div class="space-y-2">
            <Label>Select Franchise</Label>
            <Select
              v-model="form.franchise_id"
              @update:model-value="form.errors.franchise_id = ''"
            >
              <SelectTrigger
                :class="{ 'border-red-500': form.errors.franchise_id }"
              >
                <SelectValue placeholder="Select Franchise..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="item in franchises"
                  :key="item.id"
                  :value="String(item.id)"
                >
                  {{ item.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <InputError :message="form.errors.franchise_id" />
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div class="space-y-2">
            <Label>Plate Number</Label>
            <Input
              v-model="form.plate_number"
              placeholder="e.g., ABC 1234"
              :class="{ 'border-red-500': form.errors.plate_number }"
              @input="handlePlateInput"
            />
            <InputError :message="form.errors.plate_number" />
          </div>

          <div class="space-y-2">
            <Label>VIN (Chassis Number)</Label>
            <Input
              v-model="form.vin"
              placeholder="17-character VIN"
              class="font-mono uppercase"
              :class="{ 'border-red-500': form.errors.vin }"
              @input="handleVinInput"
            />
            <p class="px-1 text-[10px] text-muted-foreground">
              Excludes I, O, and Q
            </p>
            <InputError :message="form.errors.vin" />
          </div>
        </div>

        <div class="my-4 border-t" />

        <div class="space-y-4">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label>Brand</Label>
              <Input
                v-model="form.brand"
                placeholder="e.g., Toyota"
                :class="{ 'border-red-500': form.errors.brand }"
                @input="form.errors.brand = ''"
              />
              <InputError :message="form.errors.brand" />
            </div>
            <div class="space-y-2">
              <Label>Model</Label>
              <Input
                v-model="form.model"
                placeholder="e.g., Corolla"
                :class="{ 'border-red-500': form.errors.model }"
                @input="form.errors.model = ''"
              />
              <InputError :message="form.errors.model" />
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="flex flex-col space-y-2">
              <Label>Year</Label>
              <Input
                type="number"
                v-model="form.year"
                placeholder="e.g., 2024"
                :class="{ 'border-red-500': form.errors.year }"
                @input="handleYearInput"
              />
              <InputError :message="form.errors.year" />
            </div>
            <div class="flex flex-col space-y-2">
              <Label>Color</Label>
              <Select
                v-model="form.color"
                @update:model-value="form.errors.color = ''"
              >
                <SelectTrigger :class="{ 'border-red-500': form.errors.color }">
                  <SelectValue placeholder="Select Color" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="c in vehicleColors" :key="c" :value="c">
                    {{ c }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <InputError :message="form.errors.color" />
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <Label class="font-bold">OR-CR Document</Label>
              <button
                v-if="form.or_cr"
                type="button"
                @click="clearFile"
                class="text-xs font-medium text-red-500 hover:text-red-700"
              >
                Clear Selection
              </button>
            </div>

            <Input
              id="or_cr_input"
              type="file"
              accept=".jpg,.jpeg,.png,.pdf"
              @change="handleFileChange"
              :class="{ 'border-red-500': form.errors.or_cr }"
              class="cursor-pointer file:cursor-pointer"
            />
            <p class="text-xs text-muted-foreground">
              Upload a scan or photo of the OR-CR (PDF, JPG, PNG).
            </p>
            <div
              v-if="form.or_cr"
              class="mt-2 text-xs font-medium text-green-600"
            >
              Selected: {{ form.or_cr.name }}
            </div>
            <InputError :message="form.errors.or_cr" />
          </div>
        </div>

        <div class="flex justify-end gap-4 border-t pt-6">
          <Button type="button" variant="outline" @click="form.reset()">
            Reset
          </Button>
          <Button type="submit" :disabled="disableSubmit">
            {{ form.processing ? 'Saving...' : 'Create Vehicle' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
