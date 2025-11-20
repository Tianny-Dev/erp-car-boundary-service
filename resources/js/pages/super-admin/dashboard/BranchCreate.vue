<script setup lang="ts">
import StepAddress from '@/components/auth/registration/step/Step2Address.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { useAddress } from '@/composables/useAddress';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

// Props passed from Controller
defineProps<{
  paymentOptions: Array<{ id: number; name: string }>;
  genderOptions: Array<{ value: string; label: string }>;
  idTypeOptions: Array<{ value: string; label: string }>;
}>();

// 1. Initialize Address Logic for Branch
const branchAddress = reactive(useAddress());

// 2. Initialize Address Logic for Manager
const managerAddress = reactive(useAddress());

const form = useForm({
  // Branch Details
  name: '',
  email: '',
  phone: '',
  payment_option_id: '',
  address: '',
  region: '',
  province: '',
  city: '',
  barangay: '',
  postal_code: '',

  // Files
  dti_registration_attachment: null as File | null,
  mayor_permit_attachment: null as File | null,
  proof_agreement_attachment: null as File | null,

  // Manager Toggle
  has_manager: false,

  // Manager Details
  manager: {
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    gender: '',
    address: '',
    region: '',
    province: '',
    city: '',
    barangay: '',
    postal_code: '',
    valid_id_type: '',
    valid_id_number: '',
    front_valid_id_picture: null as File | null,
    back_valid_id_picture: null as File | null,
  },
});

// Configuration for Branch Address Component
const branchFieldNames = {
  region: 'region',
  province: 'province',
  city: 'city',
  barangay: 'barangay',
  postalCode: 'postal_code',
  address: 'address',
};
const branchLabels = {
  region: 'Region',
  province: 'Province',
  city: 'City',
  barangay: 'Barangay',
  postalCode: 'Postal Code',
  address: 'Street Address',
};

// Configuration for Manager Address Component
const managerFieldNames = {
  region: 'manager.region',
  province: 'manager.province',
  city: 'manager.city',
  barangay: 'manager.barangay',
  postalCode: 'manager.postal_code',
  address: 'manager.address',
};
const managerLabels = {
  region: 'Manager Region',
  province: 'Manager Province',
  city: 'Manager City',
  barangay: 'Manager Barangay',
  postalCode: 'Manager Postal',
  address: 'Manager Address',
};

const submit = () => {
  form.post(superAdmin.branch.store().url, {
    forceFormData: true,
    onSuccess: () => form.reset(),
  });
};

const handleManagerToggle = (value: boolean) => {
  form.has_manager = value;
};

