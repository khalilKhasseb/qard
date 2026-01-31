<template>
  <div v-if="translationCredits.hasFeature" class="bg-gradient-to-br from-indigo-50 to-purple-50 shadow-sm rounded-lg p-6 border border-indigo-100">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
        </svg>
        <h3 class="text-lg font-semibold text-gray-900">{{ t('ai_translation') }}</h3>
      </div>
      <button
        @click="$emit('refresh-credits')"
        class="text-indigo-600 hover:text-indigo-700"
        title="Refresh credits"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
      </button>
    </div>

    <!-- Credits Display -->
    <div class="mb-4 p-3 bg-white rounded-lg border border-indigo-200">
      <div class="flex justify-between items-center mb-2">
        <span class="text-sm font-medium text-gray-700">{{ t('translation_credits') }}</span>
        <span class="text-lg font-bold text-indigo-600">
          {{ translationCredits.unlimited ? '∞' : `${translationCredits.remaining}/${translationCredits.limit}` }}
        </span>
      </div>
      <div v-if="!translationCredits.unlimited && translationCredits.usage" class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="h-2 rounded-full transition-all duration-300"
          :class="translationCredits.usage.usage_percentage > 80 ? 'bg-red-500' : 'bg-indigo-500'"
          :style="{ width: `${translationCredits.usage.usage_percentage}%` }"
        ></div>
      </div>
    </div>

    <!-- Language Selection -->
    <div class="mb-4">
      <InputLabel :value="t('translate_to')" class="mb-2" />
      <div class="space-y-2 max-h-40 overflow-y-auto">
        <label
          v-for="lang in availableTargetLanguages"
          :key="lang.code"
          class="flex items-center p-2 hover:bg-white rounded cursor-pointer transition-colors"
        >
          <input
            type="checkbox"
            :checked="selectedTargetLanguages.includes(lang.code)"
            @change="updateSelectedLanguages(lang.code, $event.target.checked)"
            :value="lang.code"
            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
          />
          <span class="ml-2 text-sm text-gray-700">{{ lang.name }} ({{ lang.code.toUpperCase() }})</span>
        </label>
      </div>
    </div>

    <!-- Translation Actions -->
    <div class="space-y-2">
      <PrimaryButton
        @click="$emit('translate')"
        :disabled="translating || selectedTargetLanguages.length === 0 || (!translationCredits.unlimited && translationCredits.remaining === 0)"
        class="w-full justify-center bg-indigo-600 hover:bg-indigo-700"
      >
        <svg v-if="translating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ translating ? t('translating') : t('translate_card') }}
      </PrimaryButton>

      <p class="text-xs text-center text-gray-600">
        {{ t('translation_info', selectedTargetLanguages.length) }}
      </p>
    </div>

    <!-- Translation Status -->
    <div v-if="translationStatus.message" class="mt-4 p-3 rounded-lg" :class="translationStatus.success ? 'bg-indigo-50 border border-indigo-200' : 'bg-red-50 border border-red-200'">
      <div class="flex items-center gap-2 mb-2" v-if="translating && translationStatus.message.includes('%')">
        <div class="flex-1 bg-indigo-200 rounded-full h-1.5">
          <div
            class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300"
            :style="{ width: extractPercentage(translationStatus.message) + '%' }"
          ></div>
        </div>
        <span class="text-xs font-semibold text-indigo-700">{{ extractPercentage(translationStatus.message) }}%</span>
      </div>
      <p class="text-sm" :class="translationStatus.success ? 'text-indigo-800' : 'text-red-800'">
        {{ translationStatus.message }}
      </p>
    </div>

    <!-- Upgrade Notice -->
    <div v-if="!translationCredits.unlimited && translationCredits.remaining === 0" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
      <p class="text-xs text-yellow-800 mb-2">{{ t('no_credits') }}</p>
      <SecondaryButton @click="$emit('upgrade')" class="w-full justify-center text-xs">
        {{ t('upgrade_plan') }}
      </SecondaryButton>
    </div>
  </div>
</template>

<script setup>
import InputLabel from '@/Components/Shared/InputLabel.vue';
import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';
import SecondaryButton from '@/Components/Shared/SecondaryButton.vue';

const props = defineProps({
  translationCredits: Object,
  availableTargetLanguages: Array,
  selectedTargetLanguages: Array,
  translating: Boolean,
  translationStatus: Object,
  t: Function,
});

const emit = defineEmits(['refresh-credits', 'update:selected-languages', 'translate', 'upgrade']);

const extractPercentage = (message) => {
  const match = message.match(/(\d+)%/);
  return match ? parseInt(match[1]) : 0;
};

const updateSelectedLanguages = (languageCode, isChecked) => {
  const currentSelection = [...props.selectedTargetLanguages];

  if (isChecked) {
    if (!currentSelection.includes(languageCode)) {
      currentSelection.push(languageCode);
    }
  } else {
    const index = currentSelection.indexOf(languageCode);
    if (index > -1) {
      currentSelection.splice(index, 1);
    }
  }

  emit('update:selected-languages', currentSelection);
};
</script>
