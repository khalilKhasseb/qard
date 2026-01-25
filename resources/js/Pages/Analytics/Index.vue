<template>
  <AuthenticatedLayout>
    <Head title="Analytics" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Analytics Dashboard</h2>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Views</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_views }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">NFC Taps</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats.nfc_taps }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">QR Scans</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats.qr_scans }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Shares</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats.shares }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Card Performance -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Card Performance</h3>
          </div>
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Card
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Views
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Shares
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="card in cardAnalytics" :key="card.id">
                    <td class="px-4 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ card.title.en }}</div>
                      <div class="text-sm text-gray-500">{{ card.subtitle.en }}</div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ card.views_count }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ card.shares_count }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                      <span
                        :class="card.is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                        class="px-2 py-1 text-xs font-medium rounded-full"
                      >
                        {{ card.is_published ? 'Published' : 'Draft' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Event Timeline -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div
                v-for="event in recentEvents"
                :key="event.id"
                class="flex items-start space-x-3 text-sm"
              >
                <div
                  :class="getEventColor(event.event_type)"
                  class="flex-shrink-0 w-2 h-2 mt-2 rounded-full"
                ></div>
                <div class="flex-1">
                  <p class="text-gray-900">
                    <span class="font-medium">{{ getEventLabel(event.event_type) }}</span>
                    on {{ event.card_title }}
                  </p>
                  <p class="text-gray-500 text-xs">{{ formatDate(event.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
  stats: Object,
  cardAnalytics: Array,
  recentEvents: Array,
});

const getEventColor = (type) => {
  const colors = {
    view: 'bg-blue-500',
    nfc_tap: 'bg-green-500',
    qr_scan: 'bg-purple-500',
    social_share: 'bg-pink-500',
    section_click: 'bg-yellow-500',
  };
  return colors[type] || 'bg-gray-500';
};

const getEventLabel = (type) => {
  const labels = {
    view: 'View',
    nfc_tap: 'NFC Tap',
    qr_scan: 'QR Scan',
    social_share: 'Share',
    section_click: 'Section Click',
  };
  return labels[type] || type;
};

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};
</script>
