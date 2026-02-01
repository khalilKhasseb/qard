<template>
  <div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-gray-100">
      <h3 class="text-lg font-semibold text-gray-900">{{ t('publishing.title') }}</h3>
    </div>

    <div class="p-5 space-y-5">
      <!-- Status Badge -->
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-600">{{ t('publishing.status') }}</span>
        <div class="flex items-center gap-2">
          <span
            :class="card.is_published
              ? 'bg-green-100 text-green-700 ring-1 ring-green-600/20'
              : 'bg-gray-100 text-gray-600 ring-1 ring-gray-500/20'"
            class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full"
          >
            <span
              :class="card.is_published ? 'bg-green-500' : 'bg-gray-400'"
              class="w-1.5 h-1.5 rounded-full me-1.5"
            ></span>
            {{ card.is_published ? t('publishing.published') : t('publishing.draft') }}
          </span>
        </div>
      </div>

      <!-- Draft Changes Alert -->
      <div v-if="hasDraftChanges" class="rounded-lg border border-amber-200 bg-amber-50 overflow-hidden">
        <!-- Alert Header -->
        <div class="px-4 py-3 bg-amber-100/50 border-b border-amber-200">
          <div class="flex items-center gap-2">
            <div class="flex-shrink-0">
              <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-amber-800">{{ t('publishing.unpublished_changes') }}</p>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-amber-200 text-amber-800 rounded">
              {{ t('publishing.pending_changes') }}
            </span>
          </div>
        </div>

        <!-- Alert Body -->
        <div class="px-4 py-3 space-y-3">
          <p class="text-xs text-amber-700 leading-relaxed">
            {{ t('publishing.unpublished_changes_hint') }}
          </p>

          <!-- Changed Fields -->
          <div v-if="draftFields.length > 0">
            <p class="text-xs font-medium text-amber-700 mb-2">{{ t('publishing.changed_fields') }}</p>
            <div class="flex flex-wrap gap-1.5">
              <span
                v-for="field in draftFields"
                :key="field"
                class="inline-flex items-center px-2 py-0.5 text-xs bg-amber-200/70 text-amber-800 rounded"
              >
                {{ getFieldLabel(field) }}
              </span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-2 pt-2">
            <button
              @click="$emit('publish-draft')"
              class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ t('publishing.publish_changes') }}
            </button>
            <button
              @click="$emit('discard-draft')"
              class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-amber-700 bg-amber-100 hover:bg-amber-200 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
              :title="t('publishing.discard_changes')"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Publish/Unpublish Button -->
      <button
        @click="$emit('toggle-publish')"
        :class="card.is_published
          ? 'bg-gray-100 text-gray-700 hover:bg-gray-200 ring-1 ring-gray-200'
          : 'bg-indigo-600 text-white hover:bg-indigo-700'"
        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
      >
        <svg v-if="!card.is_published" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
        </svg>
        {{ card.is_published ? t('publishing.unpublish_card') : t('publishing.publish_card') }}
      </button>

      <!-- Divider -->
      <div class="border-t border-gray-100"></div>

      <!-- Delete Button -->
      <button
        @click="$emit('delete-card')"
        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        {{ t('publishing.delete_card') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  card: Object,
  draftFields: {
    type: Array,
    default: () => [],
  },
  t: Function,
});

defineEmits(['toggle-publish', 'publish-draft', 'discard-draft', 'delete-card']);

const hasDraftChanges = computed(() => {
  return props.card.draft_data !== null && Object.keys(props.card.draft_data || {}).length > 0;
});

const getFieldLabel = (field) => {
  // Try to get translated label
  const translated = props.t(`publishing.field_labels.${field}`);
  // If translation key is returned as-is, format the field name
  if (translated === `publishing.field_labels.${field}`) {
    return field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
  }
  return translated;
};
</script>
