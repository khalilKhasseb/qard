<script setup>
const props = defineProps({
    modelValue: {
        type: String,
        default: 'desktop'
    }
});

const emit = defineEmits(['update:modelValue']);

const setDevice = (device) => {
    emit('update:modelValue', device);
};
</script>

<template>
    <div class="inline-flex rounded-lg border border-gray-300 p-1 bg-gray-50">
        <button
            @click="setDevice('desktop')"
            :class="modelValue === 'desktop' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-600 hover:text-gray-900'"
            class="px-4 py-2 text-sm font-medium rounded-md transition-all flex items-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Desktop
        </button>
        <button
            @click="setDevice('mobile')"
            :class="modelValue === 'mobile' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-600 hover:text-gray-900'"
            class="px-4 py-2 text-sm font-medium rounded-md transition-all flex items-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            Mobile
        </button>
    </div>
</template>
