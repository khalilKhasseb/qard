<script setup>
import { computed } from 'vue';

const props = defineProps({
    item: Object,
    translations: Object,
    sectionId: [Number, String]
});

const emit = defineEmits(['update:item']);

// Check if section has been saved to database (real ID vs temporary timestamp)
// Database IDs are typically < 1 billion, timestamps are 13+ digits
const isSectionSaved = computed(() => {
    const id = Number(props.sectionId);
    return id > 0 && id < 1000000000;
});

const updateField = (field, value) => {
    emit('update:item', { ...props.item, [field]: value });
};

const uploadGalleryImage = async (file) => {
    if (!file) return;

    if (!isSectionSaved.value) {
        emit('update:item', {
            ...props.item,
            uploadError: 'Please save the section first before uploading images'
        });
        return;
    }

    const updatedItem = {
        ...props.item,
        uploading: true,
        uploadError: null
    };
    emit('update:item', updatedItem);

    const formData = new FormData();
    formData.append('image', file);

    try {
        const resp = await window.axios.post(`/api/sections/${props.sectionId}/gallery-upload`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (progressEvent) => {
                const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                emit('update:item', { ...updatedItem, progress });
            }
        });

        emit('update:item', {
            ...props.item,
            url: resp.data.url,
            progress: 100,
            uploading: false,
            uploadError: null
        });

        setTimeout(() => {
            emit('update:item', { ...props.item, url: resp.data.url, progress: null, uploading: false });
        }, 1000);
    } catch (err) {
        const errorMsg = (err.response?.data?.message) || 'Upload failed';
        emit('update:item', {
            ...props.item,
            uploadError: errorMsg,
            uploading: false
        });
    }
};
</script>

<template>
    <div class="space-y-2">
        <div class="flex gap-4 items-center mb-2">
            <label class="flex items-center gap-1 text-xs">
                <input
                    type="radio"
                    :checked="item.inputType === 'upload'"
                    @change="updateField('inputType', 'upload')"
                />
                Upload
            </label>
            <label class="flex items-center gap-1 text-xs">
                <input
                    type="radio"
                    :checked="item.inputType === 'url'"
                    @change="updateField('inputType', 'url')"
                />
                URL
            </label>
        </div>

        <div v-if="item.url" class="mb-2">
            <img :src="item.url" alt="preview" class="w-32 h-32 object-cover rounded border border-gray-200" />
        </div>

        <div v-if="item.inputType === 'upload'" class="space-y-2">
            <div v-if="!isSectionSaved" class="text-xs text-amber-600 bg-amber-50 px-3 py-2 rounded border border-amber-200">
                ⚠️ Save the section first to enable image uploads
            </div>
            <div class="flex items-center gap-2">
                <input
                    type="file"
                    accept="image/*"
                    :disabled="item.uploading || !isSectionSaved"
                    @change="uploadGalleryImage($event.target.files[0])"
                    class="block w-full text-sm border border-gray-300 rounded bg-white px-3 py-2 text-gray-900 disabled:opacity-50 disabled:cursor-not-allowed"
                />
                <span v-if="item.uploading" class="text-xs text-gray-500">{{ item.progress || 0 }}%</span>
            </div>
        </div>

        <div v-if="item.inputType === 'url'" class="flex items-center gap-2">
            <input
                :value="item.url"
                @input="updateField('url', $event.target.value)"
                type="text"
                :placeholder="translations.url"
                class="block w-full text-sm border border-gray-300 rounded bg-white px-3 py-2 text-gray-900"
            />
        </div>

        <div v-if="item.uploadError" class="text-xs text-red-500">{{ item.uploadError }}</div>

        <input
            :value="item.caption"
            @input="updateField('caption', $event.target.value)"
            type="text"
            :placeholder="translations.caption"
            class="block w-full text-sm border border-gray-300 rounded bg-white px-3 py-2 text-gray-900"
        />
    </div>
</template>
