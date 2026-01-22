<script setup>
const props = defineProps({
    content: Object
});

const emit = defineEmits(['update:content']);

const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

const updateDay = (day, value) => {
    const updated = { ...props.content };
    updated[day] = value;
    emit('update:content', updated);
};
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div v-for="day in days" :key="day">
            <label class="text-xs font-medium text-gray-600 uppercase">{{ day }}</label>
            <input
                :value="content[day]"
                @input="updateDay(day, $event.target.value)"
                type="text"
                class="mt-1 block w-full rounded-md border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-gray-900"
                placeholder="9:00 AM - 5:00 PM"
            />
        </div>
    </div>
</template>
