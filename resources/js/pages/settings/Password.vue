<script setup lang="ts">
import { ref, computed } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import { edit } from '@/routes/user-password';
import { Eye, EyeOff } from 'lucide-vue-next';

const breadcrumbItems = [{ title: 'Password settings', href: edit().url }];

// Toggle states
const showCurrentPassword = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Password fields
const currentPassword = ref('');
const password = ref('');
const confirmPassword = ref('');

// Password validation
const passwordRequirements = computed(() => {
  return [
    { label: 'At least 8 characters', met: password.value.length >= 8 },
    {
      label: 'At least one uppercase letter',
      met: /[A-Z]/.test(password.value),
    },
    {
      label: 'At least one lowercase letter',
      met: /[a-z]/.test(password.value),
    },
    { label: 'At least one number', met: /\d/.test(password.value) },
    {
      label: 'At least one special character (@$!%*?&)',
      met: /[@$!%*?&#]/.test(password.value),
    },
  ];
});

const passwordsMatch = computed(() => {
  if (!confirmPassword.value) return true;
  return password.value === confirmPassword.value;
});

// Disable Save button if errors exist
const canSave = computed(() => {
  return (
    passwordRequirements.value.every((req) => req.met) &&
    passwordsMatch.value &&
    currentPassword.value.length > 0
  );
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Password settings" />

    <SettingsLayout>
      <div class="space-y-6">
        <HeadingSmall
          title="Update password"
          description="Ensure your account is using a long, random password to stay secure"
        />

        <Form
          v-bind="PasswordController.update.form()"
          :options="{ preserveScroll: true }"
          reset-on-success
          :reset-on-error="[
            'password',
            'password_confirmation',
            'current_password',
          ]"
          class="space-y-6"
          v-slot="{ errors, processing, recentlySuccessful }"
        >
          <!-- Current Password -->
          <div class="grid gap-2">
            <Label for="current_password">Current password</Label>
            <div class="relative w-full items-center">
              <Input
                id="current_password"
                name="current_password"
                :type="showCurrentPassword ? 'text' : 'password'"
                v-model="currentPassword"
                autocomplete="current-password"
                required
                placeholder="Current password"
                class="mt-1 block w-full pe-10"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500"
                @click="showCurrentPassword = !showCurrentPassword"
              >
                <Eye v-if="!showCurrentPassword" class="h-5 w-5" />
                <EyeOff v-else class="h-5 w-5" />
              </button>
            </div>

            <InputError :message="errors.current_password" />
          </div>

          <!-- New Password -->
          <div class="grid gap-2">
            <Label for="password">New password</Label>

            <div class="relative w-full items-center">
              <Input
                id="password"
                name="password"
                :type="showPassword ? 'text' : 'password'"
                v-model="password"
                autocomplete="new-password"
                required
                placeholder="New password"
                class="mt-1 block w-full pe-10"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500"
                @click="showPassword = !showPassword"
              >
                <Eye v-if="!showPassword" class="h-5 w-5" />
                <EyeOff v-else class="h-5 w-5" />
              </button>
            </div>

            <!-- Password requirements -->
            <div
              v-if="
                password.length > 0 &&
                !passwordRequirements.every((req) => req.met)
              "
              class="mt-1 grid grid-cols-1 gap-1 text-[11px]"
            >
              <div
                v-for="(req, index) in passwordRequirements"
                :key="index"
                :class="req.met ? 'text-green-600' : 'text-gray-400'"
                class="flex items-center gap-1 transition-colors duration-300"
              >
                <span v-if="req.met">✔</span>
                <span v-else>○</span>
                {{ req.label }}
              </div>
            </div>

            <InputError :message="errors.password" />
          </div>

          <!-- Confirm Password -->
          <div class="grid gap-2">
            <Label for="password_confirmation">Confirm Password</Label>

            <div class="relative w-full items-center">
              <Input
                id="password_confirmation"
                name="password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                v-model="confirmPassword"
                autocomplete="new-password"
                required
                placeholder="Confirm Password"
                class="mt-1 block w-full pe-10"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                <Eye v-if="!showConfirmPassword" class="h-5 w-5" />
                <EyeOff v-else class="h-5 w-5" />
              </button>
            </div>

            <p
              v-if="!passwordsMatch"
              class="text-xs font-semibold text-red-500"
            >
              Passwords do not match
            </p>
            <InputError :message="errors.password_confirmation" />
          </div>

          <!-- Save Button -->
          <div class="flex items-center gap-4">
            <Button
              :disabled="processing || !canSave"
              data-test="update-password-button"
            >
              Save password
            </Button>

            <Transition
              enter-active-class="transition ease-in-out"
              enter-from-class="opacity-0"
              leave-active-class="transition ease-in-out"
              leave-to-class="opacity-0"
            >
              <p v-show="recentlySuccessful" class="text-sm text-neutral-600">
                Saved.
              </p>
            </Transition>
          </div>
        </Form>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>
