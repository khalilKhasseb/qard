<template>
  <AuthenticatedLayout>
    <Head :title="t('analytics.upgrade.title')" />

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
          <!-- Lock Icon -->
          <div class="mx-auto w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>

          <!-- Title -->
          <h2 class="text-2xl font-semibold text-gray-900 mb-2">
            {{ t('analytics.upgrade.heading') }}
          </h2>

          <!-- Message -->
          <p class="text-gray-600 mb-6">
            {{ message || t('analytics.upgrade.description') }}
          </p>

          <!-- Features List -->
          <div class="bg-gray-50 rounded-lg p-6 mb-6 text-start">
            <h3 class="font-medium text-gray-900 mb-4">{{ t('analytics.upgrade.features_title') }}</h3>
            <ul class="space-y-3">
              <li class="flex items-center gap-3 text-sm text-gray-700">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ t('analytics.upgrade.feature_views') }}
              </li>
              <li class="flex items-center gap-3 text-sm text-gray-700">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ t('analytics.upgrade.feature_devices') }}
              </li>
              <li class="flex items-center gap-3 text-sm text-gray-700">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ t('analytics.upgrade.feature_geographic') }}
              </li>
              <li class="flex items-center gap-3 text-sm text-gray-700">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ t('analytics.upgrade.feature_events') }}
              </li>
            </ul>
          </div>

          <!-- CTA Button -->
          <Link
            :href="route('subscription.index')"
            class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
          >
            {{ t('analytics.upgrade.cta') }}
            <svg class="ms-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

defineProps({
  message: String,
});
</script>
