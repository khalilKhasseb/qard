<template>
  <div class="mb-6 bg-indigo-50 border border-indigo-100 shadow-sm rounded-lg p-4 flex flex-col md:flex-row items-center justify-between gap-4">
    <div class="flex flex-col md:flex-row items-center gap-3">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
        </svg>
        <span class="text-sm font-bold text-indigo-900">Editing Language:</span>
      </div>
      <div class="flex flex-wrap items-center bg-white/50 backdrop-blur-sm border border-indigo-200 rounded-lg p-1">
        <div
          v-for="lang in languages"
          :key="lang.code"
          class="relative group flex items-center"
        >
          <button
            @click="$emit('switch-language', lang.code)"
            :class="[
              'px-4 py-1.5 text-sm font-semibold rounded-md transition-all whitespace-nowrap flex items-center gap-2',
              inputLanguage === lang.code
                ? 'bg-indigo-600 text-white shadow-md'
                : 'text-indigo-600 hover:bg-indigo-100 hover:text-indigo-800'
            ]"
          >
            {{ lang.name }}
            <span v-if="isActive(lang.code)" class="w-2 h-2 bg-green-400 rounded-full border border-white" title="Active on card"></span>
          </button>

          <!-- Language Activation Toggle -->
          <div class="ml-1 px-1 flex items-center">
            <input
              type="checkbox"
              :checked="isActive(lang.code)"
              :disabled="isPrimary(lang.code)"
              @change="toggleLanguage(lang.code)"
              class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 disabled:opacity-50"
              :title="isPrimary(lang.code) ? 'Primary language cannot be deactivated' : 'Toggle visibility on card'"
            />
          </div>
        </div>
      </div>
    </div>
    <div class="text-xs font-medium text-indigo-700 max-w-xs text-center md:text-right italic">
      <span class="block md:inline">✨ Pro tip:</span> Switch language to edit content for different audiences!
    </div>
  </div>
</template>

<script setup>
import {onMounted} from "vue";

const props = defineProps({
  languages: Array,
  inputLanguage: String,
  activeLanguages: Array,
  primaryLanguageId: Number,
});

onMounted(
    () => {
        console.log(props.languages)
    }
)

const emit = defineEmits(['switch-language', 'toggle-language']);

const isActive = (code) => {
  return props.activeLanguages.includes(code);
}

const isPrimary = (code) => {
  const primaryLang = props.languages.find(l => l.id === props.primaryLanguageId);
  return primaryLang?.code === code;
};

const toggleLanguage = (code) => {
  emit('toggle-language', code);
};
</script>
