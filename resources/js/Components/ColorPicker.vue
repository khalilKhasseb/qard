<script setup>
import { ref, watch } from 'vue';
import InputLabel from './InputLabel.vue';
import TextInput from './TextInput.vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '#000000'
    },
    label: {
        type: String,
        default: 'Color'
    },
    id: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['update:modelValue']);

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
        <div class="mt-1 flex gap-2">
            <input
                :id="id"
                v-model="localValue"
                type="color"
                class="h-10 w-16 rounded border border-gray-300 cursor-pointer"
            />
            <TextInput
                v-model="localValue"
                type="text"
                class="flex-1"
                placeholder="#000000"
            />
        </div>
    </div>
</template>
