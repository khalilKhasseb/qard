<template>
  <div class="mb-6 bg-white shadow-sm rounded-lg p-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <span class="text-sm font-medium text-gray-700">Input Language Context:</span>
      <div class="flex items-center bg-gray-100 rounded-lg p-1">
        <button
          v-for="lang in languages"
          :key="lang.code"
          @click="$emit('switch-language', lang.code)"
          :class="[
            'px-4 py-1.5 text-sm font-medium rounded-md transition-all',
            inputLanguage === lang.code
              ? 'bg-white text-indigo-600 shadow-sm'
              : 'text-gray-500 hover:text-gray-700'
          ]"
        >
          {{ lang.name }} ({{ lang.code.toUpperCase() }})
        </button>
      </div>
    </div>
    <div class="text-xs text-gray-500 max-w-xs text-right">
      Switching language here changes the context for all translatable fields below.
    </div>
  </div>
</template>

<script setup>
defineProps({
  languages: Array,
  inputLanguage: String,
});

defineEmits(['switch-language']);
</script>