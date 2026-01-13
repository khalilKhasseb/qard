<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    languages: Array,
    currentLanguage: String
});

const showDropdown = ref(false);
const selectedLanguage = ref(props.currentLanguage);

const switchLanguage = (languageCode) => {
    router.post(route('language.switch'), {
        language_code: languageCode
    }, {
        preserveScroll: true,
        onSuccess: () => {
            selectedLanguage.value = languageCode;
            showDropdown.value = false;
        }
    });
};

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

// Close dropdown when clicking outside
const closeDropdown = (event) => {
    if (!event.target.closest('.language-selector')) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});
</script>

<template>
    <div class="language-selector relative">
        <button @click="toggleDropdown" class="flex items-center space-x-2 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span>{{ selectedLanguage.toUpperCase() }}</span>
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <div v-if="showDropdown" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
            <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                <button v-for="language in languages" :key="language.code"
                        @click="switchLanguage(language.code)"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between">
                    <span>{{ language.name }}</span>
                    <span v-if="language.code === selectedLanguage" class="text-indigo-600 dark:text-indigo-400">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.language-selector {
    min-width: 80px;
}
</style>
