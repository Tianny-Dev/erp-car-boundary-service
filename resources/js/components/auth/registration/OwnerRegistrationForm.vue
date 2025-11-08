<script setup lang="ts">
import InputError from '@/components/InputError.vue';
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
import { Spinner } from '@/components/ui/spinner';
import { useAddress } from '@/composables/useAddress';
import AuthBase from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';
import {
  Building2,
  Check,
  ChevronLeft,
  ChevronRight,
  Eye,
  EyeOff,
  File,
  Home,
  IdCard,
  Lock,
  Mail,
  MapPin,
  PhoneCall,
  User,
} from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

defineProps<{
  genderOptions: { value: string; label: string }[];
  idTypes: { value: string; label: string }[];
  paymentOptions: {
    id: string;
    label: string;
    color: string;
  }[];
  userType: {
    encrypted_id: string;
    name: string;
  };
}>();

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const currentStep = ref(1);
const totalSteps = 6;
const birthday = ref('');
const selectedGender = ref('');
const selectedIdType = ref('');
const selectedPayout = ref('cash');

const validIdFront = ref<File | null>(null);
const validIdBack = ref<File | null>(null);

const handleFileUpload = (event: Event, fileRef: any) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    fileRef.value = target.files[0];
  }
};

const removeFile = (fileRef: any) => {
  fileRef.value = null;
};

// Calculate age from birthday
const calculatedAge = computed(() => {
  if (!birthday.value) return null;

  const birthDate = new Date(birthday.value);
  const today = new Date();
  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();

  if (
    monthDiff < 0 ||
    (monthDiff === 0 && today.getDate() < birthDate.getDate())
  ) {
    age--;
  }

  return age;
});

const stepTitles: Record<number, string> = {
  1: 'Basic Information',
  2: 'Home Address',
  3: 'Franchise Details',
  4: 'Preferences',
  5: 'Documents to Upload',
  6: 'Account Security',
};

const canProceed = computed(() => {
  // Add your validation logic here
  return true;
});

const nextStep = () => {
  if (currentStep.value < totalSteps) {
    currentStep.value++;
  }
};

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const goToStep = (step: number) => {
  if (step <= totalSteps && step >= 1) {
    currentStep.value = step;
  }
};

const homeAddress = reactive(useAddress());
const franchiseAddress = reactive(useAddress());
</script>

