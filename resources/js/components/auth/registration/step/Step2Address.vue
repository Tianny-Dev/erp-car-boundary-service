<script setup lang="ts">
import InputError from '@/components/InputError.vue';
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
import { Home, MapPin } from 'lucide-vue-next';

// Define the shape of the address object from useAddress
interface AddressData {
  regions: { code: string; name: string }[];
  provinces: { code: string; name: string }[];
  cities: { code: string; name: string }[];
  barangays: { code: string; name: string }[];
  selectedRegion: string;
  selectedProvince: string;
  selectedCity: string;
  selectedBarangay: string;
  isNcr: boolean;
  isLoadingRegions: boolean;
  isLoadingProvinces: boolean;
  isLoadingCities: boolean;
  isLoadingBarangays: boolean;
}

// Define the shapes of the dynamic props
interface FieldNames {
  region: string;
  province: string;
  city: string;
  barangay: string;
  postalCode: string;
  address: string;
}

interface Labels {
  region: string;
  province: string;
  city: string;
  barangay: string;
  postalCode: string;
  address: string;
}

defineProps<{
  addressData: AddressData;
  errors?: Record<string, string>;
  fieldNames: FieldNames;
  labels: Labels;
}>();

// No emits needed! v-model bindings below
// will directly mutate the 'addressData' object,
// which is reactive and lives in the parent.
</script>

<template>
  <!-- Region -->
  <div class="grid gap-2">
    <Label :for="fieldNames.region" class="text-auth-blue">{{
      labels.region
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <MapPin class="h-5 w-5 text-white" />
      </div>
      <Select v-model="addressData.selectedRegion" :name="fieldNames.region">
        <SelectTrigger
          class="flex-1 border-0 focus-visible:ring-0"
          :disabled="addressData.isLoadingRegions"
        >
          <SelectValue placeholder="Select Region" />
          <Spinner
            v-if="addressData.isLoadingRegions"
            class="ml-auto h-4 w-4"
          />
        </SelectTrigger>
        <SelectContent>
          <SelectItem
            v-for="r in addressData.regions"
            :key="r.code"
            :value="r.name"
          >
            {{ r.name }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>
    <InputError :message="errors?.[fieldNames.region]" />
  </div>

  <!-- Province -->
  <div
    v-if="!addressData.isNcr && addressData.provinces.length"
    class="grid gap-2"
  >
    <Label :for="fieldNames.province" class="text-auth-blue">{{
      labels.province
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <MapPin class="h-5 w-5 text-white" />
      </div>
      <Select
        v-model="addressData.selectedProvince"
        :name="fieldNames.province"
      >
        <SelectTrigger
          class="flex-1 border-0 focus-visible:ring-0"
          :disabled="
            addressData.isLoadingProvinces || !addressData.selectedRegion
          "
        >
          <SelectValue placeholder="Select Province" />
          <Spinner
            v-if="addressData.isLoadingProvinces"
            class="ml-auto h-4 w-4"
          />
        </SelectTrigger>
        <SelectContent>
          <SelectItem
            v-for="p in addressData.provinces"
            :key="p.code"
            :value="p.name"
          >
            {{ p.name }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>
    <InputError :message="errors?.[fieldNames.province]" />
  </div>

  <!-- Show "N/A" Province for NCR -->
  <div v-else-if="addressData.isNcr" class="grid gap-2">
    <Label :for="fieldNames.province" class="text-auth-blue">{{
      labels.province
    }}</Label>
    <div
      class="flex w-full max-w-sm items-center rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-gray-500"
    >
      N/A
    </div>
  </div>

  <!-- City -->
  <div
    class="grid gap-2"
    v-if="addressData.cities.length || addressData.isLoadingCities"
  >
    <Label :for="fieldNames.city" class="text-auth-blue">{{
      labels.city
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <MapPin class="h-5 w-5 text-white" />
      </div>
      <Select v-model="addressData.selectedCity" :name="fieldNames.city">
        <SelectTrigger
          class="flex-1 border-0 focus-visible:ring-0"
          :disabled="addressData.isLoadingCities"
        >
          <SelectValue placeholder="Select City / Municipality" />
          <Spinner v-if="addressData.isLoadingCities" class="ml-auto h-4 w-4" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem
            v-for="c in addressData.cities"
            :key="c.code"
            :value="c.name"
          >
            {{ c.name }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>
    <InputError :message="errors?.[fieldNames.city]" />
  </div>

  <!-- Barangay -->
  <div
    class="grid gap-2"
    v-if="addressData.barangays.length || addressData.isLoadingBarangays"
  >
    <Label :for="fieldNames.barangay" class="text-auth-blue">{{
      labels.barangay
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <MapPin class="h-5 w-5 text-white" />
      </div>
      <Select
        v-model="addressData.selectedBarangay"
        :name="fieldNames.barangay"
      >
        <SelectTrigger
          class="flex-1 border-0 focus-visible:ring-0"
          :disabled="addressData.isLoadingBarangays"
        >
          <SelectValue placeholder="Select Barangay" />
          <Spinner
            v-if="addressData.isLoadingBarangays"
            class="ml-auto h-4 w-4"
          />
        </SelectTrigger>
        <SelectContent>
          <SelectItem
            v-for="b in addressData.barangays"
            :key="b.code"
            :value="b.name"
          >
            {{ b.name }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>
    <InputError :message="errors?.[fieldNames.barangay]" />
  </div>

  <!-- Postal Code -->
  <div class="grid gap-2">
    <Label :for="fieldNames.postalCode" class="text-auth-blue">{{
      labels.postalCode
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <MapPin class="h-5 w-5 text-white" />
      </div>
      <Input
        :id="fieldNames.postalCode"
        type="number"
        :name="fieldNames.postalCode"
        required
        :autocomplete="fieldNames.postalCode"
        placeholder="2009"
        class="flex-1 border-0 focus-visible:ring-0"
      />
    </div>
    <InputError :message="errors?.[fieldNames.postalCode]" />
  </div>

  <!-- Address -->
  <div class="grid gap-2">
    <Label :for="fieldNames.address" class="text-auth-blue">{{
      labels.address
    }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <Home class="h-5 w-5 text-white" />
      </div>
      <Input
        :id="fieldNames.address"
        type="text"
        :name="fieldNames.address"
        required
        autocomplete="address"
        placeholder="123 St. / Building Name"
        class="flex-1 border-0 focus-visible:ring-0"
      />
    </div>
    <InputError :message="errors?.[fieldNames.address]" />
  </div>
</template>
