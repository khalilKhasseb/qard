<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    sections: Array,
    language: String
});

const emit = defineEmits(['update:sections']);

const page = usePage();
const currentLanguage = computed(() => props.language || page.props.auth.user?.language || 'en');

const addSection = () => {
    const newSections = [...props.sections, {
        id: Date.now(),
        type: 'text',
        content: { [currentLanguage]: '' },
        order: props.sections.length + 1
    }];
    emit('update:sections', newSections);
};

const removeSection = (index) => {
    const newSections = props.sections.filter((_, i) => i !== index);
    emit('update:sections', newSections);
};

const updateSectionContent = (index, content) => {
    const newSections = [...props.sections];
    newSections[index].content = content;
    emit('update:sections', newSections);
};
</script>

<template>
    <div class="language-aware-section-builder">
        <div v-for="(section, index) in sections" :key="section.id" class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="flex justify-between items-center mb-2">
                <h4 class="font-medium text-gray-900 dark:text-white">Section {{ index + 1 }}</h4>
                <button @click="removeSection(index)" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                    Remove
                </button>
            </div>

            <div class="mb-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                <select v-model="section.type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300">
                    <option value="text">Text</option>
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                    <option value="link">Link</option>
                </select>
            </div>

            <div class="mb-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Content ({{ currentLanguage }})</label>
                <textarea v-model="section.content[currentLanguage]"
                          @input="updateSectionContent(index, section.content)"
                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300"
                          rows="3"></textarea>
            </div>
        </div>

        <button @click="addSection" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Add Section
        </button>
    </div>
</template>
