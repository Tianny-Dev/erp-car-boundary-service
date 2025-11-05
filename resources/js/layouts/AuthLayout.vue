<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { home, login } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
  textOverlay?: string;
  title?: string;
  description?: string;
  titleRegistration?: string;

  goBackHomeButton?: boolean;
}>();
</script>

<template>
  <div
    class="flex min-h-svh flex-col items-center justify-center bg-muted bg-[url(@/assets/auth/loginbg.jpg)] p-6 md:p-10"
  >
    <div class="w-full max-w-sm md:max-w-3xl">
      <div class="flex flex-col gap-6">
        <Card class="overflow-hidden p-0">
          <CardContent class="grid p-0 md:grid-cols-2">
            <div class="relative hidden bg-muted p-6 md:block">
              <!-- Background -->
              <div
                class="absolute inset-0 bg-[url(@/assets/auth/logindriver.jpg)] bg-cover bg-center bg-no-repeat dark:brightness-[0.2] dark:grayscale"
              ></div>

              <!-- Overlay -->
              <div class="absolute inset-0 bg-black/40"></div>

              <!-- Foreground content -->
              <div
                class="relative z-10 flex h-full w-full flex-col items-center justify-between py-10 text-center"
              >
                <!-- Title -->
                <h1
                  class="mt-auto mb-auto text-2xl leading-relaxed font-bold text-white drop-shadow-lg"
                >
                  {{ textOverlay }}
                </h1>

                <!-- Bottom Home and Login -->
                <template v-if="goBackHomeButton">
                  <div
                    class="absolute bottom-8 left-1/2 z-10 flex w-full max-w-xs -translate-x-1/2 flex-col items-center space-y-3"
                  >
                    <!-- Return Home Button -->
                    <Button
                      variant="secondary"
                      asChild
                      class="w-full cursor-pointer"
                    >
                      <Link :href="home()">Return Home</Link>
                    </Button>

                    <!-- Login Text -->
                    <div class="text-center text-sm text-white/90">
                      Already have an account?
                      <TextLink
                        :href="login()"
                        class="text-white underline underline-offset-4 hover:text-white/80"
                      >
                        Log in
                      </TextLink>
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <div>
              <Card class="border-0 shadow-none">
                <CardHeader class="pt-8 text-center">
                  <!-- For Login Title and Description -->
                  <CardTitle class="text-xl text-auth-blue">{{
                    title
                  }}</CardTitle>

                  <CardDescription>
                    {{ description }}
                  </CardDescription>
                </CardHeader>

                <!-- For Registration Titles -->
                <CardTitle
                  v-if="titleRegistration"
                  class="w-full bg-auth-blue py-2 text-center text-3xl text-white"
                >
                  {{ titleRegistration }}
                </CardTitle>

                <!-- Card Content Here -->
                <CardContent class="border-0 py-2 md:px-8">
                  <slot />
                </CardContent>
              </Card>
            </div>
          </CardContent>
        </Card>
        <div
          class="bg-accent p-4 text-center text-xs text-balance text-muted-foreground [&_a]:underline [&_a]:underline-offset-4 hover:[&_a]:text-primary"
        >
          By clicking continue, you agree to our
          <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
        </div>
      </div>
    </div>
  </div>
</template>
