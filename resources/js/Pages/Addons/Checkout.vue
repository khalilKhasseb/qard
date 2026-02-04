<template>
  <AuthenticatedLayout>
    <Head :title="t('addons.checkout')" />

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <!-- Header -->
          <div class="bg-indigo-600 px-6 py-8 text-white">
            <h2 class="text-3xl font-bold mb-2">{{ t('addons.checkout_page.title') }}</h2>
            <p class="text-indigo-100">{{ t('addons.checkout_page.subtitle') }}</p>
          </div>

          <div class="p-6">
            <!-- Add-on Summary -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('addons.checkout_page.order_summary') }}</h3>

              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="font-semibold text-gray-900">{{ addon.name }}</p>
                    <p class="text-sm text-gray-600">{{ addon.description }}</p>
                    <span
                      class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="addon.type === 'extra_cards' ? 'bg-indigo-100 text-indigo-800' : 'bg-purple-100 text-purple-800'"
                    >
                      {{ addon.type === 'extra_cards' ? t('addons.type_extra_cards') : t('addons.type_feature_unlock') }}
                    </span>
                  </div>
                  <div class="text-end">
                    <p class="text-2xl font-bold text-gray-900">${{ addon.price }}</p>
                    <p class="text-sm text-gray-600">{{ t('addons.one_time') }}</p>
                  </div>
                </div>

                <div class="border-t border-gray-200 pt-3">
                  <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                    <span>{{ t('addons.checkout_page.total') }}</span>
                    <span>${{ addon.price }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
              <button
                @click="$inertia.visit(route('addons.index'))"
                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition text-center"
              >
                {{ t('common.buttons.cancel') }}
              </button>
              <button
                @click="initPayment"
                :disabled="paymentProcessing"
                class="flex-1 px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition text-center"
              >
                {{ paymentProcessing ? t('addons.checkout_page.processing') : t('addons.checkout_page.pay_now') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Security Badge -->
        <div class="mt-6 text-center text-sm text-gray-500">
          <svg class="w-5 h-5 inline me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          {{ t('payments.checkout_page.secure_checkout') }}
        </div>
      </div>
    </div>

    <!-- Checkout Modal -->
    <div v-if="showCheckoutModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">{{ t('addons.checkout_page.complete_payment') }}</h3>
            <button
              @click="closeCheckoutModal"
              class="text-gray-400 hover:text-gray-600"
              :disabled="paymentProcessing"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="checkoutError" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            {{ checkoutError }}
          </div>

          <div v-if="checkoutUrl" class="space-y-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <p class="text-sm text-blue-800">
                {{ t('payments.checkout_page.payment_redirect_info') }}
              </p>
            </div>

            <button
              @click="openLahzaCheckout"
              :disabled="paymentProcessing"
              class="w-full bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
              {{ t('payments.checkout_page.open_payment_form') }}
            </button>
          </div>

          <div v-else-if="paymentProcessing" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
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
  addon: Object,
});

const showCheckoutModal = ref(false);
const checkoutUrl = ref(null);
const checkoutError = ref(null);
const paymentProcessing = ref(false);

const initPayment = () => {
  paymentProcessing.value = true;
  checkoutError.value = null;

  window.axios.post(route('addons.initialize', { addon: props.addon.id }))
    .then(response => {
      const data = response.data;

      if (data.checkout_url) {
        showCheckoutModal.value = true;
        checkoutUrl.value = data.checkout_url;
        paymentProcessing.value = false;

        if (data.reference) {
          sessionStorage.setItem('lahza_addon_reference', data.reference);
        }
      } else {
        throw new Error('No checkout URL received');
      }
    })
    .catch(error => {
      const message = error.response?.data?.message
        || error.message
        || 'Failed to initialize payment. Please try again.';
      checkoutError.value = message;
      showCheckoutModal.value = true;
      paymentProcessing.value = false;
    });
};

const openLahzaCheckout = () => {
  if (!checkoutUrl.value) {
    checkoutError.value = 'Checkout URL not available. Please try again.';
    return;
  }
  window.open(checkoutUrl.value, '_blank', 'width=800,height=800,scrollbars=yes');
};

const closeCheckoutModal = () => {
  if (paymentProcessing.value) return;
  showCheckoutModal.value = false;
  checkoutUrl.value = null;
  checkoutError.value = null;
};
</script>
