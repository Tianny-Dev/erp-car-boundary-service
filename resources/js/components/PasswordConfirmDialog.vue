<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
  open: boolean;
  title?: string;
  description?: string;
  processing?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Confirm Password',
  description: 'Please enter your current password to confirm this action.',
  processing: false,
});

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'confirmed', passwordValue: string): void;
}>();

const passwordInput = ref<HTMLInputElement | null>(null);

// Using Inertia's useForm to handle the input and errors easily
const form = useForm({
  password: '',
});

const handleClose = () => {
  form.reset();
  form.clearErrors();
  emit('update:open', false);
};

const handleConfirm = () => {
  if (!form.password) {
    form.setError('password', 'Password is required');
    passwordInput.value?.focus();
    return;
  }

  // Emit the password back to the parent component
  emit('confirmed', form.password);
};

// Expose clear/set errors back to parent if API calls fail
defineExpose({
  setError: (message: string) => form.setError('password', message),
  clearErrors: () => form.clearErrors(),
  reset: () => form.reset(),
});
</script>

<template>
  <Dialog :open="open" @update:open="handleClose">
    <DialogContent class="max-w-md font-mono">
      <DialogHeader>
        <DialogTitle class="text-2xl">{{ title }}</DialogTitle>
        <DialogDescription class="text-md font-semibold text-muted-foreground">
          {{ description }}
        </DialogDescription>
      </DialogHeader>

      <div class="py-4">
        <label for="confirm_password" class="mb-1 block text-sm font-medium"
          >Password</label
        >
        <Input
          id="confirm_password"
          ref="passwordInput"
          v-model="form.password"
          type="password"
          class="w-full"
          placeholder="Enter your current password"
          @keyup.enter="handleConfirm"
          :class="{ 'border-destructive': form.errors.password }"
        />
        <p v-if="form.errors.password" class="mt-1 text-xs text-destructive">
          {{ form.errors.password }}
        </p>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="handleClose" :disabled="processing"
          >Cancel</Button
        >
        <Button
          variant="destructive"
          @click="handleConfirm"
          :disabled="processing || !form.password"
        >
          {{ processing ? 'Processing...' : 'Confirm' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