const breadcrumbs = [
  { title: 'Dashboard', href: superAdmin.dashboard().url },
  { title: 'Create Branch', href: superAdmin.branch.create().url },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-4xl rounded-xl border bg-white p-6 shadow-sm">
      <h2 class="mb-6 text-2xl font-bold">Create New Branch</h2>

      <form @submit.prevent="submit" class="space-y-8">
        <div class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-700">
            Branch Information
          </h3>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label>Branch Name</Label>
              <Input v-model="form.name" placeholder="Branch Name" />
              <span v-if="form.errors.name" class="text-sm text-red-500">{{
                form.errors.name
              }}</span>
            </div>
            <div class="space-y-2">
              <Label>Payment Option</Label>
              <Select v-model="form.payment_option_id">
                <SelectTrigger
                  ><SelectValue placeholder="Select Option"
                /></SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="opt in paymentOptions"
                    :key="opt.id"
                    :value="String(opt.id)"
                  >
                    {{ opt.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label>Email</Label>
              <Input v-model="form.email" type="email" />
              <span v-if="form.errors.email" class="text-sm text-red-500">{{
                form.errors.email
              }}</span>
            </div>
            <div class="space-y-2">
              <Label>Phone</Label>
              <Input v-model="form.phone" />
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 pt-4 md:grid-cols-2">
            <StepAddress
              :address-data="branchAddress"
              :field-names="branchFieldNames"
              :labels="branchLabels"
              :errors="form.errors"
            />
          </div>

          <div class="grid grid-cols-1 gap-4 pt-4 md:grid-cols-3">
            <div class="space-y-2">
              <Label>DTI Registration</Label>
              <Input
                type="file"
                @input="
                  form.dti_registration_attachment = $event.target.files[0]
                "
              />
            </div>
            <div class="space-y-2">
              <Label>Mayor's Permit</Label>
              <Input
                type="file"
                @input="form.mayor_permit_attachment = $event.target.files[0]"
              />
            </div>
            <div class="space-y-2">
              <Label>Proof of Agreement</Label>
              <Input
                type="file"
                @input="
                  form.proof_agreement_attachment = $event.target.files[0]
                "
              />
            </div>
          </div>
        </div>

        <Separator />

        <div class="space-y-4">
          <div class="flex items-center space-x-2">
            <Checkbox
              id="hasManager"
              :checked="form.has_manager"
              @update:checked="handleManagerToggle"
            />
            <Label
              for="hasManager"
              class="cursor-pointer text-lg font-semibold"
            >
              Register a new Manager?
            </Label>
          </div>

          <div
            v-if="form.has_manager"
            class="space-y-4 rounded-lg border bg-gray-50 p-4"
          >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label>Full Name</Label>
                <Input v-model="form.manager.name" />
                <span
                  v-if="form.errors['manager.name']"
                  class="text-sm text-red-500"
                  >{{ form.errors['manager.name'] }}</span
                >
              </div>
              <div class="space-y-2">
                <Label>Email</Label>
                <Input v-model="form.manager.email" type="email" />
                <span
                  v-if="form.errors['manager.email']"
                  class="text-sm text-red-500"
                  >{{ form.errors['manager.email'] }}</span
                >
              </div>
              <div class="space-y-2">
                <Label>Phone</Label>
                <Input v-model="form.manager.phone" />
              </div>
              <div class="space-y-2">
                <Label>Password</Label>
                <Input v-model="form.manager.password" type="password" />
              </div>
              <div class="space-y-2">
                <Label>Confirm Password</Label>
                <Input
                  v-model="form.manager.password_confirmation"
                  type="password"
                />
              </div>
              <div class="space-y-2">
                <Label>Gender</Label>
                <Select v-model="form.manager.gender">
                  <SelectTrigger
                    ><SelectValue placeholder="Select Gender"
                  /></SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="g in genderOptions"
                      :key="g.value"
                      :value="g.value"
                      >{{ g.label }}</SelectItem
                    >
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 pt-4 md:grid-cols-2">
              <StepAddress
                :address-data="managerAddress"
                :field-names="managerFieldNames"
                :labels="managerLabels"
                :errors="form.errors"
                v-model:street-address="form.manager.address"
                v-model:postal-code="form.manager.postal_code"
              />
            </div>

            <div class="grid grid-cols-1 gap-4 border-t pt-4 md:grid-cols-3">
              <div class="space-y-2">
                <Label>ID Type</Label>
                <Select v-model="form.manager.valid_id_type">
                  <SelectTrigger
                    ><SelectValue placeholder="Select ID Type"
                  /></SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="id in idTypeOptions"
                      :key="id.value"
                      :value="id.value"
                      >{{ id.label }}</SelectItem
                    >
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-2">
                <Label>ID Number</Label>
                <Input v-model="form.manager.valid_id_number" />
              </div>
              <div class="col-span-1 grid grid-cols-2 gap-4 md:col-span-3">
                <div class="space-y-2">
                  <Label>Front ID Pic</Label>
                  <Input
                    type="file"
                    @input="
                      form.manager.front_valid_id_picture =
                        $event.target.files[0]
                    "
                  />
                </div>
                <div class="space-y-2">
                  <Label>Back ID Pic</Label>
                  <Input
                    type="file"
                    @input="
                      form.manager.back_valid_id_picture =
                        $event.target.files[0]
                    "
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-4">
          <Button type="button" variant="secondary" @click="form.reset()"
            >Reset</Button
          >
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Saving...' : 'Create Branch' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
