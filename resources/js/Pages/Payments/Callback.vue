<template>
  <AuthenticatedLayout>
    <Head title="Payment Callback" />

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

              <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h2>
              <p class="text-gray-600 mb-6">{{ message }}</p>

              <div v-if="subscription" class="bg-green-50 border border-green-200 rounded-lg p-4 text-left mb-6">
                <h3 class="font-semibold text-green-900 mb-2">Subscription Activated</h3>
                <div class="space-y-1 text-sm text-green-800">
                  <p><strong>Plan:</strong> {{ subscription.plan.name }}</p>
                  <p><strong>Status:</strong> Active</p>
                  <p><strong>Starts:</strong> {{ formatDate(subscription.starts_at) }}</p>
                  <p v-if="subscription.ends_at"><strong>Expires:</strong> {{ formatDate(subscription.ends_at) }}</p>
                </div>
              </div>

              <div class="flex gap-3">
                <button 
                  @click="$inertia.visit(route('dashboard'))"
                  class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition"
                >
                  Go to Dashboard
                </button>
                <button 
                  @click="$inertia.visit(route('payments.index'))"
                  class="flex-1 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition"
                >
                  View Payments
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

              <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Verification Failed</h2>
              <p class="text-gray-600 mb-2">{{ message }}</p>

              <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-left mb-6">
                <p class="text-sm text-red-800"><strong>Error:</strong> {{ error }}</p>
              </div>

              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-left mb-6">
                <h3 class="font-semibold text-yellow-900 mb-2">What to do next?</h3>
                <ul class="text-sm text-yellow-800 space-y-1">
                  <li>• Check your payment status in your payment provider's dashboard</li>
                  <li>• Verify that your payment was successfully completed</li>
                  <li>• Contact support if the issue persists</li>
                </ul>
              </div>

              <div class="flex gap-3">
                <button 
                  @click="$inertia.visit(route('payments.index'))"
                  class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition"
                >
                  Back to Payments
                </button>
                <button 
                  @click="retryVerification"
                  :disabled="verifying"
                  class="flex-1 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition disabled:opacity-50"
                >
                  {{ verifying ? 'Verifying...' : 'Try Again' }}
                </button>
              </div>
            </div>

            <!-- Support Info -->
            <div class="mt-8 pt-6 border-t border-gray-200">
              <p class="text-sm text-gray-600 text-center">
                Need help? Contact our support team at 
                <a :href="`mailto:${supportEmail}`" class="text-indigo-600 hover:text-indigo-800">
                  {{ supportEmail }}
                </a>
              </p>
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

const props = defineProps({
  success: Boolean,
  subscription: Object,
  message: String,
  error: String,
});

const supportEmail = ref('support@qard.com');
const verifying = ref(false);

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const retryVerification = () => {
  verifying.value = true;
  // Get reference from URL or sessionStorage
  const urlParams = new URLSearchParams(window.location.search);
  const reference = urlParams.get('reference') || sessionStorage.getItem('lahza_reference');

  if (reference) {
    // Retry verification by visiting callback endpoint again
    window.location.href = route('payments.callback') + '?reference=' + reference;
  } else {
    verifying.value = false;
    alert('No transaction reference found. Please try starting the payment again.');
  }
};
</script>
