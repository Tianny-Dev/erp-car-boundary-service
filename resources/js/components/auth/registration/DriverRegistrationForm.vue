<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
  File,
  Home,
  IdCard,
  Lock,
  Mail,
  PhoneCall,
  User,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineProps<{
  genderOptions: { value: string; label: string }[];
}>();

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const currentStep = ref(1);
const totalSteps = 5;
const birthday = ref('');
const selectedGender = ref('');

const selectedShift = ref('Morning');
const selectedPayout = ref('cash');
const shifts = ['Morning', 'Evening', 'Night'];
const payouts = [
  { id: 'cash', label: 'Cash', color: 'bg-red-500' },
  { id: 'bank', label: 'Bank Transfer', color: 'bg-yellow-400' },
  { id: 'gcash', label: 'GCash', color: 'bg-blue-500' },
  { id: 'maya', label: 'Maya', color: 'bg-blue-400' },
];

// File uploads
const driverLicenseFront = ref<File | null>(null);
const driverLicenseBack = ref<File | null>(null);

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
  1: 'Personal Information',
  2: 'Account Details',
  3: 'Preference',
  4: 'Work Details',
  5: 'Account Security',
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
</script>

<template>
  <AuthBase
    text-overlay="PLEASE FILL OUT THE FORM TO REGISTER AS A DRIVER UNDER ERP SYSTEM FOR CAR BOUNDARY SERVICE -PHILIPPINES. YOUR FRANCHISE WILL VERIFY YOUR ACCOUNT."
    title-registration="Create an account"
    :go-back-home-button="true"
  >
    <Head title="Register" />

    <Form
      v-bind="store.form()"
      :reset-on-success="['password', 'password_confirmation']"
      v-slot="{ errors, processing }"
      class="flex flex-col gap-6"
    >
      <!-- Step Title -->
      <h1 class="text-center text-2xl font-black text-auth-blue">
        {{ stepTitles[currentStep] }}
      </h1>

      <!-- Step 1: Personal Information -->
      <div v-if="currentStep === 1" class="space-y-4">
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

      <!-- Step 2: Account Details -->
      <div v-if="currentStep === 2" class="space-y-4">
        <div class="grid gap-2">
          <Label for="phone_number" class="text-auth-blue">Phone Number</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <PhoneCall class="h-5 w-5 text-white" />
            </div>
            <Input
              id="phone_number"
              type="tel"
              name="phone_number"
              required
              autocomplete="phone"
              placeholder="639123456789"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.phone_number" />
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
          <Label for="licence_number" class="text-auth-blue"
            >Driver's License Number</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <IdCard class="h-5 w-5 text-white" />
            </div>
            <Input
              id="licence_number"
              type="text"
              name="licence_number"
              required
              autocomplete="licence_number"
              placeholder="Driver's License Number"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.licence_number" />
        </div>

        <div class="grid gap-2">
          <Label for="license_expiry_date" class="text-auth-blue"
            >License Expiry Date</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <Calendar class="h-5 w-5 text-white" />
            </div>
            <Input
              id="license_expiry_date"
              type="date"
              name="license_expiry_date"
              v-model="birthday"
              :max="new Date().toISOString().split('T')[0]"
              autocomplete="license_expiry_date"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.license_expiry_date" />
        </div>
      </div>

      <!-- Step 3: Contact Information -->
      <div v-if="currentStep === 3" class="space-y-4">
        <div class="grid gap-2">
          <Label for="phone" class="text-auth-blue">Phone Number</Label>
          <div class="flex gap-2">
            <Button
              v-for="shift in shifts"
              :key="shift"
              :variant="selectedShift === shift ? 'default' : 'outline'"
              @click="selectedShift = shift"
              :class="[
                'flex-1',
                selectedShift === shift
                  ? 'bg-auth-blue hover:bg-blue-700'
                  : 'border-gray-300 hover:bg-gray-50',
              ]"
            >
              {{ shift }}
            </Button>
          </div>
          <InputError :message="errors.phone" />
        </div>

        <div class="grid gap-2">
          <Label for="address" class="text-auth-blue">Address</Label>
          <div class="grid grid-cols-2 gap-2">
            <Button
              v-for="payout in payouts"
              :key="payout.id"
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
          <InputError :message="errors.address" />
        </div>
      </div>

      <!-- Step 4: Additional Details -->
      <div v-if="currentStep === 4" class="space-y-4">
        <!-- Driver's License -->
        <div class="space-y-3">
          <Label class="text-base font-semibold text-auth-blue"
            >Driver's License</Label
          >

          <div class="grid grid-cols-2 gap-3">
            <!-- Front -->
            <div>
              <Label class="mb-2 block text-sm text-gray-600">Front</Label>
              <div
                class="relative flex h-24 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 transition-colors hover:bg-gray-100"
                @click="
                  !driverLicenseFront &&
                  ($refs.licenseFrontInput as HTMLInputElement)?.click()
                "
              >
                <input
                  ref="licenseFrontInput"
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="handleFileUpload($event, driverLicenseFront)"
                />

                <template v-if="!driverLicenseFront">
                  <Upload class="mb-1 h-6 w-6 text-auth-blue" />
                  <span class="text-xs font-medium text-auth-blue"
                    >Front +</span
                  >
                </template>

                <template v-else>
                  <div class="flex items-center gap-2">
                    <FileText class="h-5 w-5 text-auth-blue" />
                    <span class="max-w-[100px] truncate text-xs text-gray-700">
                      {{ driverLicenseFront.name }}
                    </span>
                  </div>
                  <button
                    type="button"
                    @click.stop="removeFile(driverLicenseFront)"
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
                  !driverLicenseBack &&
                  ($refs.licenseBackInput as HTMLInputElement)?.click()
                "
              >
                <input
                  ref="licenseBackInput"
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="handleFileUpload($event, driverLicenseBack)"
                />

                <template v-if="!driverLicenseBack">
                  <Upload class="mb-1 h-6 w-6 text-auth-blue" />
                  <span class="text-xs font-medium text-auth-blue">Back +</span>
                </template>

                <template v-else>
                  <div class="flex items-center gap-2">
                    <FileText class="h-5 w-5 text-auth-blue" />
                    <span class="max-w-[100px] truncate text-xs text-gray-700">
                      {{ driverLicenseBack.name }}
                    </span>
                  </div>
                  <button
                    type="button"
                    @click.stop="removeFile(driverLicenseBack)"
                    class="absolute top-1 right-1 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
                  >
                    <X class="h-3 w-3" />
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- NBI or Police Clearance -->
        <div class="grid gap-2">
          <Label for="clearance" class="text-auth-blue"
            >NBI or Police Clearance</Label
          >
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <File class="h-5 w-5 text-white" />
            </div>
            <Input
              id="clearance"
              type="file"
              name="clearance"
              required
              autocomplete="nbi"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.clearance" />
        </div>

        <!-- 1x1 Photo / Selfie -->
        <div class="grid gap-2">
          <Label for="photo" class="text-auth-blue">1x1 Photo/Selfie</Label>
          <div
            class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
          >
            <div class="flex items-center justify-center bg-auth-blue px-3">
              <File class="h-5 w-5 text-white" />
            </div>
            <Input
              id="photo"
              type="file"
              name="photo"
              required
              autocomplete="nbi"
              class="flex-1 border-0 focus-visible:ring-0"
            />
          </div>
          <InputError :message="errors.photo" />
        </div>
      </div>

      <!-- Step 5: Review -->
      <div v-if="currentStep === 5" class="space-y-4">
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

        <!-- <div class="space-y-4 rounded-lg bg-gray-50 p-4">
          <h3 class="mb-3 font-semibold text-auth-blue">
            Please review your information
          </h3>

          <div class="space-y-3">
            <div class="flex items-start gap-3 border-b border-gray-200 pb-3">
              <User class="mt-0.5 h-5 w-5 flex-shrink-0 text-auth-blue" />
              <div class="flex-1">
                <p class="text-xs font-medium text-gray-500 uppercase">
                  Personal
                </p>
                <p class="mt-1 text-sm text-gray-900">
                  {{ (store.form() as any).name || 'Not provided' }}
                </p>
                <p class="text-sm text-gray-600">
                  @{{ (store.form() as any).username || 'username' }}
                </p>
                <p v-if="birthday" class="text-sm text-gray-600">
                  Born: {{ new Date(birthday).toLocaleDateString() }}
                  <span class="font-medium"
                    >({{ calculatedAge }} years old)</span
                  >
                </p>
                <p v-if="selectedGender" class="text-sm text-gray-600">
                  Gender:
                  {{
                    genderOptions.find((g) => g.value === selectedGender)?.label
                  }}
                </p>
              </div>
            </div>

            <div class="flex items-start gap-3 border-b border-gray-200 pb-3">
              <Mail class="mt-0.5 h-5 w-5 flex-shrink-0 text-auth-blue" />
              <div class="flex-1">
                <p class="text-xs font-medium text-gray-500 uppercase">
                  Account
                </p>
                <p class="mt-1 text-sm text-gray-900">
                  {{ (store.form() as any).email || 'Not provided' }}
                </p>
                <p class="text-sm text-gray-600">Password: ••••••••</p>
              </div>
            </div>

            <div class="flex items-start gap-3 border-b border-gray-200 pb-3">
              <MapPin class="mt-0.5 h-5 w-5 flex-shrink-0 text-auth-blue" />
              <div class="flex-1">
                <p class="text-xs font-medium text-gray-500 uppercase">
                  Contact
                </p>
                <p class="mt-1 text-sm text-gray-900">
                  {{ (store.form() as any).phone || 'Not provided' }}
                </p>
                <p class="text-sm text-gray-600">
                  {{ (store.form() as any).address || 'No address' }}
                </p>
                <p class="text-sm text-gray-600">
                  {{ (store.form() as any).city || '' }}
                  {{ (store.form() as any).postal_code || '' }}
                </p>
              </div>
            </div>

            <div class="flex items-start gap-3">
              <Building class="mt-0.5 h-5 w-5 flex-shrink-0 text-auth-blue" />
              <div class="flex-1">
                <p class="text-xs font-medium text-gray-500 uppercase">
                  Professional
                </p>
                <p class="mt-1 text-sm text-gray-900">
                  {{ (store.form() as any).job_title || 'Not specified' }}
                </p>
                <p class="text-sm text-gray-600">
                  {{ (store.form() as any).company || 'No company' }}
                </p>
              </div>
            </div>
          </div>
        </div> -->
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
