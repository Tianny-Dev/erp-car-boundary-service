<script setup lang="ts">
import DriverRegistrationForm from '@/components/auth/registration/DriverRegistrationForm.vue';
import PassengerRegisrationForm from '@/components/auth/registration/PassengerRegisrationForm.vue';

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
</script>

<template>
  <DriverRegistrationForm
    v-if="userType.name === 'driver'"
    :gender-options="genderOptions"
    :payment-options="paymentOptions"
    :user-type="userType"
  />

  <PassengerRegisrationForm
    v-else-if="userType.name === 'passenger'"
    :gender-options="genderOptions"
    :payment-options="paymentOptions"
    :user-type="userType"
    :preferred-languages="preferredLanguages"
    :accessibility-options="accessibilityOptions"
  />
</template>
