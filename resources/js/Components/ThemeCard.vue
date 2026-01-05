<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    theme: {
        type: Object,
        required: true
    }
});

const getThemePreviewStyle = (theme) => {
    const config = theme.config || {};
    const colors = config.colors || {};
    
    return {
        background: `linear-gradient(135deg, ${colors.primary || '#3b82f6'} 0%, ${colors.secondary || '#1e40af'} 100%)`,
        color: colors.text || '#ffffff',
    };
};
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden">
        <!-- Preview -->
        <div
            class="h-32 flex items-center justify-center"
            :style="getThemePreviewStyle(theme)"
        >
            <div class="text-center">
                <div class="text-sm font-medium opacity-90">
                    {{ theme.name }}
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="p-4">
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-lg font-semibold text-gray-900">{{ theme.name }}</h3>
                <div class="flex gap-1">
                    <span v-if="theme.is_system_default" class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                        System
                    </span>
                    <span v-if="theme.is_public" class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                        Public
                    </span>
                </div>
            </div>

            <p class="text-sm text-gray-600 mb-3">
                Used by {{ theme.used_by_cards_count || 0 }} card{{ (theme.used_by_cards_count || 0) !== 1 ? 's' : '' }}
            </p>

            <div class="flex gap-2">
                <Link
                    v-if="!theme.is_system_default"
                    :href="`/themes/${theme.id}/edit`"
                    class="flex-1 text-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                >
                    Edit
                </Link>
                <button
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                >
                    Duplicate
                </button>
            </div>
        </div>
    </div>
</template>
