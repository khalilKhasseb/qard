<script setup>
const props = defineProps({
    content: Object
});

const emit = defineEmits(['update:content']);

const platforms = ['github', 'linkedin', 'twitter', 'instagram', 'facebook', 'whatsapp', 'youtube', 'tiktok'];

const updatePlatform = (platform, value) => {
    const updated = { ...props.content };
    updated[platform] = value;
    emit('update:content', updated);
};
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div v-for="platform in platforms" :key="platform">
            <label class="text-xs font-medium text-gray-600 uppercase">{{ platform }}</label>
            <input
                :value="content[platform]"
                @input="updatePlatform(platform, $event.target.value)"
                type="text"
                class="mt-1 block w-full rounded-md border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-gray-900"
                :placeholder="'https://' + platform + '.com/...'"
            />
        </div>
    </div>
</template>
