<script setup lang="ts">
import DriverRegistrationForm from '@/components/auth/registration/DriverRegistrationForm.vue';

defineProps<{
  genderOptions: { value: string; label: string }[];
}>();
</script>

<template>
  <DriverRegistrationForm :gender-options="genderOptions" />
</template>
