<template>
  <AuthenticatedLayout>
    <Head :title="t('payments.subscription.my_subscription')" />

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Success/Error Messages -->
        <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
          {{ $page.props.flash.error }}
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
          <p class="mt-4 text-gray-600">{{ t('payments.subscription.loading') }}</p>
        </div>

        <!-- Subscription Details -->
        <div v-else-if="subscription && subscription.data" class="space-y-6">
          <!-- Current Subscription Card -->
          <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="bg-indigo-600 px-6 py-4 text-white">
              <div class="flex justify-between items-center">
                <div>
                  <h2 class="text-xl font-bold">{{ t('payments.subscription.details') }}</h2>
                  <p class="text-indigo-100 text-sm">{{ t('payments.subscription.details_subtitle') }}</p>
                </div>
                <span
                  class="px-3 py-1 rounded-full text-sm font-medium"
                  :class="{
                    'bg-green-500 text-white': subscription.data.status === 'active',
                    'bg-yellow-500 text-white': subscription.data.status === 'pending',
                    'bg-red-500 text-white': subscription.data.status === 'canceled',
                    'bg-gray-500 text-white': subscription.data.status === 'expired',
                  }"
                >
                  {{ subscription.data.status }}
                </span>
              </div>
            </div>

            <div class="p-6">
              <!-- Plan Information -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('payments.subscription.plan') }}</label>
                  <p class="text-lg font-semibold text-gray-900">
                    {{ subscription.data.plan?.name || 'N/A' }}
                  </p>
                  <p class="text-sm text-gray-600">
                    {{ subscription.data.plan?.description }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('payments.subscription.price') }}</label>
                  <p class="text-lg font-semibold text-gray-900">
                    ${{ subscription.data.plan?.price }}/{{ subscription.data.plan?.billing_cycle }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('payments.subscription.status') }}</label>
                  <p class="text-gray-900 capitalize">
                    {{ subscription.data.status }}
                    <span v-if="subscription.data.is_active" class="text-green-600 ms-1">✓</span>
                  </p>
                </div>

                <div v-if="subscription.data.days_remaining">
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('payments.subscription.days_remaining') }}</label>
                  <p class="text-gray-900">
                    {{ t('payments.subscription.days', { count: subscription.data.days_remaining }) }}
                  </p>
                </div>

                <div v-if="subscription.data.starts_at">
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('payments.subscription.start_date') }}</label>
                  <p class="text-gray-900">
                    {{ formatDate(subscription.data.starts_at) }}
                  </p>
                </div>

                <div v-if="subscription.data.ends_at">
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('payments.subscription.renews_on') }}</label>
                  <p class="text-gray-900">
                    {{ formatDate(subscription.data.ends_at) }}
                  </p>
                </div>

                <div v-if="subscription.data.canceled_at">
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('payments.subscription.canceled_on') }}</label>
                  <p class="text-gray-900">
                    {{ formatDate(subscription.data.canceled_at) }}
                  </p>
                </div>

                <div v-if="subscription.data.trial_ends_at && subscription.data.is_trial">
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('payments.subscription.trial_ends') }}</label>
                  <p class="text-gray-900">
                    {{ formatDate(subscription.data.trial_ends_at) }}
                  </p>
                </div>

                <!-- Usage Stats -->
                <div class="col-span-1 md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('payments.subscription.usage') }}</label>
                  <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                    <!-- Cards Usage -->
                    <div class="flex justify-between items-center">
                      <div>
                        <p class="font-medium text-gray-900">{{ t('payments.subscription.business_cards', { count: '' }).replace(':', '') }}</p>
                        <p class="text-sm text-gray-600">
                          {{ t('payments.subscription.of_used', { count: usageStats.cardCount, limit: subscription.data.plan?.card_limit }) }}
                        </p>
                      </div>
                      <div class="flex items-center gap-2">
                        <span
                          class="px-2 py-1 text-xs font-medium rounded-full"
                          :class="usageStats.cardCount >= subscription.data.plan?.card_limit ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                        >
                          {{ t('payments.subscription.remaining', { count: subscription.data.plan?.card_limit - usageStats.cardCount }) }}
                        </span>
                      </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div
                        class="h-2 rounded-full transition-all"
                        :class="usageStats.cardCount >= subscription.data.plan?.card_limit ? 'bg-red-500' : 'bg-green-500'"
                        :style="`width: ${(usageStats.cardCount / subscription.data.plan?.card_limit) * 100}%`"
                      ></div>
                    </div>

                    <!-- Themes Usage -->
                    <div class="flex justify-between items-center mt-3">
                      <div>
                        <p class="font-medium text-gray-900">{{ t('payments.subscription.custom_themes', { count: '' }).replace(':', '') }}</p>
                        <p class="text-sm text-gray-600">
                          {{ t('payments.subscription.of_used', { count: usageStats.themeCount, limit: subscription.data.plan?.theme_limit }) }}
                        </p>
                      </div>
                      <span
                        class="px-2 py-1 text-xs font-medium rounded-full"
                        :class="usageStats.themeCount >= subscription.data.plan?.theme_limit ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                      >
                        {{ t('payments.subscription.remaining', { count: subscription.data.plan?.theme_limit - usageStats.themeCount }) }}
                      </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div
                        class="h-2 rounded-full bg-green-500 transition-all"
                        :style="`width: ${(usageStats.themeCount / subscription.data.plan?.theme_limit) * 100}%`"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Plan Features -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('payments.subscription.included_features') }}</label>
                <div v-if="subscription.data.plan?.features && Array.isArray(subscription.data.plan.features)" class="grid grid-cols-1 md:grid-cols-2 gap-2">
                  <div
                    v-for="feature in subscription.data.plan.features"
                    :key="feature"
                    class="flex items-center text-sm text-gray-700"
                  >
                    <svg class="w-4 h-4 text-green-500 me-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ feature }}
                  </div>
                </div>
                <div v-else class="bg-gray-50 rounded-lg p-4 text-center">
                  <p class="text-gray-600 text-sm">
                    <span v-if="subscription.data.status === 'free'">
                      {{ t('payments.subscription.no_premium_features') }}
                    </span>
                    <span v-else>
                      {{ t('payments.subscription.no_additional_features') }}
                    </span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('payments.subscription.actions') }}</h3>

            <div class="flex gap-4 flex-wrap">
              <!-- Upgrade Button (if active) -->
              <button
                v-if="subscription.data.status === 'active' && !subscription.data.is_trial"
                @click="upgradePlan"
                class="bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition"
              >
                {{ t('payments.subscription.upgrade_plan') }}
              </button>

              <!-- Renew Button (if expired or canceled) -->
              <button
                v-if="subscription.data.status === 'expired' || subscription.data.status === 'canceled'"
                @click="renewSubscription"
                class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition"
              >
                {{ t('payments.subscription.renew') }}
              </button>

              <!-- Sync Button -->
              <button
                @click="syncSubscription"
                class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition"
                :disabled="syncing"
              >
                {{ syncing ? t('payments.subscription.syncing') : t('payments.subscription.refresh') }}
              </button>

              <!-- Cancel Button (if active) -->
              <button
                v-if="subscription.data.status === 'active' && !subscription.data.is_trial"
                @click="cancelSubscription"
                class="bg-red-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-700 transition"
              >
                {{ t('payments.subscription.cancel') }}
              </button>

              <!-- View All Plans -->
              <button
                @click="$inertia.visit(route('payments.index'))"
                class="bg-indigo-100 text-indigo-800 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-200 transition"
              >
                {{ t('payments.subscription.view_all_plans') }}
              </button>
            </div>
          </div>

          <!-- Upgrade Available Section -->
          <div v-if="availablePlans.length > 0" class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('payments.subscription.upgrade_your_plan') }}</h3>
            <p class="text-gray-600 mb-4">{{ t('payments.subscription.upgrade_prompt', { count: usageStats.cardCount, limit: subscription.data.plan?.cards_limit }) }}</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div
                v-for="plan in availablePlans"
                :key="plan.id"
                class="border-2 rounded-lg p-4 transition-colors hover:border-indigo-500"
                :class="plan.id === subscription.data.plan?.id ? 'border-green-500 bg-green-50' : 'border-gray-200'"
              >
                <div class="flex justify-between items-start mb-2">
                  <h4 class="font-bold text-gray-900">{{ plan.name }}</h4>
                  <span
                    v-if="plan.id === subscription.data.plan?.id"
                    class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium"
                  >
                    {{ t('payments.subscription.current') }}
                  </span>
                </div>
                <p class="text-2xl font-bold text-gray-900 mb-2">${{ plan.price }}<span class="text-sm text-gray-500">/{{ plan.billing_cycle }}</span></p>
                <p class="text-sm text-gray-600 mb-3">{{ t('payments.subscription.cards_themes', { cards: plan.cards_limit, themes: plan.themes_limit }) }}</p>
                <button
                  v-if="plan.id !== subscription.data.plan?.id"
                  @click="upgradeToPlan(plan)"
                  class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-indigo-700 transition"
                >
                  {{ t('payments.subscription.upgrade') }}
                </button>
                <button
                  v-else
                  @click="syncSubscription"
                  class="w-full bg-gray-200 text-gray-800 px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-300 transition"
                  :disabled="syncing"
                >
                  {{ syncing ? t('payments.subscription.syncing') : t('payments.subscription.sync_plan') }}
                </button>
              </div>
            </div>
          </div>

          <!-- Payment History -->
          <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('payments.history.title') }}</h3>
            <button
              @click="$inertia.visit(route('payments.index'))"
              class="text-indigo-600 hover:text-indigo-800 font-medium"
            >
              {{ t('payments.history.title') }} →
            </button>
          </div>
        </div>

        <!-- No Subscription -->
        <div v-else class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ t('payments.subscription.no_subscription_title') }}</h3>
            <p class="text-gray-600 mb-6 max-w-md mx-auto">
              {{ t('payments.subscription.no_subscription_desc') }}
            </p>
            <div class="flex gap-4 justify-center">
              <button
                @click="$inertia.visit(route('payments.index'))"
                class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition"
              >
                {{ t('payments.subscription.choose_plan') }}
              </button>
              <button
                @click="$inertia.visit(route('dashboard'))"
                class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition"
              >
                {{ t('payments.subscription.go_to_dashboard') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
          <div class="flex items-center">
            <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <span class="text-red-700">{{ error }}</span>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>

import { ref, onMounted, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import { useTranslations } from '@/composables/useTranslations';

const { t, locale } = useTranslations();
const page = usePage();

const props = defineProps({
  availablePlans: {
    type: Array,
    default: () => [],
  },
});

const subscription = ref(null);
const loading = ref(true);
const error = ref(null);
const syncing = ref(false);
const cardCount = ref(0);
const themeCount = ref(0);

const usageStats = computed(() => ({
  cardCount: cardCount.value,
  themeCount: themeCount.value,
}));

const loadSubscription = async () => {
  loading.value = true;
  error.value = null;

  try {
    // Get the XSRF token from cookies (Laravel Sanctum session auth)
    const xsrfToken = document.cookie
      .split('; ')
      .find(row => row.startsWith('XSRF-TOKEN='))
      ?.split('=')[1];

    // Decode the token if it's URL encoded
    const csrfToken = xsrfToken ? decodeURIComponent(xsrfToken) : null;
    
    // Load subscription data
    // await axios.get('/sanctum/csrf-cookie');

    // const t =await  axios.get('/api/subscription',{
    //    withCredentials: true,
    // });
    //   console.log(t);

    //   return ;
    const response = await fetch('/api/subscription', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...(csrfToken && { 'X-XSRF-TOKEN': csrfToken }),
      },
    });

    if (!response.ok) {
      throw new Error('Failed to load subscription');
    }

    const data = await response.json();
    
    if (data.data) {
      console.log(data);
      subscription.value = data;
    }

    // Load usage stats
    const statsResponse = await fetch('/api/usage', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...(csrfToken && { 'X-XSRF-TOKEN': csrfToken }),
      },
    });

    if (statsResponse.ok) {
      const statsData = await statsResponse.json();
      cardCount.value = statsData.cardCount || 0;
      themeCount.value = statsData.themeCount || 0;
    }
  } catch (err) {
    error.value = t('payments.subscription.load_error');
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const syncSubscription = async () => {
  syncing.value = true;
  error.value = null;

  try {
    const xsrfToken = document.cookie
      .split('; ')
      .find(row => row.startsWith('XSRF-TOKEN='))
      ?.split('=')[1];
    const csrfToken = xsrfToken ? decodeURIComponent(xsrfToken) : null;
    
    const response = await fetch('/api/subscription/sync', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...(csrfToken && { 'X-XSRF-TOKEN': csrfToken }),
      },
    });

    if (!response.ok) {
      throw new Error('Failed to sync subscription');
    }

    const data = await response.json();
    
    if (data.data) {
      subscription.value = data;
    }

    // Show success message
    alert(t('payments.subscription.sync_success'));
  } catch (err) {
    error.value = t('payments.subscription.sync_error');
    console.error(err);
  } finally {
    syncing.value = false;
  }
};

