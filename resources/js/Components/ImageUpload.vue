<script setup>
import { ref } from 'vue';
import InputLabel from './InputLabel.vue';

const props = defineProps({
    modelValue: {
        type: [String, Object],
        default: null
    },
    label: {
        type: String,
        default: 'Image'
    },
    id: {
        type: String,
        required: true
    },
    accept: {
        type: String,
        default: 'image/*'
    }
});

const emit = defineEmits(['update:modelValue', 'upload']);

const uploading = ref(false);
const previewUrl = ref(typeof props.modelValue === 'string' ? props.modelValue : props.modelValue?.url);

const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Show preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);

    // Emit the file for parent to handle upload
    emit('upload', file);
};

const clearImage = () => {
    previewUrl.value = null;
    emit('update:modelValue', null);
};
</script>

<template>
    <div>
        <InputLabel :for="id" :value="label" />
        
        <div v-if="previewUrl" class="mt-2 mb-3 relative inline-block">
            <img
                :src="previewUrl"
                :alt="label"
                class="h-32 w-auto rounded border border-gray-300"
            />
            <button
                @click="clearImage"
                type="button"
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <input
            :id="id"
            type="file"
            :accept="accept"
            @change="handleFileChange"
            :disabled="uploading"
            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 disabled:opacity-50"
        />
        
        <p v-if="uploading" class="mt-1 text-xs text-indigo-600">
            Uploading...
        </p>
    </div>
</template>
