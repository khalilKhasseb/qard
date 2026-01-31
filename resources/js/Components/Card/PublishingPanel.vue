<template>
  <div class="bg-white shadow-sm rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('publishing') }}</h3>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-700">{{ t('status') }}</span>
        <div class="flex items-center gap-2">
          <span
            :class="card.is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
            class="px-2 py-1 text-xs font-medium rounded-full"
          >
            {{ card.is_published ? t('published') : t('draft') }}
          </span>
          <span
            v-if="hasDraftChanges"
            class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800"
            title="You have unpublished draft changes"
          >
            Unsaved Draft
          </span>
        </div>
      </div>

      <div v-if="hasDraftChanges" class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
        <p class="text-sm text-yellow-800 mb-3">You have unpublished changes in draft. These are not visible on the live card.</p>
        <PrimaryButton @click="$emit('publish-draft')" class="w-full justify-center bg-yellow-600 hover:bg-yellow-700">
          Publish Changes
        </PrimaryButton>
      </div>

      <PrimaryButton @click="$emit('toggle-publish')" class="w-full justify-center">
        {{ card.is_published ? t('unpublish') : t('publish') }}
      </PrimaryButton>

      <div class="pt-4 mt-4 border-t border-gray-100">
        <button
          @click="$emit('delete-card')"
          class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Delete Card
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';

const props = defineProps({
  card: Object,
  t: Function,
});

defineEmits(['toggle-publish', 'publish-draft', 'delete-card']);

const hasDraftChanges = computed(() => {
  return props.card.draft_data !== null && Object.keys(props.card.draft_data || {}).length > 0;
});
</script>
