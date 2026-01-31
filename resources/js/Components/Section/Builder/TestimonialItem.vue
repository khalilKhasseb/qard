<script setup>
const props = defineProps({
    item: Object,
    translations: Object
});

const emit = defineEmits(['update:item']);

const updateField = (field, value) => {
    emit('update:item', { ...props.item, [field]: value });
};
</script>

<template>
    <div class="space-y-2">
        <textarea
            :value="item.quote"
            @input="updateField('quote', $event.target.value)"
            :placeholder="translations.quote"
            class="block w-full text-sm border border-gray-300 rounded bg-white px-3 py-2 text-gray-900"
            rows="2"
        ></textarea>
        <input
            :value="item.author"
            @input="updateField('author', $event.target.value)"
            type="text"
            :placeholder="translations.author"
            class="block w-full text-sm border border-gray-300 rounded bg-white px-3 py-2 text-gray-900"
        />
        <input
            :value="item.company"
            @input="updateField('company', $event.target.value)"
            type="text"
            :placeholder="translations.company"
            class="block w-full text-sm border border-gray-300 rounded bg-white px-3 py-2 text-gray-900"
        />
    </div>
</template>
