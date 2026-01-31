<script setup>
const props = defineProps({
    item: Object,
    sectionType: String,
    translations: Object
});

const emit = defineEmits(['update:item']);

const updateField = (field, value) => {
    emit('update:item', { ...props.item, [field]: value });
};
</script>

<template>
    <div class="space-y-2">
        <input
            :value="item.name"
            @input="updateField('name', $event.target.value)"
            type="text"
            :placeholder="translations.name"
            class="block w-full text-sm border border-gray-300 rounded bg-white px-3 py-2 text-gray-900"
        />
        <input
            v-if="sectionType === 'products'"
            :value="item.price"
            @input="updateField('price', $event.target.value)"
            type="text"
            :placeholder="translations.price"
            class="block w-full text-sm border border-gray-300 rounded bg-white px-3 py-2 text-gray-900"
        />
        <textarea
            :value="item.description"
            @input="updateField('description', $event.target.value)"
            :placeholder="translations.description"
            class="block w-full text-sm border border-gray-300 rounded bg-white px-3 py-2 text-gray-900"
            rows="2"
        ></textarea>
    </div>
</template>
