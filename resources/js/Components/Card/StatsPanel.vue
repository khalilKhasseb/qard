<template>
  <div class="bg-white shadow-sm rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('stats') }}</h3>
    <div class="space-y-3">
      <div class="flex justify-between">
        <span class="text-sm text-gray-600">{{ t('views') }}</span>
        <span class="text-sm font-medium text-gray-900">{{ card.views_count }}</span>
      </div>
      <div class="flex justify-between">
        <span class="text-sm text-gray-600">{{ t('shares') }}</span>
        <span class="text-sm font-medium text-gray-900">{{ card.shares_count }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  card: Object,
  t: Function,
});
</script>