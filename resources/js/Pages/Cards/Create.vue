<template>
  <AuthenticatedLayout>
    <Head title="Create Business Card" />

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg">
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-900">Create New Business Card</h2>
            <p class="mt-1 text-sm text-gray-600">Fill in the basic information for your card.</p>
          </div>

          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- Title -->
            <div>
              <InputLabel for="title" value="Card Title *" />
              <TextInput
                id="title"
                v-model="form.title"
                type="text"
                class="mt-1 block w-full"
                placeholder="Your Name or Business Name"
                required
                autofocus
              />
              <InputError :message="form.errors.title" class="mt-2" />
            </div>

            <!-- Subtitle -->
            <div>
              <InputLabel for="subtitle" value="Subtitle" />
              <TextInput
                id="subtitle"
                v-model="form.subtitle"
                type="text"
                class="mt-1 block w-full"
                placeholder="Your Title or Tagline"
              />
              <InputError :message="form.errors.subtitle" class="mt-2" />
            </div>

            <!-- Theme Selection -->
            <div>
              <InputLabel for="theme_id" value="Theme" />
              <select
                id="theme_id"
                v-model="form.theme_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option :value="null">Default Theme</option>
                <option v-for="theme in themes" :key="theme.id" :value="theme.id">
                  {{ theme.name }}
                </option>
              </select>
              <InputError :message="form.errors.theme_id" class="mt-2" />
            </div>

            <!-- Custom Slug -->
            <div>
              <InputLabel for="custom_slug" value="Custom URL (optional)" />
              <div class="mt-1 flex rounded-md shadow-sm">
                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                  {{ appUrl }}/u/
                </span>
                <TextInput
                  id="custom_slug"
                  v-model="form.custom_slug"
                  type="text"
                  class="flex-1 rounded-none rounded-r-md"
                  placeholder="your-custom-url"
                />
              </div>
              <p class="mt-1 text-xs text-gray-500">Leave empty to use a generated URL</p>
              <InputError :message="form.errors.custom_slug" class="mt-2" />
            </div>

            <!-- Publish Toggle -->
            <div class="flex items-center">
              <input
                id="is_published"
                v-model="form.is_published"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <label for="is_published" class="ml-2 block text-sm text-gray-900">
                Publish immediately
              </label>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
              <SecondaryButton @click="$inertia.visit(route('cards.index'))">
                Cancel
              </SecondaryButton>
              <PrimaryButton :disabled="form.processing">
                {{ form.processing ? 'Creating...' : 'Create Card' }}
              </PrimaryButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
  themes: Array,
  appUrl: String,
});

const form = useForm({
  title: '',
  subtitle: '',
  theme_id: null,
  custom_slug: '',
  is_published: false,
});

const submit = () => {
  form.post(route('cards.store'), {
    onSuccess: () => form.reset(),
  });
};
</script>
