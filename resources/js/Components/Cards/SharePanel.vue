<template>
  <div class="bg-white shadow-sm rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('share') }}</h3>
    <div class="space-y-3">
      <div>
        <InputLabel :value="t('public_url')" />
        <div class="mt-1 flex rounded-md shadow-sm">
          <input
            type="text"
            :value="publicUrl"
            readonly
            class="flex-1 rounded-l-md border-gray-300 bg-gray-50 text-sm"
          />
          <button
            @click="$emit('copy-url')"
            class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 hover:bg-gray-100"
          >
            {{ t('copy') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import InputLabel from '@/Components/InputLabel.vue';

defineProps({
  publicUrl: String,
  t: Function,
});

defineEmits(['copy-url']);
</script>