<template>
  <AuthBase
    text-overlay="SIGN UP AND ENJOY FAST, SAFE, AND ECO-FRIENDLY RIDES WITH ERP E-TAXI."
    title-registration="Franchise Registration"
    :go-back-home-button="true"
    :user-type-name="userType.name"
  >
    <Head title="Franchise Registration" />

    <Form
      v-bind="store.form()"
      :reset-on-success="['password', 'password_confirmation']"
      v-slot="{ errors, processing }"
      class="flex flex-col gap-6"
    >
      <input type="hidden" name="user_type_id" :value="userType.encrypted_id" />
      <!-- Step Title -->
      <h1 class="text-center text-2xl font-black text-auth-blue">
        {{ stepTitles[currentStep] }}
      </h1>

      <!-- Step 1: Personal Information -->
      <div v-show="currentStep === 1" class="space-y-4">
        <div class="grid gap-2">
          <Label for="name" class="text-auth-blue">Full Name</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <User class="h-5 w-5 text-white" />
            </div>
            <Input
              id="name"
              type="text"
              name="name"
              required
              autofocus
              autocomplete="name"
              placeholder="Juan Delacruz"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.name" />
        </div>

        <div class="grid gap-2">
          <Label for="phone" class="text-auth-blue">Phone Number</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <PhoneCall class="h-5 w-5 text-white" />
            </div>
            <Input
              id="phone"
              type="tel"
              name="phone"
              required
              autocomplete="phone"
              placeholder="639123456789"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.phone" />
        </div>

        <div class="grid gap-2">
          <Label for="email" class="text-auth-blue">Email Address</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <Mail class="h-5 w-5 text-white" />
            </div>
            <Input
              id="email"
              type="email"
              name="email"
              required
              autocomplete="email"
              placeholder="email@example.com"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.email" />
        </div>

        <div class="grid gap-2">
          <Label for="gender" class="text-auth-blue">Gender</Label>
          <select
            id="gender"
            name="gender"
            v-model="selectedGender"
            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-auth-blue focus-visible:ring-2 focus-visible:ring-auth-blue focus-visible:ring-offset-2 focus-visible:outline-none"
          >
            <option value="" disabled>Select your gender</option>
            <option
              v-for="option in genderOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
          <InputError :message="errors.gender" />
        </div>
      </div>

      <!-- Step 2: Account Details -->
      <div v-show="currentStep === 2" class="space-y-4">
        <!-- Home Region -->
        <div class="grid gap-2">
          <Label for="home_region" class="text-auth-blue">Region (home)</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Select v-model="homeAddress.selectedRegion" name="home_region">
              <SelectTrigger
                class="flex-1 border-0 focus-visible:ring-0"
                :disabled="homeAddress.isLoadingRegions"
              >
                <SelectValue placeholder="Select Region" />
                <Spinner
                  v-if="homeAddress.isLoadingRegions"
                  class="ml-auto h-4 w-4"
                />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="r in homeAddress.regions"
                  :key="r.code"
                  :value="r.name"
                >
                  {{ r.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <InputError :message="errors?.home_region" />
        </div>

        <!-- Home Province Dropdown for Non-NCR -->
        <div
          v-if="!homeAddress.isNcr && homeAddress.provinces.length"
          class="grid gap-2"
        >
          <Label for="home_province" class="text-auth-blue"
            >Province (home)</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Select v-model="homeAddress.selectedProvince" name="home_province">
              <SelectTrigger
                class="flex-1 border-0 focus-visible:ring-0"
                :disabled="
                  homeAddress.isLoadingProvinces || !homeAddress.selectedRegion
                "
              >
                <SelectValue placeholder="Select Province" />
                <Spinner
                  v-if="homeAddress.isLoadingProvinces"
                  class="ml-auto h-4 w-4"
                />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="p in homeAddress.provinces"
                  :key="p.code"
                  :value="p.name"
                >
                  {{ p.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <InputError :message="errors?.home_province" />
        </div>

        <!-- Show "N/A" Province for NCR -->
        <div v-else-if="homeAddress.isNcr" class="grid gap-2">
          <Label for="home_province" class="text-auth-blue"
            >Province (home)</Label
          >
          <div
            class="flex w-full max-w-sm items-center rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-gray-500"
          >
            N/A
          </div>
        </div>

        <!-- Home City / Municipality -->
        <div
          class="grid gap-2"
          v-if="homeAddress.cities.length || homeAddress.isLoadingCities"
        >
          <Label for="home_city" class="text-auth-blue"
            >City / Municipality (home)</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Select v-model="homeAddress.selectedCity" name="home_city">
              <SelectTrigger
                class="flex-1 border-0 focus-visible:ring-0"
                :disabled="homeAddress.isLoadingCities"
              >
                <SelectValue placeholder="Select City / Municipality" />
                <Spinner
                  v-if="homeAddress.isLoadingCities"
                  class="ml-auto h-4 w-4"
                />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="c in homeAddress.cities"
                  :key="c.code"
                  :value="c.name"
                >
                  {{ c.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <InputError :message="errors?.home_city" />
        </div>

        <!-- Home Barangay -->
        <div
          class="grid gap-2"
          v-if="homeAddress.barangays.length || homeAddress.isLoadingBarangays"
        >
          <Label for="home_barangay" class="text-auth-blue"
            >Barangay (home)</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Select v-model="homeAddress.selectedBarangay" name="home_barangay">
              <SelectTrigger
                class="flex-1 border-0 focus-visible:ring-0"
                :disabled="homeAddress.isLoadingBarangays"
              >
                <SelectValue placeholder="Select Barangay" />
                <Spinner
                  v-if="homeAddress.isLoadingBarangays"
                  class="ml-auto h-4 w-4"
                />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="b in homeAddress.barangays"
                  :key="b.code"
                  :value="b.name"
                >
                  {{ b.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <InputError :message="errors?.home_barangay" />
        </div>

        <!-- Home Postal Code -->
        <div class="grid gap-2">
          <Label for="home_postal_code" class="text-auth-blue"
            >Postal Code (home)</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Input
              id="home_postal_code"
              type="number"
              name="home_postal_code"
              required
              autofocus
              autocomplete="home_postal_code"
              placeholder="2009"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.home_postal_code" />
        </div>

        <!-- Home Address -->
        <div class="grid gap-2">
          <Label for="home_address" class="text-auth-blue">Home Address</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <Home class="h-5 w-5 text-white" />
            </div>
            <Input
              id="home_address"
              type="text"
              name="home_address"
              required
              autocomplete="address"
              placeholder="123 St. Barangay City"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.address" />
        </div>
      </div>

      <!-- Step 3: Franchise -->
      <div v-show="currentStep === 3" class="space-y-4">
        <!-- Franchise Region -->
        <div class="grid gap-2">
          <Label for="franchise_region" class="text-auth-blue">Region</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Select
              v-model="franchiseAddress.selectedRegion"
              name="franchise_region"
            >
              <SelectTrigger
                class="flex-1 border-0 focus-visible:ring-0"
                :disabled="franchiseAddress.isLoadingRegions"
              >
                <SelectValue placeholder="Select Region" />
                <Spinner
                  v-if="franchiseAddress.isLoadingRegions"
                  class="ml-auto h-4 w-4"
                />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="r in franchiseAddress.regions"
                  :key="r.code"
                  :value="r.name"
                >
                  {{ r.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <InputError :message="errors?.franchise_region" />
        </div>

        <!-- Franchise Province -->
        <div
          v-if="!franchiseAddress.isNcr && franchiseAddress.provinces.length"
          class="grid gap-2"
        >
          <Label for="franchise_province" class="text-auth-blue"
            >Province</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Select
              v-model="franchiseAddress.selectedProvince"
              name="franchise_province"
            >
              <SelectTrigger
                class="flex-1 border-0 focus-visible:ring-0"
                :disabled="
                  franchiseAddress.isLoadingProvinces ||
                  !franchiseAddress.selectedRegion
                "
              >
                <SelectValue placeholder="Select Province" />
                <Spinner
                  v-if="franchiseAddress.isLoadingProvinces"
                  class="ml-auto h-4 w-4"
                />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="p in franchiseAddress.provinces"
                  :key="p.code"
                  :value="p.name"
                >
                  {{ p.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <InputError :message="errors?.franchise_province" />
        </div>

        <!-- Show "N/A" Province for NCR -->
        <div v-else-if="franchiseAddress.isNcr" class="grid gap-2">
          <Label for="province" class="text-auth-blue">Province</Label>
          <div
            class="flex w-full max-w-sm items-center rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-gray-500"
          >
            N/A
          </div>
        </div>

        <!-- Franchise City -->
        <div
          class="grid gap-2"
          v-if="
            franchiseAddress.cities.length || franchiseAddress.isLoadingCities
          "
        >
          <Label for="franchise_city" class="text-auth-blue"
            >City / Municipality</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Select
              v-model="franchiseAddress.selectedCity"
              name="franchise_city"
            >
              <SelectTrigger
                class="flex-1 border-0 focus-visible:ring-0"
                :disabled="franchiseAddress.isLoadingCities"
              >
                <SelectValue placeholder="Select City / Municipality" />
                <Spinner
                  v-if="franchiseAddress.isLoadingCities"
                  class="ml-auto h-4 w-4"
                />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="c in franchiseAddress.cities"
                  :key="c.code"
                  :value="c.name"
                >
                  {{ c.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <InputError :message="errors?.franchise_city" />
        </div>

        <!-- Franchise Barangay -->
        <div
          class="grid gap-2"
          v-if="
            franchiseAddress.barangays.length ||
            franchiseAddress.isLoadingBarangays
          "
        >
          <Label for="franchise_barangay" class="text-auth-blue"
            >Barangay</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Select
              v-model="franchiseAddress.selectedBarangay"
              name="franchise_barangay"
            >
              <SelectTrigger
                class="flex-1 border-0 focus-visible:ring-0"
                :disabled="franchiseAddress.isLoadingBarangays"
              >
                <SelectValue placeholder="Select Barangay" />
                <Spinner
                  v-if="franchiseAddress.isLoadingBarangays"
                  class="ml-auto h-4 w-4"
                />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="b in franchiseAddress.barangays"
                  :key="b.code"
                  :value="b.name"
                >
                  {{ b.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <InputError :message="errors?.franchise_barangay" />
        </div>

        <!-- Franchise Postal Code -->
        <div class="grid gap-2">
          <Label for="franchise_postal_code" class="text-auth-blue"
            >Postal Code (franchise)</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Input
              id="franchise_postal_code"
              type="number"
              name="franchise_postal_code"
              required
              autofocus
              autocomplete="franchise_postal_code"
              placeholder="2009"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.franchise_postal_code" />
        </div>

        <!-- Franchise Address -->
        <div class="grid gap-2">
          <Label for="franchise_address" class="text-auth-blue"
            >Franchise Address</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <Building2 class="h-5 w-5 text-white" />
            </div>
            <Input
              id="franchise_address"
              type="text"
              name="franchise_address"
              required
              autocomplete="address"
              placeholder="123 St. / Building Name"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.address" />
        </div>
      </div>

      <!-- Step 4: Preferences -->
      <div v-show="currentStep === 4" class="space-y-4">
        <!-- Payment Option -->
        <div class="grid gap-2">
          <Label for="payout" class="text-auth-blue">Payment Option</Label>
          <div class="grid grid-cols-2 gap-2">
            <Button
              v-for="payout in paymentOptions"
              :key="payout.id"
              type="button"
              variant="default"
              @click="selectedPayout = payout.id"
              :class="[
                payout.color,
                'relative h-12 text-base font-semibold text-white hover:opacity-90',
                selectedPayout === payout.id
                  ? 'ring-2 ring-auth-blue ring-offset-2'
                  : '',
              ]"
            >
              {{ payout.label }}
            </Button>
          </div>
          <InputError :message="errors.payout" />
          <input
            type="hidden"
            name="payment_option_id"
            :value="selectedPayout"
          />
        </div>
      </div>

      <!-- Step 5: Uploads -->
      <div v-show="currentStep === 5" class="space-y-4">
        <!-- DTI Certificate -->
        <div class="grid gap-2">
          <Label for="dti_certificate" class="text-auth-blue">
            DTI/SEC Registration
          </Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <File class="h-5 w-5 text-white" />
            </div>
            <Input
              id="dti_certificate"
              type="file"
              required
              name="dti_certificate"
              autocomplete="dti_certificate"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.dti_certificate" />
        </div>

        <!-- Mayor's Permit -->
        <div class="grid gap-2">
          <Label for="mayor_permit" class="text-auth-blue">
            Mayor's Permit
          </Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <File class="h-5 w-5 text-white" />
            </div>
            <Input
              id="mayor_permit"
              type="file"
              required
              name="mayor_permit"
              autocomplete="mayor_permit"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.mayor_permit" />
        </div>

        <!-- Proof of Capital -->
        <div class="grid gap-2">
          <Label for="proof_capital" class="text-auth-blue">
            Proof of Capital or Franchise Agreement
          </Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <File class="h-5 w-5 text-white" />
            </div>
            <Input
              id="proof_capital"
              type="file"
              required
              name="proof_capital"
              autocomplete="proof_capital"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.proof_capital" />
        </div>

        <!-- Valid ID Type -->
        <div class="grid gap-2">
          <Label for="valid_id_type" class="text-auth-blue"
            >Valid ID Type</Label
          >
          <select
            id="valid_id_type"
            name="valid_id_type"
            v-model="selectedIdType"
            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-auth-blue focus-visible:ring-2 focus-visible:ring-auth-blue focus-visible:ring-offset-2 focus-visible:outline-none"
          >
            <option value="" disabled>Valid ID Type</option>
            <option
              v-for="idType in idTypes"
              :key="idType.value"
              :value="idType.value"
            >
              {{ idType.label }}
            </option>
          </select>
          <InputError :message="errors.valid_id_type" />
        </div>

        <!-- Valid ID -->
        <div class="space-y-3">
          <Label class="text-base font-semibold text-auth-blue"
            >Upload Valid ID</Label
          >

          <div class="grid grid-cols-2 gap-3">
            <!-- Front -->
            <div>
              <Label class="mb-2 block text-sm text-gray-600">Front</Label>
              <div
                class="relative flex h-24 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 transition-colors hover:bg-gray-100"
                @click="
                  !validIdFront &&
                  ($refs.licenseFrontInput as HTMLInputElement)?.click()
                "
              >
                <input
                  ref="licenseFrontInput"
                  type="file"
                  name="front_valid_id_picture"
                  accept="image/*"
                  class="hidden"
                  @change="handleFileUpload($event, validIdFront)"
                />

                <template v-if="!validIdFront">
                  <Upload class="mb-1 h-6 w-6 text-auth-blue" />
                  <span class="text-xs font-medium text-auth-blue"
                    >Front +</span
                  >
                </template>

                <template v-else>
                  <div class="flex items-center gap-2">
                    <FileText class="h-5 w-5 text-auth-blue" />
                    <span class="max-w-[100px] truncate text-xs text-gray-700">
                      {{ validIdFront.name }}
                    </span>
                  </div>
                  <button
                    type="button"
                    @click.stop="removeFile(validIdFront)"
                    class="absolute top-1 right-1 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
                  >
                    <X class="h-3 w-3" />
                  </button>
                </template>
              </div>
            </div>

            <!-- Back -->
            <div>
              <Label class="mb-2 block text-sm text-gray-600">Back</Label>
              <div
                class="relative flex h-24 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 transition-colors hover:bg-gray-100"
                @click="
                  !validIdBack &&
                  ($refs.licenseBackInput as HTMLInputElement)?.click()
                "
              >
                <input
                  ref="licenseBackInput"
                  type="file"
                  name="back_valid_id_picture"
                  accept="image/*"
                  class="hidden"
                  @change="handleFileUpload($event, validIdBack)"
                />

                <template v-if="!validIdBack">
                  <Upload class="mb-1 h-6 w-6 text-auth-blue" />
                  <span class="text-xs font-medium text-auth-blue">Back +</span>
                </template>

                <template v-else>
                  <div class="flex items-center gap-2">
                    <FileText class="h-5 w-5 text-auth-blue" />
                    <span class="max-w-[100px] truncate text-xs text-gray-700">
                      {{ validIdBack.name }}
                    </span>
                  </div>
                  <button
                    type="button"
                    @click.stop="removeFile(validIdBack)"
                    class="absolute top-1 right-1 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
                  >
                    <X class="h-3 w-3" />
                  </button>
                </template>
              </div>
            </div>
          </div>

          <!-- Valid Id number -->
          <div class="grid gap-2">
            <Label for="valid_id_number" class="text-auth-blue"
              >Valid ID Number</Label
            >
            <div
              class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
            >
              <div class="flex items-center justify-center bg-auth-blue px-3">
                <IdCard class="h-5 w-5 text-white" />
              </div>
              <Input
                id="valid_id_number"
                type="text"
                name="valid_id_number"
                required
                autofocus
                autocomplete="valid_id_number"
                placeholder="123456789"
                class="flex-1 border-0 focus-visible:ring-0"
              />
            </div>
            <InputError :message="errors.valid_id_number" />
          </div>
        </div>
      </div>

      <!-- Step 6: Security -->
      <div v-show="currentStep === 6" class="space-y-4">
        <div class="grid gap-2">
          <Label for="password" class="text-auth-blue">Password</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <Lock class="h-5 w-5 text-white" />
            </div>

            <div class="relative w-full items-center">
              <Input
                id="password"
                :type="showPassword ? 'text' : 'password'"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Password"
                class="flex-1 border-0 focus-visible:ring-0"
              />

              <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                @click="showPassword = !showPassword"
              >
                <Eye v-if="!showPassword" class="h-5 w-5" />
                <EyeOff v-else class="h-5 w-5" />
              </button>
            </div>
          </div>
          <InputError :message="errors.password" />
        </div>

        <div class="grid gap-2">
          <Label for="password_confirmation" class="text-auth-blue"
            >Confirm Password</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <Lock class="h-5 w-5 text-white" />
            </div>

            <div class="relative w-full items-center">
              <Input
                id="password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Confirm Password"
                class="flex-1 border-0 focus-visible:ring-0"
              />

              <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                <Eye v-if="!showConfirmPassword" class="h-5 w-5" />
                <EyeOff v-else class="h-5 w-5" />
              </button>
            </div>
          </div>
          <InputError :message="errors.password_confirmation" />
        </div>

        <div class="items-top flex gap-x-2">
          <Checkbox id="terms1" />
          <div class="grid gap-1.5 leading-none">
            <label
              for="terms1"
              class="text-xs leading-none font-normal text-auth-blue peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
            >
              I Agree to the Franchise Terms and Boundary Policy
            </label>
          </div>
        </div>

        <div class="items-top flex gap-x-2">
          <Checkbox id="terms2" />
          <div class="grid gap-1.5 leading-none">
            <label
              for="terms1"
              class="text-xs leading-none font-normal text-auth-blue peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
            >
              I confirm all details are true and valid
            </label>
          </div>
        </div>
      </div>

      <!-- Navigation Buttons -->
      <div class="mt-4 flex gap-3">
        <Button
          v-if="currentStep > 1"
          type="button"
          variant="outline"
          class="flex-1 cursor-pointer"
          @click="prevStep"
          :disabled="processing"
        >
          <ChevronLeft class="mr-2 h-4 w-4" />
          Previous
        </Button>

        <Button
          v-if="currentStep < totalSteps"
          type="button"
          class="flex-1 cursor-pointer bg-auth-blue text-white hover:bg-auth-blue hover:opacity-80"
          @click="nextStep"
          :disabled="!canProceed"
        >
          Next
          <ChevronRight class="ml-2 h-4 w-4" />
        </Button>

        <Button
          v-if="currentStep === totalSteps"
          type="submit"
          class="flex-1 cursor-pointer bg-brand-green hover:bg-brand-green hover:opacity-80"
          :disabled="processing"
          data-test="register-user-button"
        >
          <Spinner v-if="processing" />
          Create account
        </Button>
      </div>

      <!-- Progress Indicator -->
      <div class="mb-4">
        <!-- Progress Bar -->
        <div class="h-2 w-full rounded-full bg-gray-200">
          <div
            class="h-2 rounded-full bg-auth-blue transition-all duration-300"
            :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
          ></div>
        </div>

        <!-- Step Indicators -->
        <div class="mt-4 flex justify-between">
          <button
            v-for="step in totalSteps"
            :disabled="true"
            :key="step"
            type="button"
            @click="goToStep(step)"
            class="flex flex-col items-center gap-1"
            :class="
              step <= currentStep
                ? 'cursor-not-allowed'
                : 'cursor-not-allowed opacity-50'
            "
          >
            <div
              class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold transition-all"
              :class="
                step < currentStep
                  ? 'bg-green-500 text-white'
                  : step === currentStep
                    ? 'bg-auth-blue text-white'
                    : 'bg-gray-200 text-gray-500'
              "
            >
              <Check v-if="step < currentStep" class="h-4 w-4" />
              <span v-else>{{ step }}</span>
            </div>
            <span class="hidden text-xs text-gray-600 sm:block">
              {{ stepTitles[step].split(' ')[0] }}
            </span>
          </button>
        </div>
      </div>
    </Form>
  </AuthBase>
</template>
