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
import AuthBase from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';
import {
  Calendar,
  ChevronLeft,
  ChevronRight,
  Eye,
  EyeOff,
  Home,
  Lock,
  Mail,
  MapPin,
  PhoneCall,
  User,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

defineProps<{
  genderOptions: { value: string; label: string }[];
  preferredLanguages: { value: string; label: string }[];
  accessibilityOptions: { value: string; label: string }[];
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
const totalSteps = 4;
const birthday = ref('');
const selectedGender = ref('');

const selectedLanguage = ref('');
const selectedAccessibilityOptions = ref('');
const selectedPayout = ref('cash');

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
  3: 'Preferences',
  4: 'Account Security',
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

// State
const regions = ref<any[]>([]);
const provinces = ref<any[]>([]);
const cities = ref<any[]>([]);
const barangays = ref<any[]>([]);

const selectedRegion = ref('');
const selectedProvince = ref('');
const selectedCity = ref('');
const selectedBarangay = ref('');

const isNcr = computed(() => {
  const region = regions.value.find((r) => r.name === selectedRegion.value);
  return region?.code === '130000000' || region?.name.includes('NCR');
});

async function fetchRegions() {
  const res = await fetch('https://psgc.gitlab.io/api/regions/');
  regions.value = await res.json();
}

async function fetchProvinces(regionCode: string) {
  const res = await fetch(
    `https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`,
  );
  provinces.value = await res.json();
}

async function fetchCities(provinceOrRegionCode: string, isNcr = false) {
  const url = isNcr
    ? `https://psgc.gitlab.io/api/regions/${provinceOrRegionCode}/cities-municipalities/`
    : `https://psgc.gitlab.io/api/provinces/${provinceOrRegionCode}/cities-municipalities/`;

  const res = await fetch(url);
  cities.value = await res.json();
}

async function fetchBarangays(cityCode: string) {
  const res = await fetch(
    `https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`,
  );
  barangays.value = await res.json();
}

watch(selectedRegion, async (name) => {
  const region = regions.value.find((r) => r.name === name);
  selectedProvince.value = '';
  selectedCity.value = '';
  selectedBarangay.value = '';
  provinces.value = [];
  cities.value = [];
  barangays.value = [];

  if (!region) return;

  // NCR → fetch cities directly (no provinces)
  if (region.code === '130000000' || region.name.includes('NCR')) {
    await fetchCities(region.code, true);
  } else {
    await fetchProvinces(region.code);
  }
});

watch(selectedProvince, async (name) => {
  if (isNcr.value) return; // skip province watch when NCR
  const province = provinces.value.find((p) => p.name === name);
  selectedCity.value = '';
  selectedBarangay.value = '';
  cities.value = [];
  barangays.value = [];
  if (province) await fetchCities(province.code);
});

watch(selectedCity, async (name) => {
  const city = cities.value.find((c) => c.name === name);
  selectedBarangay.value = '';
  barangays.value = [];
  if (city) await fetchBarangays(city.code);
});

onMounted(fetchRegions);
</script>

<template>
  <AuthBase
    text-overlay="SIGN UP AND ENJOY FAST, SAFE, AND ECO-FRIENDLY RIDES WITH ERP E-TAXI."
    title-registration="Passenger Registration"
    :go-back-home-button="true"
    :user-type-name="userType.name"
  >
    <Head title="Passenger Registration" />

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

        <div class="flex flex-wrap items-end gap-3">
          <!-- Date of Birth -->
          <div class="flex min-w-[200px] flex-1 flex-col">
            <Label for="birthday" class="mb-1 text-auth-blue"
              >Date of Birth</Label
            >
            <div class="flex overflow-hidden rounded-md border border-gray-300">
              <div class="flex items-center justify-center bg-auth-blue px-3">
                <Calendar class="h-5 w-5 text-white" />
              </div>
              <Input
                id="birthday"
                type="date"
                name="birthday"
                v-model="birthday"
                :max="new Date().toISOString().split('T')[0]"
                autocomplete="bday"
                class="flex-1 border-0 focus-visible:ring-0"
              />
            </div>
            <InputError :message="errors.birthday" />
          </div>

          <!-- Age -->
          <div class="flex w-24 flex-col">
            <Label for="age" class="mb-1 text-auth-blue">Age</Label>
            <div
              class="flex h-10 w-full items-center justify-center rounded-md border border-gray-300 bg-gray-50 text-lg font-bold text-auth-blue"
            >
              {{ calculatedAge !== null ? calculatedAge : '00' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2: Account Details -->
      <div v-show="currentStep === 2" class="space-y-4">
        <!-- Region -->
        <div class="grid gap-2">
          <Label for="region" class="text-auth-blue">Region</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Select v-model="selectedRegion" name="region">
              <SelectTrigger class="flex-1 border-0 focus-visible:ring-0">
                <SelectValue placeholder="Select Region" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="r in regions" :key="r.code" :value="r.name">
                  {{ r.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <InputError :message="errors?.region" />
        </div>

        <!-- Province Dropdown for Non-NCR -->
        <div v-if="!isNcr && provinces.length" class="grid gap-2">
          <Label for="province" class="text-auth-blue">Province</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Select v-model="selectedProvince" name="province">
              <SelectTrigger class="flex-1 border-0 focus-visible:ring-0">
                <SelectValue placeholder="Select Province" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="p in provinces"
                  :key="p.code"
                  :value="p.name"
                >
                  {{ p.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <InputError :message="errors?.province" />
        </div>

        <!-- Show "N/A" Province for NCR -->
        <div v-else-if="isNcr" class="grid gap-2">
          <Label for="province" class="text-auth-blue">Province</Label>
          <div
            class="flex w-full max-w-sm items-center rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-gray-500"
          >
            N/A
          </div>
        </div>

        <!-- City / Municipality -->
        <div class="grid gap-2" v-if="cities.length">
          <Label for="city" class="text-auth-blue">City / Municipality</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Select v-model="selectedCity" name="city">
              <SelectTrigger class="flex-1 border-0 focus-visible:ring-0">
                <SelectValue placeholder="Select City / Municipality" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="c in cities" :key="c.code" :value="c.name">
                  {{ c.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <InputError :message="errors?.city" />
        </div>

        <!-- Barangay -->
        <div class="grid gap-2" v-if="barangays.length">
          <Label for="barangay" class="text-auth-blue">Barangay</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <MapPin class="h-5 w-5 text-white" />
            </div>
            <Select v-model="selectedBarangay" name="barangay">
              <SelectTrigger class="flex-1 border-0 focus-visible:ring-0">
                <SelectValue placeholder="Select Barangay" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="b in barangays"
                  :key="b.code"
                  :value="b.name"
                >
                  {{ b.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <InputError :message="errors?.barangay" />
        </div>

        <div class="grid gap-2">
          <Label for="address" class="text-auth-blue">Home Address</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <Home class="h-5 w-5 text-white" />
            </div>
            <Input
              id="address"
              type="text"
              name="address"
              required
              autocomplete="address"
              placeholder="123 St. Barangay City"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.address" />
        </div>
      </div>

      <!-- Step 3: Preferences -->
      <div v-show="currentStep === 3" class="space-y-4">
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

        <!-- Preferred Language -->
        <div class="grid gap-2">
          <Label for="preferred-language" class="text-auth-blue">
            Preferred Language
          </Label>
          <div class="flex gap-2">
            <Button
              v-for="language in preferredLanguages"
              :key="language.value"
              type="button"
              :variant="
                selectedLanguage === language.value ? 'default' : 'outline'
              "
              @click="selectedLanguage = language.value"
              :class="[
                'flex-1',
                selectedLanguage === language.value
                  ? 'bg-auth-blue text-white hover:bg-blue-700'
                  : 'border-gray-300 bg-sky-100 text-gray-700 hover:bg-gray-50',
              ]"
            >
              {{ language.label }}
            </Button>
          </div>
          <InputError :message="errors.language" />
          <input
            type="hidden"
            name="preferred_language"
            :value="selectedLanguage"
          />
        </div>

        <!-- Accessibility Options -->
        <div class="grid gap-2">
          <Label for="accessibility-option" class="text-auth-blue">
            Accessibility Options
          </Label>
          <div class="flex flex-col gap-2">
            <Button
              v-for="option in accessibilityOptions"
              :key="option.value"
              type="button"
              :variant="
                selectedAccessibilityOptions === option.value
                  ? 'default'
                  : 'outline'
              "
              @click="selectedAccessibilityOptions = option.value"
              :class="[
                'flex-1',
                selectedAccessibilityOptions === option.value
                  ? 'bg-auth-blue text-white hover:bg-blue-700'
                  : 'border-gray-300 bg-sky-100 text-gray-700 hover:bg-gray-50',
              ]"
            >
              {{ option.label }}
            </Button>
          </div>
          <InputError :message="errors.accessibility_option" />
          <input
            type="hidden"
            name="accessibility_option"
            :value="selectedAccessibilityOptions"
          />
        </div>
      </div>

      <!-- Step 4: Security -->
      <div v-show="currentStep === 4" class="space-y-4">
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
              I Agree to the Driver Terms and Code of Conduct
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
              I consent to GPS tracking during active trips
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
        <!-- <div class="mb-2 flex items-center justify-between">
          <span class="text-sm font-medium text-auth-blue">
            Step {{ currentStep }} of {{ totalSteps }}
          </span>
          <span class="text-sm text-gray-500">
            {{ Math.round((currentStep / totalSteps) * 100) }}% Complete
          </span>
        </div> -->

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
