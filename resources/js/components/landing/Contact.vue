<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
  name: '',
  email: '',
  subject: '',
  message: '',
});

const submitting = ref(false);
const successMessage = ref<string | null>(null);
const errorMessage = ref<string | null>(null);

function submit() {
  submitting.value = true;
  successMessage.value = null;
  errorMessage.value = null;

  form.post('/contact', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      successMessage.value = 'Submitted successfully!';
      submitting.value = false;
      setTimeout(() => {
        successMessage.value = '';
      }, 3000);
    },
  });
}
</script>

<template>
  <div
    id="contact"
    class="scroll-mt-12 bg-[url('@/assets/con12.jpg')] bg-cover bg-center bg-no-repeat py-10 text-center text-white"
  >
    <h1 class="text-4xl font-bold">CONTACT US</h1>
    <p class="pt-3 text-2xl">We're Here to Help You.</p>
    <p class="pt-1 text-xl">
      Have questions, suggestions, or partnership inquires?
      <br />Reach out to us anytime.
    </p>
  </div>

  <div class="mx-auto w-full max-w-[1320px] px-5 py-10">
    <div class="w-full rounded-md bg-brand-blue p-4 shadow-md sm:p-8">
      <div
        v-if="successMessage"
        class="my-2 rounded bg-green-100 p-2 text-green-800"
      >
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="my-2 rounded bg-red-100 p-2 text-red-800">
        {{ errorMessage }}
      </div>
      <form @submit.prevent="submit()">
        <div class="grid grid-cols-12 gap-4 md:gap-5">
          <div class="col-span-12 md:col-span-6">
            <label for="" class="text-white">Full Name</label>
            <input
              type="text"
              v-model="form.name"
              required
              placeholder="Name"
              class="mb-3 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
            />
            <div v-if="form.errors.name" class="text-red-500">
              {{ form.errors.name }}
            </div>

            <label for="" class="text-white">Email</label>
            <input
              type="email"
              v-model="form.email"
              required
              placeholder="Email"
              class="mb-3 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
            />
            <div v-if="form.errors.email" class="text-red-500">
              {{ form.errors.email }}
            </div>

            <label for="" class="text-white">Subject</label>
            <input
              type="text"
              v-model="form.subject"
              required
              placeholder="Subject"
              class="w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
            />
            <div v-if="form.errors.subject" class="text-red-500">
              {{ form.errors.subject }}
            </div>
          </div>

          <div class="col-span-12 md:col-span-6">
            <label for="" class="text-white">Message</label>
            <textarea
              placeholder="Message.."
              v-model="form.message"
              required
              rows="5"
              class="w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
            ></textarea>
            <div v-if="form.errors.message" class="text-red-500">
              {{ form.errors.message }}
            </div>

            <div class="pt-4 text-end">
              <button
                type="submit"
                :disabled="form.processing"
                class="rounded-md bg-white px-6 py-2 font-bold text-brand-blue"
              >
                {{ submitting ? 'Submitting...' : 'Send Message' }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>

    <div class="grid grid-cols-12 gap-5 pt-8">
      <div
        class="col-span-12 rounded-xl bg-brand-blue p-5 sm:p-8 md:col-span-6"
      >
        <div class="flex gap-4 sm:gap-7">
          <div>
            <div
              class="grid h-[40px] w-[40px] place-items-center rounded-full bg-white"
            >
              <i class="fa-solid fa-phone-volume text-xl text-brand-blue"></i>
            </div>
          </div>

          <div class="font-bold text-white">
            <h1 class="text-xl">MOBILE NUMBER</h1>
            <h1 class="text-2xl">(+63) 999-999-9999</h1>
          </div>
        </div>
      </div>

      <div
        class="col-span-12 rounded-xl bg-brand-blue p-5 sm:p-8 md:col-span-6"
      >
        <div class="flex gap-4 sm:gap-7">
          <div>
            <div
              class="grid h-[40px] w-[40px] place-items-center rounded-full bg-white"
            >
              <i class="fa-solid fa-clock text-2xl text-brand-blue"></i>
            </div>
          </div>

          <div class="text-white">
            <h1 class="text-xl font-bold">SUPPORT HOURS</h1>
            <h1 class="text-lg">Monday - Saturday, 8:00 AM - 6:00 PM</h1>
          </div>
        </div>
      </div>

      <div
        class="col-span-12 rounded-xl bg-brand-blue p-5 sm:p-8 md:col-span-6"
      >
        <div class="flex gap-4 sm:gap-7">
          <div>
            <div
              class="grid h-[40px] w-[40px] place-items-center rounded-full bg-white"
            >
              <i class="fa-solid fa-envelope text-2xl text-brand-blue"></i>
            </div>
          </div>

          <div class="text-white">
            <h1 class="text-xl font-bold">EMAIL</h1>
            <h1 class="text-lg">support@carboundary.ph</h1>
          </div>
        </div>
      </div>

      <div
        class="col-span-12 rounded-xl bg-brand-blue p-5 sm:p-8 md:col-span-6"
      >
        <div class="flex gap-4 sm:gap-7">
          <div>
            <div
              class="grid h-[40px] w-[40px] place-items-center rounded-full bg-white"
            >
              <i class="fa-solid fa-location-dot text-2xl text-brand-blue"></i>
            </div>
          </div>

          <div class="text-white">
            <h1 class="text-xl font-bold">ADDRESS</h1>
            <h1 class="text-lg">
              Unit 202, GreenTech Building, Quezon City, Philippines
            </h1>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
