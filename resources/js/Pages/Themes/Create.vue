<template>
  <AuthenticatedLayout>
    <Head :title="t('themes.create')" />

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg">
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-900">{{ t('themes.create_page.title') }}</h2>
            <p class="mt-1 text-sm text-gray-600">{{ t('themes.create_page.description') }}</p>

            <!-- Usage Indicator -->
            <div v-if="themeLimit > 0" class="mt-3">
              <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span>{{ t('themes.create_page.usage', { count: themeCount, limit: themeLimit }) }}</span>
                <span :class="themeCount >= themeLimit ? 'text-red-600 font-semibold' : 'text-gray-600'">
                  {{ t('themes.create_page.remaining', { count: themeLimit - themeCount }) }}
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="h-2 rounded-full transition-all"
                  :class="themeCount >= themeLimit ? 'bg-red-500' : 'bg-green-500'"
                  :style="`width: ${(themeCount / themeLimit) * 100}%`"
                ></div>
              </div>
            </div>
          </div>

          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- Name -->
            <div>
              <InputLabel for="name" :value="t('themes.fields.name_required')" />
              <TextInput
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1 block w-full"
                :placeholder="t('themes.fields.name_placeholder')"
                required
                autofocus
              />
              <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <!-- Make Public -->
            <div class="flex items-center">
              <input
                id="is_public"
                v-model="form.is_public"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <label for="is_public" class="ms-2 block text-sm text-gray-900">
                {{ t('themes.fields.make_public') }}
              </label>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
              <div class="flex">
                <svg class="h-5 w-5 text-blue-400 me-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-blue-700">
                  {{ t('themes.create_page.info') }}
                </p>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
              <SecondaryButton @click="$inertia.visit(route('themes.index'))">
                {{ t('common.buttons.cancel') }}
              </SecondaryButton>
              <PrimaryButton :disabled="form.processing">
                {{ form.processing ? t('themes.create_page.creating') : t('themes.create') }}
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
import InputLabel from '@/Components/Shared/InputLabel.vue';
import TextInput from '@/Components/Shared/TextInput.vue';
import InputError from '@/Components/Shared/InputError.vue';
import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';
import SecondaryButton from '@/Components/Shared/SecondaryButton.vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

defineProps({
  canUseCustomCSS: Boolean,
  themeCount: {
    type: Number,
    default: 0,
  },
  themeLimit: {
    type: Number,
    default: 0,
  },
});

const form = useForm({
  name: '',
  is_public: false,
  config: {
    colors: {
      primary: '#3b82f6',
      secondary: '#1e40af',
      background: '#ffffff',
      text: '#1f2937',
      card_bg: '#f9fafb',
    },
    fonts: {
      heading: 'Inter',
      body: 'Inter',
    },
    images: {},
    layout: {
      card_style: 'elevated',
      border_radius: '12px',
      alignment: 'center',
      spacing: 'normal',
    },
    custom_css: '',
  },
});

const submit = () => {
  form.post(route('themes.store'));
};
</script>
