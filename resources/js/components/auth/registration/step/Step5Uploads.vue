<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { File } from 'lucide-vue-next';
import { computed } from 'vue';
import FileInput from './FileInput.vue';

// --- TYPES ---
interface FieldNames {
  nbiClearance: string;
  selfiePicture: string;
  prcCertificate: string;
  professionalLicense: string;
  cvAttachment: string;
  dtiCertificate: string;
  mayorPermit: string;
  proofOfCapital: string;
}
interface Labels {
  nbiClearance: string;
  selfiePicture: string;
  prcCertificate: string;
  professionalLicense: string;
  cvAttachment: string;
  dtiCertificate: string;
  mayorPermit: string;
  proofOfCapital: string;
}
interface ShowFields {
  nbiClearance: boolean;
  selfiePicture: boolean;
  prcCertificate: boolean;
  professionalLicense: boolean;
  cvAttachment: boolean;
  dtiCertificate: boolean;
  mayorPermit: boolean;
  proofOfCapital: boolean;
}

// --- PROPS ---
const props = defineProps<{
  errors?: Record<string, string>;
  // Customization props (all optional)
  fieldNames?: Partial<FieldNames>;
  labels?: Partial<Labels>;
  showFields?: Partial<ShowFields>;
}>();

// --- DEFAULTS ---
const defaultFieldNames: FieldNames = {
  nbiClearance: 'nbi_clearance',
  selfiePicture: 'selfie_picture',
  prcCertificate: 'certificate_prc_no',
  professionalLicense: 'professional_license',
  cvAttachment: 'cv_attachment',
  dtiCertificate: 'dti_certificate',
  mayorPermit: 'mayor_permit',
  proofOfCapital: 'proof_capital',
};
const defaultLabels: Labels = {
  nbiClearance: 'NBI or Police Clearance',
  selfiePicture: '1x1 Photo/Selfie',
  prcCertificate: 'Certification or PRC Number (if applicable)',
  professionalLicense: 'Professional Licence / Certificate (if applicable)',
  cvAttachment: 'Photo or Resume',
  dtiCertificate: 'DTI/SEC Registration',
  mayorPermit: "Mayor's Permit",
  proofOfCapital: 'Proof of Capital or Franchise Agreement',
};
const defaultShowFields: ShowFields = {
  nbiClearance: true,
  selfiePicture: true,
  prcCertificate: true,
  professionalLicense: true,
  cvAttachment: true,
  dtiCertificate: true,
  mayorPermit: true,
  proofOfCapital: true,
};

// --- MERGED COMPUTEDS ---
const fields = computed(() => ({ ...defaultFieldNames, ...props.fieldNames }));
const labels = computed(() => ({ ...defaultLabels, ...props.labels }));
const show = computed(() => ({ ...defaultShowFields, ...props.showFields }));
</script>

<template>
  <FileInput
    v-if="show.nbiClearance"
    :id="fields.nbiClearance"
    :name="fields.nbiClearance"
    :label="labels.nbiClearance"
    :required="true"
    :errorMsg="errors?.[fields.nbiClearance]"
  />

  <div v-if="show.selfiePicture" class="grid gap-2">
    <Label :for="fields.selfiePicture" class="text-auth-blue">{{
      labels.selfiePicture
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <File class="h-5 w-5 text-white" />
      </div>
      <Input
        :id="fields.selfiePicture"
        type="file"
        :name="fields.selfiePicture"
        required
        class="flex-1 border-0 focus-visible:ring-0"
      />
    </div>
    <InputError :message="errors?.[fields.selfiePicture]" />
  </div>

  <div v-if="show.prcCertificate" class="grid gap-2">
    <Label :for="fields.prcCertificate" class="text-auth-blue">{{
      labels.prcCertificate
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <File class="h-5 w-5 text-white" />
      </div>
      <Input
        :id="fields.prcCertificate"
        type="file"
        :name="fields.prcCertificate"
        class="flex-1 border-0 focus-visible:ring-0"
      />
    </div>
    <InputError :message="errors?.[fields.prcCertificate]" />
  </div>

  <div v-if="show.professionalLicense" class="grid gap-2">
    <Label :for="fields.professionalLicense" class="text-auth-blue">{{
      labels.professionalLicense
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <File class="h-5 w-5 text-white" />
      </div>
      <Input
        :id="fields.professionalLicense"
        type="file"
        :name="fields.professionalLicense"
        class="flex-1 border-0 focus-visible:ring-0"
      />
    </div>
    <InputError :message="errors?.[fields.professionalLicense]" />
  </div>

  <div v-if="show.cvAttachment" class="grid gap-2">
    <Label :for="fields.cvAttachment" class="text-auth-blue">{{
      labels.cvAttachment
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <File class="h-5 w-5 text-white" />
      </div>
      <Input
        :id="fields.cvAttachment"
        type="file"
        :name="fields.cvAttachment"
        class="flex-1 border-0 focus-visible:ring-0"
      />
    </div>
    <InputError :message="errors?.[fields.cvAttachment]" />
  </div>

  <div v-if="show.dtiCertificate" class="grid gap-2">
    <Label :for="fields.dtiCertificate" class="text-auth-blue">{{
      labels.dtiCertificate
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <File class="h-5 w-5 text-white" />
      </div>
      <Input
        :id="fields.dtiCertificate"
        type="file"
        :name="fields.dtiCertificate"
        required
        class="flex-1 border-0 focus-visible:ring-0"
      />
    </div>
    <InputError :message="errors?.[fields.dtiCertificate]" />
  </div>

  <div v-if="show.mayorPermit" class="grid gap-2">
    <Label :for="fields.mayorPermit" class="text-auth-blue">{{
      labels.mayorPermit
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <File class="h-5 w-5 text-white" />
      </div>
      <Input
        :id="fields.mayorPermit"
        type="file"
        :name="fields.mayorPermit"
        required
        class="flex-1 border-0 focus-visible:ring-0"
      />
    </div>
    <InputError :message="errors?.[fields.mayorPermit]" />
  </div>

  <div v-if="show.proofOfCapital" class="grid gap-2">
    <Label :for="fields.proofOfCapital" class="text-auth-blue">{{
      labels.proofOfCapital
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <File class="h-5 w-5 text-white" />
      </div>
      <Input
        :id="fields.proofOfCapital"
        type="file"
        :name="fields.proofOfCapital"
        required
        class="flex-1 border-0 focus-visible:ring-0"
      />
    </div>
    <InputError :message="errors?.[fields.proofOfCapital]" />
  </div>
</template>
