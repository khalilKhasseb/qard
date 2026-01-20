<template>
  <AuthenticatedLayout>
    <Head title="My Subscription" />

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
          <p class="mt-4 text-gray-600">Loading subscription details...</p>
        </div>

        <!-- Subscription Details -->
        <div v-else-if="subscription && subscription.data" class="space-y-6">
          <!-- Current Subscription Card -->
          <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="bg-indigo-600 px-6 py-4 text-white">
              <div class="flex justify-between items-center">
                <div>
                  <h2 class="text-xl font-bold">Subscription Details</h2>
                  <p class="text-indigo-100 text-sm">Your current plan and billing information</p>
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
                  <label class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
                  <p class="text-lg font-semibold text-gray-900">
                    {{ subscription.data.plan?.name || 'N/A' }}
                  </p>
                  <p class="text-sm text-gray-600">
                    {{ subscription.data.plan?.description }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                  <p class="text-lg font-semibold text-gray-900">
                    ${{ subscription.data.plan?.price }}/{{ subscription.data.plan?.billing_cycle }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <p class="text-gray-900 capitalize">
                    {{ subscription.data.status }}
                    <span v-if="subscription.data.is_active" class="text-green-600 ml-1">✓</span>
                  </p>
                </div>

                <div v-if="subscription.data.days_remaining">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Days Remaining</label>
                  <p class="text-gray-900">
                    {{ subscription.data.days_remaining }} days
                  </p>
                </div>

                <div v-if="subscription.data.starts_at">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                  <p class="text-gray-900">
                    {{ formatDate(subscription.data.starts_at) }}
                  </p>
                </div>

                <div v-if="subscription.data.ends_at">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Renews On</label>
                  <p class="text-gray-900">
                    {{ formatDate(subscription.data.ends_at) }}
                  </p>
                </div>

                <div v-if="subscription.data.canceled_at">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Canceled On</label>
                  <p class="text-gray-900">
                    {{ formatDate(subscription.data.canceled_at) }}
                  </p>
                </div>

                <div v-if="subscription.data.trial_ends_at && subscription.data.is_trial">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Trial Ends</label>
                  <p class="text-gray-900">
                    {{ formatDate(subscription.data.trial_ends_at) }}
                  </p>
                </div>
              </div>

              <!-- Plan Features -->
              <div v-if="subscription.data.plan?.features" class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Included Features</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                  <div 
                    v-for="feature in subscription.data.plan.features" 
                    :key="feature"
                    class="flex items-center text-sm text-gray-700"
                  >
                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ feature }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
            
            <div class="flex gap-4 flex-wrap">
              <!-- Upgrade Button (if active) -->
              <button 
                v-if="subscription.data.status === 'active' && !subscription.data.is_trial"
                @click="upgradePlan"
                class="bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition"
              >
                Upgrade Plan
              </button>

              <!-- Renew Button (if expired or canceled) -->
              <button 
                v-if="subscription.data.status === 'expired' || subscription.data.status === 'canceled'"
                @click="renewSubscription"
                class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition"
              >
                Renew Subscription
              </button>

              <!-- Sync Button -->
              <button 
                @click="syncSubscription"
                class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition"
                :disabled="syncing"
              >
                {{ syncing ? 'Syncing...' : 'Refresh Details' }}
              </button>

              <!-- Cancel Button (if active) -->
              <button 
                v-if="subscription.data.status === 'active' && !subscription.data.is_trial"
                @click="cancelSubscription"
                class="bg-red-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-700 transition"
              >
                Cancel Subscription
              </button>

              <!-- View All Plans -->
              <button 
                @click="$inertia.visit(route('payments.index'))"
                class="bg-indigo-100 text-indigo-800 px-6 py-2 rounded-lg font-semibold hover:bg-indigo-200 transition"
              >
                View All Plans
              </button>
            </div>
          </div>

          <!-- Payment History -->
          <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment History</h3>
            <button 
              @click="$inertia.visit(route('payments.index'))"
              class="text-indigo-600 hover:text-indigo-800 font-medium"
            >
              View Payment History →
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
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Active Subscription</h3>
            <p class="text-gray-600 mb-6 max-w-md mx-auto">
              You don't have an active subscription. Upgrade your account to unlock more features and create more cards.
            </p>
            <div class="flex gap-4 justify-center">
              <button 
                @click="$inertia.visit(route('payments.index'))"
                class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition"
              >
                Choose a Plan
              </button>
              <button 
                @click="$inertia.visit(route('dashboard'))"
                class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition"
              >
                Go to Dashboard
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
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const subscription = ref(null);
const loading = ref(true);
const error = ref(null);
const syncing = ref(false);

const loadSubscription = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await fetch('/api/subscription', {
      headers: {
        'Accept': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Failed to load subscription');
    }

    const data = await response.json();
    
    if (data.data) {
      subscription.value = data;
    }
  } catch (err) {
    error.value = 'Failed to load subscription details. Please try again.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const syncSubscription = async () => {
  syncing.value = true;
  error.value = null;

  try {
    const response = await fetch('/api/subscription/sync', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
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
    alert('Subscription details refreshed successfully!');
  } catch (err) {
    error.value = 'Failed to refresh subscription details. Please try again.';
    console.error(err);
  } finally {
    syncing.value = false;
  }
};

const cancelSubscription = async () => {
  if (!confirm('Are you sure you want to cancel your subscription? This action cannot be undone.')) {
    return;
  }

  try {
    const response = await fetch('/api/subscription/cancel', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
      },
    });

    if (!response.ok) {
      const data = await response.json();
      throw new Error(data.message || 'Failed to cancel subscription');
    }

    const data = await response.json();
    subscription.value = data;

    alert('Your subscription has been canceled. You can continue using your current plan until the end of the billing period.');
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

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

onMounted(() => {
  loadSubscription();
});
</script>
