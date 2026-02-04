<template>
  <AuthenticatedLayout>
    <Head :title="t('addons.title')" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
          <h2 class="text-2xl font-semibold text-gray-900">{{ t('addons.title') }}</h2>
          <p class="mt-1 text-sm text-gray-600">{{ t('addons.subtitle') }}</p>
        </div>

        <!-- No subscription banner -->
        <div v-if="!hasActiveSubscription" class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-yellow-400 me-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div>
              <h3 class="text-sm font-medium text-yellow-800">{{ t('addons.no_subscription_title') }}</h3>
              <p class="mt-1 text-sm text-yellow-700">{{ t('addons.no_subscription_desc') }}</p>
              <button
                @click="$inertia.visit(route('subscription.index'))"
                class="mt-2 text-sm font-medium text-yellow-800 hover:text-yellow-900 underline"
              >
                {{ t('addons.view_plans') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Extra Card Slots Section -->
        <div v-if="extraCards.length > 0" class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('addons.extra_cards_title') }}</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="addon in extraCards"
              :key="addon.id"
              class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
            >
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center me-3">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900">{{ addon.name }}</h4>
                    <p class="text-sm text-gray-600">+{{ addon.value }} {{ t('addons.card_slots') }}</p>
                  </div>
                </div>
              </div>
              <p v-if="addon.description" class="text-sm text-gray-600 mb-4">{{ addon.description }}</p>
              <div class="flex items-center justify-between">
                <span class="text-2xl font-bold text-gray-900">${{ addon.price }}</span>
                <button
                  @click="buyAddon(addon)"
                  :disabled="!hasActiveSubscription"
                  class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                >
                  {{ t('addons.buy_now') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Feature Unlocks Section -->
        <div v-if="featureUnlocks.length > 0">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('addons.feature_unlocks_title') }}</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="addon in featureUnlocks"
              :key="addon.id"
              class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
              :class="{ 'border-green-300 bg-green-50': addon.is_owned }"
            >
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                  <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center me-3"
                    :class="addon.is_owned ? 'bg-green-100' : 'bg-purple-100'"
                  >
                    <svg v-if="addon.is_owned" class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900">{{ addon.name }}</h4>
                    <p class="text-sm text-gray-600">{{ getFeatureLabel(addon.feature_key) }}</p>
                  </div>
                </div>
              </div>
              <p v-if="addon.description" class="text-sm text-gray-600 mb-4">{{ addon.description }}</p>
              <div class="flex items-center justify-between">
                <span class="text-2xl font-bold text-gray-900">${{ addon.price }}</span>
                <span
                  v-if="addon.is_owned"
                  class="px-4 py-2 bg-green-100 text-green-800 text-sm font-semibold rounded-lg"
                >
                  {{ t('addons.owned') }}
                </span>
                <button
                  v-else
                  @click="buyAddon(addon)"
                  :disabled="!hasActiveSubscription"
                  class="px-4 py-2 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                >
                  {{ t('addons.unlock') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="extraCards.length === 0 && featureUnlocks.length === 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">{{ t('addons.empty_title') }}</h3>
          <p class="mt-1 text-sm text-gray-500">{{ t('addons.empty_desc') }}</p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

defineProps({
  extraCards: Array,
  featureUnlocks: Array,
  hasActiveSubscription: Boolean,
});

const featureLabels = {
  nfc: 'NFC Support',
  analytics: 'Advanced Analytics',
  custom_domain: 'Custom Domain',
  custom_css: 'Custom CSS',
};

const getFeatureLabel = (key) => {
  return featureLabels[key] || key;
};

const buyAddon = (addon) => {
  window.location.href = route('addons.checkout', { addon: addon.id });
};
</script>
