<script setup>
const props = defineProps({
    content: String,
    placeholder: String,
    language: String
});

const emit = defineEmits(['update:content']);

const updateContent = (value) => {
    emit('update:content', value);
};
</script>

<template>
    <textarea
        :value="content"
        @input="updateContent($event.target.value)"
        class="mt-1 block w-full rounded-md border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-gray-900"
        rows="3"
        :placeholder="placeholder"
    ></textarea>
</template>
