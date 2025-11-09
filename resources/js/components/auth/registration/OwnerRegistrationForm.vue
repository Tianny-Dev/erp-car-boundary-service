<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { useAddress } from '@/composables/useAddress';
import AuthBase from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';
import { Check, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import Step1Personal from './step/Step1Personal.vue';
import Step2Address from './step/Step2Address.vue';
import Step3Preferences from './step/Step3Preferences.vue';
import Step4Account from './step/Step4Account.vue';
import Step5Uploads from './step/Step5Uploads.vue';
import Step6Security from './step/Step6Security.vue';

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

const currentStep = ref(1);
const totalSteps = 7;

const stepTitles: Record<number, string> = {
  1: 'Basic Information',
  2: 'Home Address',
  3: 'Franchise Details',
  4: 'Preferences',
  5: 'Identity Verification',
  6: 'Documents to Upload',
  7: 'Account Security',
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

// --- Step 1 State & Config ---
const selectedGender = ref('');
const personalStep1Show = {
  birthday: false,
  franchiseName: false,
};

// --- Step 2 (Home Address) State & Config ---
const homeAddress = reactive(useAddress()); // Use reactive
const homeAddressFields = {
  region: 'home_region',
  province: 'home_province',
  city: 'home_city',
  barangay: 'home_barangay',
  postalCode: 'home_postal_code',
  address: 'home_address',
};
const homeAddressLabels = {
  region: 'Region (home)',
  province: 'Province (home)',
  city: 'City / Municipality (home)',
  barangay: 'Barangay (home)',
  postalCode: 'Postal Code (home)',
  address: 'Home Address',
};

// --- Step 3 (Franchise Address) State & Config ---
const franchiseAddress = reactive(useAddress()); // Use reactive
const franchiseAddressFields = {
  region: 'franchise_region',
  province: 'franchise_province',
  city: 'franchise_city',
  barangay: 'franchise_barangay',
  postalCode: 'franchise_postal_code',
  address: 'franchise_address',
};
const franchiseAddressLabels = {
  region: 'Region (franchise)',
  province: 'Province (franchise)',
  city: 'City / Municipality (franchise)',
  barangay: 'Barangay (franchise)',
  postalCode: 'Postal Code (franchise)',
  address: 'Franchise Address',
};
const personalStep3Show = {
  name: false,
  phone: false,
  email: false,
  gender: false,
  birthday: false,
};

// --- Step 4 (Preferences) State & Config ---
const selectedPayout = ref('');
const preferencesStep3Show = {
  shift: false,
  language: false,
  accessibility: false,
};

// --- Step 5 (Identity Verification) State & Config ---
const selectedIdType = ref('');
const validIdFront = ref<File | null>(null);
const validIdBack = ref<File | null>(null);
const identityStep5Show = {
  licenseNumber: false,
  licenseExpiry: false,
  expertise: false,
  yearExperience: false,
};

// --- Step 6 (Documents to Upload) State & Config ---
const documentsStep6Show = {
  selfiePicture: false,
  prcCertificate: false,
  professionalLicense: false,
  cvAttachment: false,
};

// --- Step 7 (Account Security) State & Config ---
const securityStep6Labels = {
  terms1: 'I Agree to the Franchise Terms and Boundary Policy',
  terms2: 'I confirm all details are true and valid',
};
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
        <Step1Personal
          :errors="errors"
          :gender-options="genderOptions"
          :show-fields="personalStep1Show"
          v-model:selectedGender="selectedGender"
        />
      </div>

      <!-- Step 2: Home Address -->
      <div v-show="currentStep === 2" class="space-y-4">
        <Step2Address
          :address-data="homeAddress"
          :errors="errors"
          :field-names="homeAddressFields"
          :labels="homeAddressLabels"
        />
      </div>

      <!-- Step 3: Franchise Address -->
      <div v-show="currentStep === 3" class="space-y-4">
        <Step1Personal :errors="errors" :show-fields="personalStep3Show" />
        <Step2Address
          :address-data="franchiseAddress"
          :errors="errors"
          :field-names="franchiseAddressFields"
          :labels="franchiseAddressLabels"
        />
      </div>

      <!-- Step 4: Preferences -->
      <div v-show="currentStep === 4" class="space-y-4">
        <Step3Preferences
          :errors="errors"
          :payment-options="paymentOptions"
          :show-fields="preferencesStep3Show"
          v-model:selectedPayout="selectedPayout"
        />
      </div>

      <!-- Step 5: Identification -->
      <div v-show="currentStep === 5" class="space-y-4">
        <Step4Account
          :errors="errors"
          :id-types="idTypes"
          :show-fields="identityStep5Show"
          v-model:selectedIdType="selectedIdType"
          v-model:validIdFront="validIdFront"
          v-model:validIdBack="validIdBack"
        />
      </div>

      <!-- Step 6: Uploads -->
      <div v-show="currentStep === 6" class="space-y-4">
        <Step5Uploads :errors="errors" :show-fields="documentsStep6Show" />
      </div>

      <!-- Step 7: Security -->
      <div v-show="currentStep === 7" class="space-y-4">
        <Step6Security :errors="errors" :labels="securityStep6Labels" />
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
          </button>
        </div>
      </div>
    </Form>
  </AuthBase>
</template>
