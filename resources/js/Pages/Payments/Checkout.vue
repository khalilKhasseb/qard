<template>
  <AuthenticatedLayout>
    <Head title="Checkout" />

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <!-- Header -->
          <div class="bg-indigo-600 px-6 py-8 text-white">
            <h2 class="text-3xl font-bold mb-2">Complete Your Purchase</h2>
            <p class="text-indigo-100">You're one step away from upgrading your account</p>
          </div>

          <div class="p-6">
            <!-- Plan Summary -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
              
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="font-semibold text-gray-900">{{ plan.name }} Plan</p>
                    <p class="text-sm text-gray-600">{{ plan.description }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-2xl font-bold text-gray-900">${{ plan.price }}</p>
                    <p class="text-sm text-gray-600">/ {{ plan.billing_cycle }}</p>
                  </div>
                </div>

                <div class="border-t border-gray-200 pt-3">
                  <h4 class="text-sm font-semibold text-gray-700 mb-2">Included Features:</h4>
                  <ul class="space-y-1">
                    <li class="text-sm text-gray-600 flex items-center">
                      <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      {{ plan.card_limit }} Business Cards
                    </li>
                    <li class="text-sm text-gray-600 flex items-center">
                      <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      {{ plan.theme_limit }} Custom Themes
                    </li>
                    <li v-if="plan.custom_domain_enabled" class="text-sm text-gray-600 flex items-center">
                      <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Custom Domain
                    </li>
                    <li v-if="plan.analytics_enabled" class="text-sm text-gray-600 flex items-center">
                      <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Advanced Analytics
                    </li>
                  </ul>
                </div>

                <div class="border-t border-gray-200 pt-3">
                  <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                    <span>Total</span>
                    <span>${{ plan.price }} / {{ plan.billing_cycle }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Payment Method -->
            <div class="mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h3>
              
              <div class="space-y-3">
                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                       :class="form.payment_method === 'cash' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200'">
                  <input
                    type="radio"
                    v-model="form.payment_method"
                    value="cash"
                    class="h-4 w-4 text-indigo-600"
                  />
                  <div class="ml-3 flex-1">
                    <div class="font-semibold text-gray-900">Cash Payment</div>
                    <div class="text-sm text-gray-600">Pay via cash. Contact support for payment instructions.</div>
                  </div>
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </label>

                <label class="flex items-center p-4 border-2 rounded-lg cursor-not-allowed opacity-50"
                       :class="form.payment_method === 'card' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200'">
                  <input
                    type="radio"
                    v-model="form.payment_method"
                    value="card"
                    disabled
                    class="h-4 w-4 text-indigo-600"
                  />
                  <div class="ml-3 flex-1">
                    <div class="font-semibold text-gray-900">Credit/Debit Card</div>
                    <div class="text-sm text-gray-600">Coming soon - Pay securely with your card</div>
                  </div>
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
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
                <span class="ml-2 text-sm text-gray-600">
                  I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-800">Terms of Service</a> 
                  and <a href="#" class="text-indigo-600 hover:text-indigo-800">Privacy Policy</a>
                </span>
              </label>
              <InputError :message="form.errors.accept_terms" class="mt-2" />
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
              <SecondaryButton @click="$inertia.visit(route('payments.index'))" class="flex-1 justify-center">
                Cancel
              </SecondaryButton>
              <PrimaryButton 
                @click="completePayment" 
                :disabled="form.processing || !form.accept_terms"
                class="flex-1 justify-center"
              >
                {{ form.processing ? 'Processing...' : `Pay $${plan.price}` }}
              </PrimaryButton>
            </div>
          </div>
        </div>

        <!-- Security Badge -->
        <div class="mt-6 text-center text-sm text-gray-500">
          <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          Secure checkout powered by Qard
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  plan: Object,
});

const form = useForm({
  subscription_plan_id: props.plan.id,
  payment_method: 'cash',
  accept_terms: false,
});

const completePayment = () => {
  form.post(route('api.payments.create'), {
    onSuccess: () => {
      // Redirect handled by controller
    },
  });
};
</script>
