<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    icon: {
        type: String,
        required: true
    },
    color: {
        type: String,
        default: 'blue' // blue, green, purple, pink, indigo
    }
});

const colorClasses = {
    blue: 'bg-blue-500',
    green: 'bg-green-500',
    purple: 'bg-purple-500',
    pink: 'bg-pink-500',
    indigo: 'bg-indigo-500',
    yellow: 'bg-yellow-500',
    red: 'bg-red-500',
};
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div 
                class="flex-shrink-0 rounded-md p-3"
                :class="colorClasses[color]"
            >
                <div class="text-white" v-html="icon"></div>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500">{{ title }}</p>
                <p class="text-2xl font-semibold text-gray-900">{{ value }}</p>
            </div>
        </div>
    </div>
</template>
