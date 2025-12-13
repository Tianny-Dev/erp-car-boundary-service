<script setup lang="ts">
import StepPersonal from '@/components/auth/registration/step/Step1Personal.vue';
import StepAddress from '@/components/auth/registration/step/Step2Address.vue';
import StepAccount from '@/components/auth/registration/step/Step4Account.vue';
import StepSecurity from '@/components/auth/registration/step/Step6Security.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { useAddress } from '@/composables/useAddress';
import AppLayout from '@/layouts/AppLayout.vue';
import superAdmin from '@/routes/super-admin';
import { useForm } from '@inertiajs/vue3';
import { IdCardIcon, VenusAndMarsIcon } from 'lucide-vue-next';
import { computed, reactive, watchEffect } from 'vue';
import { toast } from 'vue-sonner';

// Props passed from Controller
defineProps<{
  genderOptions: Array<{ value: string; label: string }>;
  idTypeOptions: Array<{ value: string; label: string }>;
}>();

// Initialize Address Logic for Manager
const managerAddress = reactive(useAddress());

const form = useForm({
  username: '',
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
});

const disableSubmit = computed(() => {
  // Helper to check required fields except province
  const checkRequired = (obj: Record<string, any>, exclude: string[] = []) => {
    return Object.entries(obj).every(([key, value]) => {
      if (exclude.includes(key)) return true; // skip excluded fields
      return value !== '' && value !== null; // must have value
    });
  };

  // Manager required fields (exclude province)
  const managerValid = checkRequired(
    {
      username: form.username,
      name: form.name,
      email: form.email,
      phone: form.phone,
      password: form.password,
      password_confirmation: form.password_confirmation,
      gender: form.gender,
      address: form.address,
      region: form.region,
      province: form.province, // excluded
      city: form.city,
      barangay: form.barangay,
      postal_code: form.postal_code,
      valid_id_type: form.valid_id_type,
      valid_id_number: form.valid_id_number,
      front_valid_id_picture: form.front_valid_id_picture,
      back_valid_id_picture: form.back_valid_id_picture,
    },
    ['province', 'name'],
  );

  // Final rule
  return !managerValid;
});

// Configuration for Manager Details Component
const managerDetailShow = {
  gender: false,
  birthday: false,
  franchiseName: false,
};

// Configuration for Manager Security Component
const managerSecurityShow = {
  terms1: false,
  terms2: false,
};

// Configuration for Manager Identity Component
const managerIdentityShow = {
  licenseNumber: false,
  licenseExpiry: false,
  validIdType: false,
  validIdUpload: false,
  expertise: false,
  yearExperience: false,
};
const managerIdShow = {
  licenseNumber: false,
  licenseExpiry: false,
  validIdType: false,
  expertise: false,
  yearExperience: false,
  validIdNumber: false,
};

// Configuration for Manager Address Component
const managerFieldNames = {
  region: 'region',
  province: 'province',
  city: 'city',
  barangay: 'barangay',
  postalCode: 'postal_code',
  address: 'address',
};
const managerLabels = {
  region: 'Region',
  province: 'Province',
  city: 'City',
  barangay: 'Barangay',
  postalCode: 'Postal',
  address: 'Street',
};

const submit = () => {
  form.post(superAdmin.manager.store().url, {
    forceFormData: true,
    onSuccess: () => {
      form.reset();
      toast.success('Manager created successfully!');
    },
  });
};

const breadcrumbs = [
  { title: 'Dashboard', href: superAdmin.dashboard().url },
  { title: 'Create Manager', href: superAdmin.manager.create().url },
];

watchEffect(() => {
  // Sync Manager Address
  form.region = managerAddress.selectedRegion;
  form.province = managerAddress.selectedProvince;
  form.city = managerAddress.selectedCity;
  form.barangay = managerAddress.selectedBarangay;
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="m-6 max-w-6xl rounded-xl border p-6 shadow-sm">
      <h2 class="mb-6 font-mono text-2xl font-bold">Create New Manager</h2>

      <form @submit.prevent="submit" class="space-y-8">
        <div class="space-y-4 rounded-lg p-1">
          <div class="grid grid-cols-1 items-start gap-4 md:grid-cols-3">
            <StepPersonal
              :errors="form.errors"
              :show-fields="managerDetailShow"
              v-model:name="form.name"
              v-model:email="form.email"
              v-model:phone="form.phone"
              v-model:user-name="form.username"
            />

            <div class="grid gap-2">
              <Label class="font-semibold text-auth-blue">Gender</Label>
              <div
                class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
              >
                <div class="flex items-center justify-center bg-auth-blue px-3">
                  <VenusAndMarsIcon class="h-5 w-5 text-white" />
                </div>
                <Select v-model="form.gender">
                  <SelectTrigger
                    class="flex-1 border-0 font-mono font-semibold focus-visible:ring-0"
                    ><SelectValue placeholder="Select Gender"
                  /></SelectTrigger>
                  <SelectContent class="font-mono font-semibold">
                    <SelectItem
                      v-for="g in genderOptions"
                      :key="g.value"
                      :value="g.value"
                      >{{ g.label }}</SelectItem
                    >
                  </SelectContent>
                </Select>
              </div>
              <InputError :message="form.errors['gender']" />
            </div>

            <StepSecurity
              :errors="form.errors"
              :show-fields="managerSecurityShow"
              v-model:password="form.password"
              v-model:confirm-password="form.password_confirmation"
            />
          </div>

          <div class="grid grid-cols-1 items-start gap-4 pt-4 md:grid-cols-3">
            <StepAddress
              :address-data="managerAddress"
              :field-names="managerFieldNames"
              :labels="managerLabels"
              :errors="form.errors"
              v-model:street-address="form.address"
              v-model:postal-code="form.postal_code"
            />
          </div>
          <div
            class="mt-6 grid grid-cols-[1fr_1.5fr] items-start gap-4 border-t pt-4"
          >
            <div class="grid grid-cols-1 items-start gap-4">
              <div class="grid gap-2">
                <Label class="font-semibold text-auth-blue"
                  >Valid ID Type</Label
                >
                <div
                  class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
                >
                  <div
                    class="flex items-center justify-center bg-auth-blue px-3"
                  >
                    <IdCardIcon class="h-5 w-5 text-white" />
                  </div>
                  <Select v-model="form.valid_id_type">
                    <SelectTrigger
                      class="flex-1 border-0 font-mono font-semibold focus-visible:ring-0"
                      ><SelectValue placeholder="Select Option"
                    /></SelectTrigger>
                    <SelectContent class="font-mono font-semibold">
                      <SelectItem
                        v-for="id in idTypeOptions"
                        :key="id.value"
                        :value="id.value"
                        >{{ id.label }}</SelectItem
                      >
                    </SelectContent>
                  </Select>
                </div>
                <InputError :message="form.errors['valid_id_type']" />
              </div>
              <StepAccount
                :errors="form.errors"
                :show-fields="managerIdentityShow"
                v-model:valid-id-number="form.valid_id_number"
              />
            </div>
            <div class="col-span-1 grid items-start gap-4">
              <StepAccount
                :errors="form.errors"
                :show-fields="managerIdShow"
                v-model:valid-id-front="form.front_valid_id_picture"
                v-model:valid-id-back="form.back_valid_id_picture"
              />
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-4">
          <Button type="button" variant="outline" @click="form.reset()"
            >Reset</Button
          >
          <Button type="submit" :disabled="form.processing || disableSubmit">
            {{ form.processing ? 'Saving...' : 'Create Manager' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
