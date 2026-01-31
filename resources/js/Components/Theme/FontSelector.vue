<script setup>
import { ref, watch } from 'vue';
import InputLabel from './InputLabel.vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: 'Inter'
    },
    label: {
        type: String,
        default: 'Font'
    },
    id: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['update:modelValue']);

const fontOptions = [
    'Inter',
    'Roboto',
    'Open Sans',
    'Lato',
    'Montserrat',
    'Playfair Display',
    'Merriweather',
    'Raleway',
    'Poppins',
    'Nunito',
    'Ubuntu',
    'PT Sans',
    'Source Sans Pro',
    'Fira Sans',
];

const localValue = ref(props.modelValue);

watch(localValue, (newValue) => {
    emit('update:modelValue', newValue);
});

watch(() => props.modelValue, (newValue) => {
    localValue.value = newValue;
});
</script>

<template>
    <div>
        <InputLabel :for="id" :value="label" />
        <select
            :id="id"
            v-model="localValue"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
            <option v-for="font in fontOptions" :key="font" :value="font">
                {{ font }}
            </option>
        </select>
    </div>
</template>
