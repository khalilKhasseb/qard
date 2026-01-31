<script setup>
import GalleryItem from './GalleryItem.vue';
import ServiceProductItem from './ServiceProductItem.vue';
import TestimonialItem from './TestimonialItem.vue';

const props = defineProps({
    items: Array,
    sectionType: String,
    translations: Object,
    sectionId: Number
});

const emit = defineEmits(['update:items', 'add-item', 'remove-item']);

const removeItem = (index) => {
    emit('remove-item', index);
};

const addItem = () => {
    emit('add-item');
};

const updateItem = (index, updatedItem) => {
    const newItems = [...props.items];
    newItems[index] = updatedItem;
    emit('update:items', newItems);
};
</script>

<template>
    <div class="space-y-4">
        <div v-for="(item, itemIdx) in items" :key="itemIdx" class="p-3 border border-gray-300 rounded bg-white shadow-sm relative">
            <button @click="removeItem(itemIdx)" class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <ServiceProductItem
                v-if="sectionType === 'services' || sectionType === 'products'"
                :item="item"
                :section-type="sectionType"
                :translations="translations"
                @update:item="updateItem(itemIdx, $event)"
            />

            <TestimonialItem
                v-else-if="sectionType === 'testimonials'"
                :item="item"
                :translations="translations"
                @update:item="updateItem(itemIdx, $event)"
            />

            <GalleryItem
                v-else-if="sectionType === 'gallery'"
                :item="item"
                :translations="translations"
                :section-id="sectionId"
                @update:item="updateItem(itemIdx, $event)"
            />
        </div>
        <button @click="addItem" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
            + {{ translations.add_item }}
        </button>
    </div>
</template>
