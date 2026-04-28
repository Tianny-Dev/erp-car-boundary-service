<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { useForm, usePage } from '@inertiajs/vue3';
import { AlertTriangle, Loader2, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface FormData {
  email: string;
  reason: string;
}

const confirmed = ref<boolean>(false);

const form = useForm<FormData>({
  email: '',
  reason: '',
});

const page = usePage();
const flash = computed(
  () => (page.props.flash as Record<string, string>) ?? {},
);

const isDisabled = computed<boolean>(() => form.processing || !confirmed.value);

const submit = () => {
  form
    .transform((data) => ({
      ...data,
      confirmed: confirmed.value ? 'on' : '',
    }))
    .post('/account-deletion');
};
</script>

<template>
  <div class="flex min-h-screen items-center justify-center p-4">
    <div class="w-full max-w-md space-y-6">
      <div>
        <h1 class="text-2xl font-semibold tracking-tight">Account Deletion</h1>
        <p class="mt-1 text-sm text-muted-foreground">
          Request permanent removal of your account and associated data.
        </p>
      </div>

      <Separator />

      <template v-if="flash.success">
        <Alert>
          <AlertDescription>{{ flash.success }}</AlertDescription>
        </Alert>
        <p class="text-sm text-muted-foreground">
          You can safely close this page.
        </p>
      </template>

      <template v-else>
        <Alert variant="destructive">
          <AlertTriangle class="h-4 w-4" />
          <AlertDescription>
            This action is irreversible. Your account and all associated data
            will be permanently deleted within 30 days.
          </AlertDescription>
        </Alert>

        <form @submit.prevent="submit" class="space-y-4">
          <div class="space-y-2">
            <Label for="email">Email address</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              placeholder="you@example.com"
              :class="{ 'border-destructive': form.errors.email }"
            />
            <p v-if="form.errors.email" class="text-sm text-destructive">
              {{ form.errors.email }}
            </p>
          </div>

          <div class="space-y-2">
            <Label for="reason">
              Reason
              <span class="font-normal text-muted-foreground">(optional)</span>
            </Label>
            <Textarea
              id="reason"
              v-model="form.reason"
              placeholder="Help us understand why you're leaving…"
              rows="4"
              :class="{ 'border-destructive': form.errors.reason }"
            />
            <p v-if="form.errors.reason" class="text-sm text-destructive">
              {{ form.errors.reason }}
            </p>
          </div>

          <div class="flex items-start gap-3">
            <input
              id="confirmed"
              v-model="confirmed"
              type="checkbox"
              class="mt-1 h-4 w-4 cursor-pointer rounded border-gray-300 accent-primary"
            />
            <Label
              for="confirmed"
              class="cursor-pointer leading-snug font-normal"
            >
              I understand this action is permanent and cannot be undone.
            </Label>
          </div>

          <Button
            type="submit"
            variant="destructive"
            class="w-full"
            :disabled="isDisabled"
          >
            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
            <Trash2 v-else class="mr-2 h-4 w-4" />
            {{ form.processing ? 'Submitting…' : 'Request Deletion' }}
          </Button>
        </form>
      </template>
    </div>
  </div>
</template>
