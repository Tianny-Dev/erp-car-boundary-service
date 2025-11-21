<script setup lang="ts">
import StepPersonal from '@/components/auth/registration/step/Step1Personal.vue';
import StepAddress from '@/components/auth/registration/step/Step2Address.vue';
import StepAccount from '@/components/auth/registration/step/Step4Account.vue';
import StepUpload from '@/components/auth/registration/step/Step5Uploads.vue';
import StepSecurity from '@/components/auth/registration/step/Step6Security.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import {
  CircleDollarSignIcon,
  IdCardIcon,
  VenusAndMarsIcon,
} from 'lucide-vue-next';
import { reactive, ref } from 'vue';

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
  dti_certificate: null as File | null,
  mayor_permit: null as File | null,
  proof_capital: null as File | null,

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

const consoleSubmit = () => {
  console.log(form);
};

// Configuration for Branch Details Component
const branchDetailFields = {
  franchiseName: 'name',
};
const branchDetailLabels = {
  franchiseName: 'Business / Branch Name',
};
const branchDetailShow = {
  name: false,
  gender: false,
  birthday: false,
};

// Configuration for Branch Uploads Component
const branchUploadLabels = {
  proofOfCapital: 'Proof of Capital or Branch Agreement',
};
const branchUploadShow = {
  nbiClearance: false,
  selfiePicture: false,
  prcCertificate: false,
  professionalLicense: false,
  cvAttachment: false,
};

// Configuration for Branch Address Component
const branchAddressFields = {
  region: 'region',
  province: 'province',
  city: 'city',
  barangay: 'barangay',
  postalCode: 'postal_code',
  address: 'address',
};
const branchAddressLabels = {
  region: 'Region',
  province: 'Province',
  city: 'City',
  barangay: 'Barangay',
  postalCode: 'Postal Code',
  address: 'Street Address',
};

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
const validIdFront = ref<File | null>(null);
const validIdBack = ref<File | null>(null);
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
            <StepPersonal
              :errors="form.errors"
              :field-names="branchDetailFields"
              :labels="branchDetailLabels"
              :show-fields="branchDetailShow"
            />
            <div class="grid gap-2">
              <Label class="font-semibold text-auth-blue">Payment Option</Label>
              <div
                class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
              >
                <div class="flex items-center justify-center bg-auth-blue px-3">
                  <CircleDollarSignIcon class="h-5 w-5 text-white" />
                </div>
                <Select v-model="form.payment_option_id">
                  <SelectTrigger
                    class="flex-1 cursor-pointer border-0 font-mono font-semibold focus-visible:ring-0"
                    ><SelectValue placeholder="Select Option"
                  /></SelectTrigger>
                  <SelectContent class="font-mono font-semibold">
                    <SelectItem
                      v-for="opt in paymentOptions"
                      :key="opt.id"
                      :value="String(opt.id)"
                      class="cursor-pointer"
                    >
                      {{ opt.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <InputError :message="form.errors['payment_option_id']" />
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 pt-4 md:grid-cols-2">
            <StepAddress
              :address-data="branchAddress"
              :field-names="branchAddressFields"
              :labels="branchAddressLabels"
              :errors="form.errors"
            />
          </div>

          <div class="grid grid-cols-1 gap-4 pt-4 md:grid-cols-3">
            <StepUpload
              :errors="form.errors"
              :labels="branchUploadLabels"
              :show-fields="branchUploadShow"
            />
          </div>
        </div>

        <Separator />

        <div class="space-y-4">
          <div class="flex items-center space-x-2">
            <Checkbox id="hasManager" v-model="form.has_manager" />
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
              <StepPersonal
                :errors="form.errors"
                :show-fields="managerDetailShow"
              />

              <div class="grid gap-2">
                <Label class="font-semibold text-auth-blue">Gender</Label>
                <div
                  class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
                >
                  <div
                    class="flex items-center justify-center bg-auth-blue px-3"
                  >
                    <VenusAndMarsIcon class="h-5 w-5 text-white" />
                  </div>
                  <Select v-model="form.manager.gender">
                    <SelectTrigger
                      class="flex-1 cursor-pointer border-0 font-mono font-semibold focus-visible:ring-0"
                      ><SelectValue placeholder="Select Gender"
                    /></SelectTrigger>
                    <SelectContent class="font-mono font-semibold">
                      <SelectItem
                        v-for="g in genderOptions"
                        :key="g.value"
                        :value="g.value"
                        class="cursor-pointer"
                        >{{ g.label }}</SelectItem
                      >
                    </SelectContent>
                  </Select>
                </div>
                <InputError :message="form.errors['manager.gender']" />
              </div>

              <StepSecurity
                :errors="form.errors"
                :show-fields="managerSecurityShow"
              />
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
                  <Select v-model="form.manager.valid_id_type">
                    <SelectTrigger
                      class="flex-1 cursor-pointer border-0 font-mono font-semibold focus-visible:ring-0"
                      ><SelectValue placeholder="Select Option"
                    /></SelectTrigger>
                    <SelectContent class="font-mono font-semibold">
                      <SelectItem
                        v-for="id in idTypeOptions"
                        :key="id.value"
                        :value="id.value"
                        class="cursor-pointer"
                        >{{ id.label }}</SelectItem
                      >
                    </SelectContent>
                  </Select>
                </div>
                <InputError :message="form.errors['manager.valid_id_type']" />
              </div>
              <StepAccount
                :errors="form.errors"
                :show-fields="managerIdentityShow"
              />
              <div class="col-span-1 grid grid-cols-2 gap-4 md:col-span-3">
                <StepAccount
                  :errors="form.errors"
                  :show-fields="managerIdShow"
                  v-model:validIdFront="validIdFront"
                  v-model:validIdBack="validIdBack"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-4">
          <Button type="button" variant="outline" @click="form.reset()"
            >Reset</Button
          >
          <Button
            type="submit"
            :disabled="form.processing"
            @click="consoleSubmit"
          >
            {{ form.processing ? 'Saving...' : 'Create Branch' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
