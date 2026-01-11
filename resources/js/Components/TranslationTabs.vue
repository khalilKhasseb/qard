<script setup>
import { ref } from 'vue';

const props = defineProps({
    languages: Array,
    translations: Object,
    currentLanguage: String
});

const activeTab = ref(props.currentLanguage);

const setActiveTab = (languageCode) => {
    activeTab.value = languageCode;
};
</script>

<template>
    <div class="translation-tabs">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button v-for="language in languages" :key="language.code"
                        @click="setActiveTab(language.code)"
                        :class="[
                            activeTab === language.code 
                                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                        :aria-current="activeTab === language.code ? 'page' : undefined">
                    {{ language.name }}
                </button>
            </nav>
        </div>

        <div class="mt-4">
            <div v-for="(translation, key) in translations" :key="key" class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ key }}</label>
                <input type="text"
                       :value="translation[activeTab] || ''"
                       @input="$emit('update:translation', { key, language: activeTab, value: $event.target.value })"
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300">
            </div>
        </div>
    </div>
</template>