const cancelSubscription = async () => {
  if (!confirm(t('payments.subscription.cancel_confirm'))) {
    return;
  }

  try {
    const xsrfToken = document.cookie
      .split('; ')
      .find(row => row.startsWith('XSRF-TOKEN='))
      ?.split('=')[1];
    const csrfToken = xsrfToken ? decodeURIComponent(xsrfToken) : null;
    
    const response = await fetch('/api/subscription/cancel', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...(csrfToken && { 'X-XSRF-TOKEN': csrfToken }),
      },
    });

    if (!response.ok) {
      const data = await response.json();
      throw new Error(data.message || 'Failed to cancel subscription');
    }

    const data = await response.json();
    subscription.value = data;

    alert(t('payments.subscription.cancel_success'));
  } catch (err) {
    error.value = err.message;
    console.error(err);
  }
};

const upgradePlan = () => {
  router.visit(route('payments.index'));
};

const renewSubscription = () => {
  router.visit(route('payments.index'));
};

const upgradeToPlan = (plan) => {
  router.visit(route('payments.checkout', plan.id));
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const localeCode = locale.value === 'ar' ? 'ar-SA' : locale.value === 'he' ? 'he-IL' : 'en-US';
  return new Date(dateString).toLocaleDateString(localeCode, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

onMounted(() => {

  loadSubscription();
});
</script>
