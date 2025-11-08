<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { File } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
  id: string;
  name: string;
  label: string;
  required?: boolean;
  errorMsg?: string;
}>();

// track selected file
const selectedFile = ref<File | null>(null);

function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    selectedFile.value = target.files[0];
  } else {
    selectedFile.value = null;
  }
}
</script>

<template>
  <div class="grid gap-2">
    <Label :for="id" class="text-auth-blue">{{ props.label }}</Label>
    <div
      class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
    >
      <div class="flex items-center justify-center bg-auth-blue px-3">
        <File class="h-5 w-5 text-white" />
      </div>

      <!-- hidden native input -->
      <input
        :id="id"
        type="file"
        :name="name"
        :required="required"
        class="hidden"
        @change="handleFileChange"
      />

      <!-- custom trigger -->
      <label
        :for="id"
        class="flex-1 cursor-pointer truncate border-0 p-2 text-sm font-semibold text-auth-blue hover:bg-blue-50"
      >
        {{ selectedFile ? selectedFile.name : 'Upload file' }}
      </label>
    </div>
    <InputError :message="errorMsg" />
  </div>
</template>
