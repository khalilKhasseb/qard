<template>
  <AuthenticatedLayout>
    <Head title="Payments & Subscription" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Payments & Subscription</h2>

        <!-- Current Subscription -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Current Subscription</h3>
          </div>
          <div class="p-6">
            <div v-if="subscription" class="space-y-4">
              <div class="flex justify-between items-start">
                <div>
                  <h4 class="text-xl font-bold text-gray-900">{{ subscription.subscription_plan.name }}</h4>
                  <p class="text-gray-600 mt-1">{{ subscription.subscription_plan.description }}</p>
                </div>
                <span
                  :class="getStatusColor(subscription.status)"
                  class="px-3 py-1 text-sm font-medium rounded-full"
                >
                  {{ subscription.status }}
                </span>
              </div>

              <div class="grid grid-cols-2 gap-4 py-4 border-t border-gray-200">
                <div>
                  <p class="text-sm text-gray-500">Price</p>
                  <p class="text-lg font-semibold text-gray-900">
                    ${{ subscription.subscription_plan.price }} / {{ subscription.subscription_plan.billing_cycle }}
                  </p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Next Billing Date</p>
                  <p class="text-lg font-semibold text-gray-900">
                    {{ formatDate(subscription.ends_at) }}
                  </p>
                </div>
              </div>

              <div class="pt-4 border-t border-gray-200">
                <h5 class="text-sm font-medium text-gray-700 mb-2">Plan Features:</h5>
                <ul class="space-y-1">
                  <li class="text-sm text-gray-600">✓ {{ subscription.subscription_plan.card_limit }} Business Cards</li>
                  <li class="text-sm text-gray-600">✓ {{ subscription.subscription_plan.theme_limit }} Custom Themes</li>
                  <li v-if="subscription.subscription_plan.custom_domain_enabled" class="text-sm text-gray-600">✓ Custom Domain</li>
                  <li v-if="subscription.subscription_plan.analytics_enabled" class="text-sm text-gray-600">✓ Advanced Analytics</li>
                  <li v-if="subscription.subscription_plan.priority_support" class="text-sm text-gray-600">✓ Priority Support</li>
                </ul>
              </div>

              <div v-if="subscription.status === 'active'" class="pt-4 border-t border-gray-200">
                <button
                  @click="cancelSubscription"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                  Cancel Subscription
                </button>
              </div>
            </div>

            <div v-else class="text-center py-8">
              <p class="text-gray-500 mb-4">No active subscription</p>
              <PrimaryButton @click="showPlans = true">
                Choose a Plan
              </PrimaryButton>
            </div>
          </div>
        </div>

        <!-- Available Plans (if no subscription) -->
        <div v-if="!subscription || showPlans" class="mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Plans</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
              v-for="plan in plans"
              :key="plan.id"
              class="bg-white rounded-lg shadow-sm border-2 hover:border-indigo-500 transition-colors"
              :class="plan.is_popular ? 'border-indigo-500' : 'border-gray-200'"
            >
              <div class="p-6">
                <div v-if="plan.is_popular" class="inline-block px-3 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full mb-4">
                  Most Popular
                </div>
                <h4 class="text-xl font-bold text-gray-900">{{ plan.name }}</h4>
                <p class="text-gray-600 mt-2 text-sm">{{ plan.description }}</p>
                <div class="mt-4">
                  <span class="text-4xl font-bold text-gray-900">${{ plan.price }}</span>
                  <span class="text-gray-500">/ {{ plan.billing_cycle }}</span>
                </div>

                <ul class="mt-6 space-y-3">
                  <li class="text-sm text-gray-600 flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ plan.card_limit }} Business Cards
                  </li>
                  <li class="text-sm text-gray-600 flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ plan.theme_limit }} Custom Themes
                  </li>
                  <li v-if="plan.custom_domain_enabled" class="text-sm text-gray-600 flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Custom Domain
                  </li>
                  <li v-if="plan.analytics_enabled" class="text-sm text-gray-600 flex items-start">
                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Advanced Analytics
                  </li>
                </ul>

                <PrimaryButton
                  @click="selectPlan(plan)"
                  class="w-full justify-center mt-6"
                  :class="plan.is_popular ? '' : 'bg-gray-900 hover:bg-gray-800'"
                >
                  Choose {{ plan.name }}
                </PrimaryButton>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment History -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Payment History</h3>
          </div>
          <div class="p-6">
            <div v-if="payments.length > 0" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Date
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Amount
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Method
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="payment in payments" :key="payment.id">
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(payment.created_at) }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                      ${{ payment.amount }} {{ payment.currency }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ payment.payment_method }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                      <span
                        :class="getStatusColor(payment.status)"
                        class="px-2 py-1 text-xs font-medium rounded-full"
                      >
                        {{ payment.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              No payment history yet
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <Modal :show="showPaymentModal" @close="showPaymentModal = false">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Complete Payment</h3>
        <p class="text-gray-600 mb-4">
          You've selected the <strong>{{ selectedPlan?.name }}</strong> plan for ${{ selectedPlan?.price }}/{{ selectedPlan?.billing_cycle }}.
        </p>
        <p class="text-sm text-gray-500 mb-6">
          For cash payment, please contact our support team with your payment details.
        </p>
        <div class="flex gap-3">
          <SecondaryButton @click="showPaymentModal = false" class="flex-1 justify-center">
            Cancel
          </SecondaryButton>
          <PrimaryButton @click="confirmPayment" class="flex-1 justify-center">
            Confirm Payment
          </PrimaryButton>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';

defineProps({
  subscription: Object,
  plans: Array,
  payments: Array,
});

const showPlans = ref(false);
const showPaymentModal = ref(false);
const selectedPlan = ref(null);

const getStatusColor = (status) => {
  const colors = {
    active: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    canceled: 'bg-red-100 text-red-800',
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const selectPlan = (plan) => {
  router.visit(route('payments.checkout', plan.id));
};

const cancelSubscription = () => {
  if (confirm('Are you sure you want to cancel your subscription?')) {
    router.post(route('api.subscription.cancel'), {}, {
      onSuccess: () => {
        router.reload();
      },
    });
  }
};
</script>
