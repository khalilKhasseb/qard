<script setup>
const props = defineProps({
    content: Object,
    translations: Object
});

const emit = defineEmits(['update:content']);

const updateField = (field, value) => {
    const updated = { ...props.content };
    updated[field] = value;
    emit('update:content', updated);
};
</script>

<template>
    <div class="space-y-3">
        <div>
            <label class="text-xs font-medium text-gray-600 uppercase">{{ translations.booking_url }}</label>
            <input
                :value="content.booking_url"
                @input="updateField('booking_url', $event.target.value)"
                type="text"
                class="mt-1 block w-full rounded-md border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-gray-900"
            />
        </div>
        <div>
            <label class="text-xs font-medium text-gray-600 uppercase">{{ translations.instructions }}</label>
            <textarea
                :value="content.instructions"
                @input="updateField('instructions', $event.target.value)"
                class="mt-1 block w-full rounded-md border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-gray-900"
                rows="2"
            ></textarea>
        </div>
    </div>
</template>
