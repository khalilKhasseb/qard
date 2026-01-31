<template>
  <div class="bg-white shadow-sm rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold text-gray-900">{{ t('card_sections') }}</h3>
      <div class="flex items-center gap-4">
        <PrimaryButton @click="$emit('save-sections')" :disabled="savingSections">
          {{ savingSections ? t('saving') : t('save_sections') }}
        </PrimaryButton>
      </div>
    </div>
    <LanguageAwareSectionBuilder
      :model-value="sections"
      @update:model-value="$emit('update:sections', $event)"
      :language="inputLanguage"
    />
  </div>
</template>

<script setup>
import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';
import LanguageAwareSectionBuilder from '@/Components/Section/LanguageAwareSectionBuilder.vue';

defineProps({
  sections: Array,
  inputLanguage: String,
  savingSections: Boolean,
  t: Function,
});

defineEmits(['save-sections', 'update:sections']);
</script>