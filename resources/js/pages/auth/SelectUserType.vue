<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

// Props type
interface UserType {
  encrypted_id: string;
  name: string;
}

const props = defineProps<{
  userTypes: UserType[];
}>();
</script>

<template>
  <div class="mx-auto max-w-md space-y-8 p-6">
    <h1 class="text-2xl font-bold">What do you want to register?</h1>

    <div class="space-y-4">
      <div v-for="type in props.userTypes" :key="type.encrypted_id">
        <Link :href="`/register/${type.encrypted_id}`">
          <div
            class="flex cursor-pointer items-center space-x-3 rounded-md border p-4 hover:bg-gray-100"
          >
            <span class="text-lg font-medium uppercase">{{ type.name }}</span>
          </div>
        </Link>

        <!-- Conditionally show "Apply a Franchised" for type.name === 'Owner' -->
        <div v-if="type.name === 'Owner'" class="mt-2 text-sm text-gray-600">
          Apply a Franchised
        </div>
      </div>
    </div>
  </div>
</template>
