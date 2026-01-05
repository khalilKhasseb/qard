<script setup>
defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    rows: {
        type: Number,
        default: 3
    }
});

const emit = defineEmits(['update:modelValue']);

const updateValue = (event) => {
    emit('update:modelValue', event.target.value);
};
</script>

<template>
    <textarea
        :rows="rows"
        :value="modelValue"
        @input="updateValue"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
    />
</template>
