<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const logoUrl = computed(() => page.props.settings?.logo);
const siteName = computed(() => page.props.settings?.site_name || 'TapIt');
</script>

<template>
    <div class="flex items-center space-x-2">
        <template v-if="logoUrl">
            <img :src="logoUrl" :alt="siteName" class="h-9 w-auto" />
        </template>
        <template v-else>
            <!-- Simple TapIt Logo -->
            <svg class="w-8 h-8" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Hand icon representing "Tap" -->
                <path d="M20 5C18.34 5 17 6.34 17 8V18L11.66 19.73C10.12 20.24 9.24 21.91 9.75 23.45L14.07 36.77C14.33 37.58 14.99 38.17 15.82 38.3L29.82 40.3C31.3 40.52 32.68 39.54 32.97 38.08L35.82 23.22C36.17 21.47 35.08 19.75 33.33 19.41L29 18.5V8C29 6.34 27.66 5 26 5C24.34 5 23 6.34 23 8V16H21V8C21 6.34 19.66 5 18 5H20Z" fill="currentColor"/>
            </svg>
            <span class="text-xl font-bold">{{ siteName }}</span>
        </template>
    </div>
</template>
