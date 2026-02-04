<template>
  <AuthenticatedLayout>
    <Head :title="t('addons.callback_title')" />

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="p-8">
            <!-- Success State -->
            <div v-if="success" class="text-center">
              <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>

              <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ t('addons.purchase_success_title') }}</h2>
              <p class="text-gray-600 mb-6">{{ message }}</p>

              <div v-if="addon" class="bg-green-50 border border-green-200 rounded-lg p-4 text-left mb-6">
                <h3 class="font-semibold text-green-900 mb-2">{{ t('addons.addon_activated') }}</h3>
                <div class="space-y-1 text-sm text-green-800">
                  <p><strong>{{ t('addons.addon_name') }}:</strong> {{ addon.name }}</p>
                  <p><strong>{{ t('addons.addon_type_label') }}:</strong> {{ addon.type === 'extra_cards' ? t('addons.type_extra_cards') : t('addons.type_feature_unlock') }}</p>
                </div>
              </div>

              <div class="flex gap-3">
                <button
                  @click="$inertia.visit(route('dashboard'))"
                  class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition"
                >
                  {{ t('addons.go_to_dashboard') }}
                </button>
                <button
                  @click="$inertia.visit(route('addons.index'))"
                  class="flex-1 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition"
                >
                  {{ t('addons.view_addons') }}
                </button>
              </div>
            </div>

            <!-- Error State -->
            <div v-else class="text-center">
              <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>

              <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ t('addons.purchase_failed_title') }}</h2>
              <p class="text-gray-600 mb-2">{{ message }}</p>

              <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-left mb-6">
                <p class="text-sm text-red-800"><strong>Error:</strong> {{ error }}</p>
              </div>

              <div class="flex gap-3">
                <button
                  @click="$inertia.visit(route('addons.index'))"
                  class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition"
                >
                  {{ t('addons.back_to_addons') }}
                </button>
                <button
                  @click="retryVerification"
                  :disabled="verifying"
                  class="flex-1 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition disabled:opacity-50"
                >
                  {{ verifying ? t('addons.verifying') : t('addons.try_again') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

const props = defineProps({
  success: Boolean,
  addon: Object,
  message: String,
  error: String,
});

const verifying = ref(false);

const retryVerification = () => {
  verifying.value = true;
  const urlParams = new URLSearchParams(window.location.search);
  const reference = urlParams.get('reference') || sessionStorage.getItem('lahza_addon_reference');

  if (reference) {
    window.location.href = route('addons.callback') + '?reference=' + reference;
  } else {
    verifying.value = false;
    alert('No transaction reference found. Please try starting the payment again.');
  }
};
</script>
