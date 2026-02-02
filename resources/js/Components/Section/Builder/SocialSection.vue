<script setup>
import { computed } from 'vue';
import { SOCIAL_ICONS, DEFAULT_PLATFORMS, ALL_PLATFORMS, normalizeLinks, createDefaultLinks } from '@/Components/Shared/SocialIcons.js';

const props = defineProps({
    content: [Object, Array],
    translations: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:content']);

// Normalize content to always work with links array
const links = computed(() => {
    const normalized = normalizeLinks(props.content);
    // If empty, return default platforms
    if (normalized.length === 0) {
        return createDefaultLinks();
    }
    return normalized;
});

// Get platform info
const getPlatformInfo = (key) => {
    return ALL_PLATFORMS.find(p => p.key === key) || { key, label: key };
};

const getPlaceholder = (key) => {
    const platform = DEFAULT_PLATFORMS.find(p => p.key === key);
    return platform?.placeholder || `https://${key}.com/...`;
};

// Update a link's URL
const updateLink = (index, url) => {
    const updated = [...links.value];
    updated[index] = { ...updated[index], url };
    emitUpdate(updated);
};

// Remove a link
const removeLink = (index) => {
    const updated = links.value.filter((_, i) => i !== index);
    emitUpdate(updated);
};

// Add a new link
const addLink = (platformKey) => {
    const updated = [...links.value, { platform: platformKey, url: '' }];
    emitUpdate(updated);
};

// Get available platforms that aren't already added
const availablePlatforms = computed(() => {
    const usedKeys = links.value.map(l => l.platform);
    return ALL_PLATFORMS.filter(p => !usedKeys.includes(p.key));
});

// Emit the update in the correct format
const emitUpdate = (linksArray) => {
    // Filter out empty links for storage, but keep structure
    const filtered = linksArray.filter(l => l.url && l.url.trim());
    emit('update:content', { links: linksArray });
};
</script>

<template>
    <div class="space-y-3">
        <!-- Existing links -->
        <div v-for="(link, index) in links" :key="link.platform + '-' + index" class="flex items-center gap-3">
            <!-- Icon -->
            <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center bg-gray-100 rounded-lg">
                <span
                    v-if="SOCIAL_ICONS[link.platform]"
                    class="w-6 h-6 flex items-center justify-center"
                    v-html="SOCIAL_ICONS[link.platform]"
                />
                <span v-else class="text-gray-400 text-xs uppercase">{{ link.platform.slice(0, 2) }}</span>
            </div>

            <!-- Input -->
            <div class="flex-1">
                <label class="text-xs font-medium text-gray-600 uppercase">{{ getPlatformInfo(link.platform).label }}</label>
                <input
                    :value="link.url"
                    @input="updateLink(index, $event.target.value)"
                    type="url"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-gray-900 text-sm"
                    :placeholder="getPlaceholder(link.platform)"
                />
            </div>

            <!-- Remove button -->
            <button
                @click="removeLink(index)"
                type="button"
                class="p-2 text-gray-400 hover:text-red-500 transition-colors"
                :title="translations.remove || 'Remove'"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>

        <!-- Add new link dropdown -->
        <div v-if="availablePlatforms.length > 0" class="pt-2 border-t border-gray-200">
            <div class="flex items-center gap-2">
                <select
                    @change="addLink($event.target.value); $event.target.value = ''"
                    class="flex-1 rounded-md border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm text-gray-700"
                >
                    <option value="">{{ translations.add_social || '+ Add social link...' }}</option>
                    <option v-for="platform in availablePlatforms" :key="platform.key" :value="platform.key">
                        {{ platform.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="links.length === 0" class="text-center py-4 text-gray-500 text-sm">
            {{ translations.no_social_links || 'No social links added yet.' }}
        </div>
    </div>
</template>
