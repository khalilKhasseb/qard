<template>
  <AuthenticatedLayout>
    <Head :title="t('payments.checkout')" />

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <!-- Header -->
          <div class="bg-indigo-600 px-6 py-8 text-white">
            <h2 class="text-3xl font-bold mb-2">{{ t('payments.checkout_page.title') }}</h2>
            <p class="text-indigo-100">{{ t('payments.checkout_page.subtitle') }}</p>
          </div>

          <div class="p-6">
            <!-- Plan Summary -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('payments.checkout_page.order_summary') }}</h3>

              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="font-semibold text-gray-900">{{ t('payments.checkout_page.plan_name', { name: plan.name }) }}</p>
                    <p class="text-sm text-gray-600">{{ plan.description }}</p>
                  </div>
                  <div class="text-end">
                    <p class="text-2xl font-bold text-gray-900">${{ plan.price }}</p>
                    <p class="text-sm text-gray-600">/ {{ plan.billing_cycle }}</p>
                  </div>
                </div>

                <div class="border-t border-gray-200 pt-3">
                  <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ t('payments.checkout_page.included_features') }}</h4>
                  <ul class="space-y-1">
                    <li class="text-sm text-gray-600 flex items-center">
                      <svg class="w-4 h-4 text-green-500 me-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      {{ t('payments.subscription.business_cards', { count: plan.cards_limit }) }}
                    </li>
                    <li class="text-sm text-gray-600 flex items-center">
                      <svg class="w-4 h-4 text-green-500 me-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      {{ t('payments.subscription.custom_themes', { count: plan.themes_limit }) }}
                    </li>
                    <li v-if="plan.custom_domain_allowed" class="text-sm text-gray-600 flex items-center">
                      <svg class="w-4 h-4 text-green-500 me-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      {{ t('payments.subscription.custom_domain') }}
                    </li>
                    <li v-if="plan.analytics_enabled" class="text-sm text-gray-600 flex items-center">
                      <svg class="w-4 h-4 text-green-500 me-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      {{ t('payments.subscription.advanced_analytics') }}
                    </li>
                  </ul>
                </div>

                <div class="border-t border-gray-200 pt-3">
                  <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                    <span>{{ t('payments.checkout_page.total') }}</span>
                    <span>${{ plan.price }} / {{ plan.billing_cycle }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Payment Method -->
            <div class="mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('payments.checkout_page.payment_method') }}</h3>

              <div class="space-y-3">
                <!-- Card Payment (Lahza) -->
                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                       :class="form.payment_method === 'card' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200'">
                  <input
                    type="radio"
                    v-model="form.payment_method"
                    value="card"
                    class="h-4 w-4 text-indigo-600"
                  />
                  <div class="ms-3 flex-1">
                    <div class="font-semibold text-gray-900">{{ t('payments.checkout_page.card_payment') }}</div>
                    <div class="text-sm text-gray-600">{{ t('payments.checkout_page.card_payment_desc') }}</div>
                  </div>
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                </label>

                <!-- Cash Payment (Legacy) -->
                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                       :class="form.payment_method === 'cash' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200'">
                  <input
                    type="radio"
                    v-model="form.payment_method"
                    value="cash"
                    class="h-4 w-4 text-indigo-600"
                  />
                  <div class="ms-3 flex-1">
                    <div class="font-semibold text-gray-900">{{ t('payments.checkout_page.cash_payment') }}</div>
                    <div class="text-sm text-gray-600">{{ t('payments.checkout_page.cash_payment_desc') }}</div>
                  </div>
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </label>
              </div>

              <InputError :message="form.errors.payment_method" class="mt-2" />
            </div>

            <!-- Terms -->
            <div class="mb-6">
              <label class="flex items-start">
                <input
                  type="checkbox"
                  v-model="form.accept_terms"
                  class="h-4 w-4 text-indigo-600 rounded mt-1"
                />
                <span class="ms-2 text-sm text-gray-600">
                  {{ t('payments.checkout_page.terms_agree') }} <a href="#" class="text-indigo-600 hover:text-indigo-800">{{ t('payments.checkout_page.terms_of_service') }}</a>
                  {{ t('payments.checkout_page.and') }} <a href="#" class="text-indigo-600 hover:text-indigo-800">{{ t('payments.checkout_page.privacy_policy') }}</a>
                </span>
              </label>
              <InputError :message="form.errors.accept_terms" class="mt-2" />
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
              <SecondaryButton @click="$inertia.visit(route('payments.index'))" class="flex-1 justify-center">
                {{ t('common.buttons.cancel') }}
              </SecondaryButton>
              <PrimaryButton
                @click="handlePayment"
                :disabled="form.processing || !form.accept_terms"
                class="flex-1 justify-center"
              >
                {{ form.processing ? t('payments.checkout_page.processing') : t('payments.checkout_page.continue') }}
              </PrimaryButton>
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

    <!-- Lahza Checkout Modal -->
    <div v-if="showCheckoutModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">{{ t('payments.checkout_page.complete_payment') }}</h3>
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
            <button @click="checkoutError = null" class="ms-2 text-red-600 hover:text-red-800">{{ t('payments.checkout_page.dismiss') }}</button>
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
              {{ paymentProcessing ? t('payments.checkout_page.processing') : t('payments.checkout_page.open_payment_form') }}
            </button>

            <p class="text-xs text-gray-500 text-center">
              {{ t('payments.checkout_page.redirect_notice') }}
            </p>
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
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';
import SecondaryButton from '@/Components/Shared/SecondaryButton.vue';
import InputError from '@/Components/Shared/InputError.vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

const props = defineProps({
  plan: Object,
});

const form = useForm({
  subscription_plan_id: props.plan.id,
  payment_method: 'card',
  accept_terms: false,
});

const showCheckoutModal = ref(false);
const checkoutUrl = ref(null);
const checkoutError = ref(null);
const paymentProcessing = ref(false);

const handlePayment = () => {
  if (form.payment_method === 'cash') {
    initCashPayment();
  } else if (form.payment_method === 'card') {
    // Initialize Lahza payment
    initLahzaPayment();
  }
};

const initCashPayment = () => {
  form.processing = true;
  checkoutError.value = null;

  window.axios.post(route('api.payments.create'), {
    subscription_plan_id: props.plan.id,
    payment_method: 'cash',
  })
    .then(response => {
      // For Inertia redirect, the redirect happens automatically
      // For API response, manually navigate
      if (response.data?.data?.id) {
        window.location.href = route('payments.confirmation', response.data.data.id);
      }
    })
    .catch(error => {
      const message = error.response?.data?.message
        || error.message
        || 'Failed to create payment. Please try again.';
      checkoutError.value = message;
      showCheckoutModal.value = true;
    })
    .finally(() => {
      form.processing = false;
    });
};

const initLahzaPayment = () => {
  paymentProcessing.value = true;
  checkoutError.value = null;

  // Use axios (already configured with CSRF token handling in bootstrap.js)
  window.axios.post(route('payments.initialize', { plan: props.plan.id }))
    .then(response => {
      const data = response.data;
      
      if (data.checkout_url) {
        showCheckoutModal.value = true;
        checkoutUrl.value = data.checkout_url;
        paymentProcessing.value = false;

        // Store payment reference for verification
        if (data.reference) {
          sessionStorage.setItem('lahza_reference', data.reference);
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
      paymentProcessing.value = false;
    });
};

const openLahzaCheckout = () => {
  if (!checkoutUrl.value) {
    checkoutError.value = 'Checkout URL not available. Please try again.';
    return;
  }

  // Open checkout in new window
  window.open(checkoutUrl.value, '_blank', 'width=800,height=800,scrollbars=yes');
};

const closeCheckoutModal = () => {
  if (paymentProcessing.value) return;
  
  showCheckoutModal.value = false;
  checkoutUrl.value = null;
  checkoutError.value = null;
};
</script>
