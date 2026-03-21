<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Eye, EyeOff, Lock } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// --- TYPES ---
interface FieldNames {
  password: string;
  confirmPassword: string;
  terms1: string;
  terms2: string;
}
interface Labels {
  terms1: string;
  terms2: string;
}
interface ShowFields {
  password: boolean;
  terms1: boolean;
  terms2: boolean;
}

// --- PROPS ---
const props = defineProps<{
  errors?: Record<string, string>;
  // v-model props
  password?: string;
  confirmPassword?: string;
  terms1?: boolean;
  terms2?: boolean;
  // Customization props (all optional)
  fieldNames?: Partial<FieldNames>;
  labels?: Partial<Labels>;
  showFields?: Partial<ShowFields>;
}>();

// --- EMITS ---
const emit = defineEmits([
  'update:terms1',
  'update:terms2',
  'update:password',
  'update:confirmPassword',
]);

// --- V-MODEL COMPUTEDS ---
const computedTerms1 = computed<boolean>({
  get: () => props.terms1,
  set: (value) => emit('update:terms1', value),
});
const computedTerms2 = computed<boolean>({
  get: () => props.terms2,
  set: (value) => emit('update:terms2', value),
});

// --- DEFAULTS ---
const defaultFieldNames: FieldNames = {
  password: 'password',
  confirmPassword: 'password_confirmation',
  terms1: 'terms1',
  terms2: 'terms2',
};
const defaultLabels: Labels = {
  terms1: 'I Agree to the Franchise Terms and Boundary Policy',
  terms2: 'I confirm all details are true and valid',
};
const defaultShowFields: ShowFields = {
  password: true,
  terms1: true,
  terms2: true,
};

// --- MERGED COMPUTEDS ---
const fields = computed(() => ({ ...defaultFieldNames, ...props.fieldNames }));
const labels = computed(() => ({ ...defaultLabels, ...props.labels }));
const show = computed(() => ({ ...defaultShowFields, ...props.showFields }));

// --- INTERNAL STATE ---
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// --- PASSWORD VALIDATION LOGIC ---
const passwordRequirements = computed(() => {
  const val = props.password || '';
  return [
    { label: 'At least 8 characters', met: val.length >= 8 },
    { label: 'At least one uppercase letter', met: /[A-Z]/.test(val) },
    { label: 'At least one lowercase letter', met: /[a-z]/.test(val) },
    { label: 'At least one number', met: /\d/.test(val) },
    {
      label: 'At least one special character (@$!%*?&)',
      met: /[@$!%*?&#]/.test(val),
    },
  ];
});

const passwordsMatch = computed(() => {
  // If confirmation is empty, don't show an error yet
  if (!props.confirmPassword) return true;
  return props.password === props.confirmPassword;
});
</script>

<template>
  <!-- Password -->
  <!-- Password -->
  <template v-if="show.password">
    <div class="grid gap-2">
      <Label for="password" class="font-semibold text-auth-blue"
        >Password</Label
      >
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
            :name="fields.password"
            :model-value="password"
            @update:model-value="emit('update:password', $event)"
            required
            placeholder="Password"
            class="flex-1 border-0 font-mono font-semibold focus-visible:ring-0"
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
      </div>

      <div
        v-if="
          (password?.length ?? 0) > 0 &&
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

      <p
        v-else-if="passwordRequirements.every((req) => req.met)"
        class="text-[11px] font-bold text-green-600"
      >
        ✔ Password is secure
      </p>

      <InputError :message="errors?.[fields.password]" />
    </div>

    <div class="grid gap-2">
      <Label for="password_confirmation" class="font-semibold text-auth-blue"
        >Confirm Password</Label
      >
      <div
        class="flex w-full max-w-sm overflow-hidden rounded-md border border-gray-300"
        :class="!passwordsMatch ? 'border-red-500' : ''"
      >
        <div class="flex items-center justify-center bg-auth-blue px-3">
          <Lock class="h-5 w-5 text-white" />
        </div>
        <div class="relative w-full items-center">
          <Input
            id="password_confirmation"
            :type="showConfirmPassword ? 'text' : 'password'"
            :name="fields.confirmPassword"
            :model-value="confirmPassword"
            @update:model-value="emit('update:confirmPassword', $event)"
            required
            placeholder="Confirm Password"
            class="flex-1 border-0 font-mono font-semibold focus-visible:ring-0"
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
      </div>
      <p v-if="!passwordsMatch" class="text-xs font-semibold text-red-500">
        Passwords do not match
      </p>
      <InputError :message="errors?.[fields.confirmPassword]" />
    </div>
  </template>

  <!-- Terms 1 -->
  <div v-if="show.terms1" class="flex items-center gap-x-2">
    <Checkbox
      :id="fields.terms1"
      :name="fields.terms1"
      v-model="computedTerms1"
      class="border-gray-500 shadow"
    />
    <div class="grid gap-1.5 leading-none">
      <label
        :for="fields.terms1"
        class="cursor-pointer text-xs leading-none font-medium text-auth-blue peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
      >
        {{ labels.terms1 }}
      </label>
      <InputError :message="errors?.[fields.terms1]" />
    </div>
  </div>

  <!-- Terms 2 -->
  <div v-if="show.terms2" class="flex items-center gap-x-2">
    <Checkbox
      :id="fields.terms2"
      :name="fields.terms2"
      v-model="computedTerms2"
      class="border-gray-500 shadow"
    />
    <div class="grid gap-1.5 leading-none">
      <label
        :for="fields.terms2"
        class="cursor-pointer text-xs leading-none font-medium text-auth-blue peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
      >
        {{ labels.terms2 }}
      </label>
      <InputError :message="errors?.[fields.terms2]" />
    </div>
  </div>
</template>
