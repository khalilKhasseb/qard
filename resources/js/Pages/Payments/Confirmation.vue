<template>
  <AuthenticatedLayout>
    <Head :title="t('payments.confirmation')" />

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <!-- Success Header -->
          <div class="bg-green-50 border-b border-green-100 px-6 py-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ t('payments.confirmation_page.title') }}</h2>
            <p class="text-gray-600">{{ t('payments.confirmation_page.thank_you') }}</p>
          </div>

          <div class="p-6">
            <!-- Payment Details -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('payments.confirmation_page.details') }}</h3>

              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600">{{ t('payments.confirmation_page.order_id') }}</span>
                  <span class="font-semibold text-gray-900">#{{ payment.id }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">{{ t('payments.confirmation_page.plan') }}</span>
                  <span class="font-semibold text-gray-900">{{ payment.subscription_plan.name }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">{{ t('payments.confirmation_page.amount') }}</span>
                  <span class="font-semibold text-gray-900">${{ payment.amount }} {{ payment.currency.toUpperCase() }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">{{ t('payments.confirmation_page.payment_method') }}</span>
                  <span class="font-semibold text-gray-900 capitalize">{{ payment.payment_method }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">{{ t('payments.confirmation_page.status') }}</span>
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                    {{ payment.status }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">{{ t('payments.confirmation_page.date') }}</span>
                  <span class="font-semibold text-gray-900">{{ formatDate(payment.created_at) }}</span>
                </div>
              </div>
            </div>

            <!-- Next Steps -->
            <div class="bg-blue-50 rounded-lg p-6 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ t('payments.confirmation_page.whats_next') }}</h3>
              <ul class="space-y-2">
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-blue-600 me-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-gray-700">{{ t('payments.confirmation_page.processing_info') }}</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-blue-600 me-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-gray-700">{{ t('payments.confirmation_page.email_confirmation') }}</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-blue-600 me-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-gray-700">{{ t('payments.confirmation_page.upgrade_info') }}</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-blue-600 me-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-gray-700">{{ t('payments.confirmation_page.support_info') }}</span>
                </li>
              </ul>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
              <PrimaryButton @click="$inertia.visit(route('dashboard'))" class="flex-1 justify-center">
                {{ t('payments.confirmation_page.go_to_dashboard') }}
              </PrimaryButton>
              <SecondaryButton @click="$inertia.visit(route('payments.index'))" class="flex-1 justify-center">
                {{ t('payments.confirmation_page.view_subscription') }}
              </SecondaryButton>
            </div>
          </div>
        </div>

        <!-- Support -->
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            {{ t('payments.confirmation_page.need_help') }}
            <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium">{{ t('payments.confirmation_page.contact_support') }}</a>
          </p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';
import SecondaryButton from '@/Components/Shared/SecondaryButton.vue';
import { useTranslations } from '@/composables/useTranslations';

const { t, locale } = useTranslations();

defineProps({
  payment: Object,
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString(locale.value, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
