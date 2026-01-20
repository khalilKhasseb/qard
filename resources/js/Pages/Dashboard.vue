<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_cards: 0,
            published_cards: 0,
            total_views: 0,
            total_themes: 0,
        })
    },
    recentCards: {
        type: Array,
        default: () => []
    },
    subscription: {
        type: Object,
        default: null
    }
});

const subscriptionStatus = computed(() => {
    if (!props.subscription) return { text: 'Free Plan', color: 'gray' };
    if (props.subscription.is_trial) return { text: 'Trial', color: 'blue' };
    if (props.subscription.is_active) return { text: 'Active', color: 'green' };
    return { text: 'Inactive', color: 'red' };
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-blue-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Cards</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.total_cards }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-green-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Published</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.published_cards }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-purple-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Views</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.total_views }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-pink-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Themes</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.total_themes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-indigo-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">NFC Taps</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.total_nfc_taps || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Status -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">Subscription Status</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                          :class="{
                                              'bg-gray-100 text-gray-800': subscriptionStatus.color === 'gray',
                                              'bg-blue-100 text-blue-800': subscriptionStatus.color === 'blue',
                                              'bg-green-100 text-green-800': subscriptionStatus.color === 'green',
                                              'bg-red-100 text-red-800': subscriptionStatus.color === 'red',
                                          }">
                                        {{ subscriptionStatus.text }}
                                    </span>
                                    <span v-if="subscription && subscription.plan" class="ml-2">
                                        {{ subscription.plan.name }}
                                    </span>
                                </p>
                                
                                <!-- Usage Bar -->
                                <div v-if="subscription && subscription.plan" class="mt-3">
                                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                                        <span>Business Cards: {{ stats.total_cards }} / {{ subscription.plan.cards_limit }}</span>
                                        <span :class="stats.total_cards >= subscription.plan.cards_limit ? 'text-red-600 font-semibold' : ''">
                                            {{ subscription.plan.cards_limit - stats.total_cards }} remaining
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all"
                                             :class="stats.total_cards >= subscription.plan.cards_limit ? 'bg-red-500' : 'bg-green-500'"
                                             :style="`width: ${(stats.total_cards / subscription.plan.cards_limit) * 100}%`"></div>
                                    </div>
                                </div>

                                <p v-if="subscription && subscription.days_remaining" class="mt-1 text-sm text-gray-500">
                                    {{ subscription.days_remaining }} days remaining
                                </p>
                            </div>
                            <Link href="/subscription" class="ml-4 inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Manage Subscription
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link href="/cards/create" class="block overflow-hidden bg-white shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-blue-100 p-3">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-lg font-semibold text-gray-900">Create New Card</p>
                                    <p class="text-sm text-gray-500">Start building your digital card</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <Link href="/themes" class="block overflow-hidden bg-white shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-purple-100 p-3">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-lg font-semibold text-gray-900">Manage Themes</p>
                                    <p class="text-sm text-gray-500">Customize your card appearance</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <Link href="/cards" class="block overflow-hidden bg-white shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-green-100 p-3">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-lg font-semibold text-gray-900">View All Cards</p>
                                    <p class="text-sm text-gray-500">Manage your business cards</p>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Recent Cards -->
                <div v-if="recentCards.length > 0" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Cards</h3>
                        <div class="space-y-3">
                            <Link v-for="card in recentCards" 
                                  :key="card.id" 
                                  :href="`/cards/${card.id}/edit`"
                                  class="block p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-sm transition-all">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ card.title }}</p>
                                        <p class="text-sm text-gray-500">{{ card.subtitle }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                              :class="card.is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                            {{ card.is_published ? 'Published' : 'Draft' }}
                                        </span>
                                        <p class="mt-1 text-xs text-gray-500">{{ card.views_count }} views</p>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
