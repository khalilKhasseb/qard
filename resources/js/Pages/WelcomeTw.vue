<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import LanguageSelector from '@/Components/Shared/LanguageSelector.vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    plans: Array,
    sampleCard: Object,
    stats: Object,
});

const page = usePage();
const settings = computed(() => page.props.settings);
const languages = computed(() => page.props.languages);
const currentLanguage = computed(() => page.props.currentLanguage);
const currentDirection = computed(() => page.props.currentDirection);

// Mobile menu state
const mobileMenuOpen = ref(false);

// Format large numbers
const formatNumber = (num) => {
    if (!num) return '0';
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(0) + 'K';
    return num.toString();
};

// Format price
const formatPrice = (price) => {
    if (price === 0) return t('welcome.pricing.free');
    return '$' + price;
};

// Get billing label
const getBillingLabel = (cycle) => {
    if (cycle === 'monthly') return t('welcome.pricing.per_month');
    if (cycle === 'yearly') return t('welcome.pricing.per_year');
    if (cycle === 'lifetime') return t('welcome.pricing.once');
    return '';
};

// How it works steps
const steps = computed(() => [
    {
        number: '01',
        title: t('welcome.how_it_works.step1_title'),
        description: t('welcome.how_it_works.step1_desc'),
        icon: 'M12 4v16m8-8H4',
    },
    {
        number: '02',
        title: t('welcome.how_it_works.step2_title'),
        description: t('welcome.how_it_works.step2_desc'),
        icon: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
    },
    {
        number: '03',
        title: t('welcome.how_it_works.step3_title'),
        description: t('welcome.how_it_works.step3_desc'),
        icon: 'M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z',
    },
]);

// Features with better icons
const features = computed(() => [
    {
        title: t('welcome.features.qr_nfc_title'),
        description: t('welcome.features.qr_nfc_desc'),
        icon: 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z',
    },
    {
        title: t('welcome.features.update_title'),
        description: t('welcome.features.update_desc'),
        icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
    },
    {
        title: t('welcome.features.analytics_title'),
        description: t('welcome.features.analytics_desc'),
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    },
    {
        title: t('welcome.features.multilingual_title'),
        description: t('welcome.features.multilingual_desc'),
        icon: 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129',
    },
    {
        title: t('welcome.features.branding_title'),
        description: t('welcome.features.branding_desc'),
        icon: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
    },
    {
        title: t('welcome.features.content_title'),
        description: t('welcome.features.content_desc'),
        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
    },
]);
</script>

<template>
    <Head :title="`${settings?.site_name || 'Qard'} - Digital Business Cards`" />

    <div class="min-h-screen bg-white antialiased" :dir="currentDirection">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full bg-white/80 backdrop-blur-lg border-b border-gray-100 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-2.5">
                        <div class="h-9 w-9 bg-gradient-to-br from-violet-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/20">
                            <span class="text-white font-bold text-lg">{{ settings?.site_name?.charAt(0) || 'Q' }}</span>
                        </div>
                        <span class="text-xl font-semibold text-gray-900">{{ settings?.site_name || 'Qard' }}</span>
                    </Link>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center gap-8">
                        <a href="#how-it-works" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">{{ t('welcome.nav.how_it_works') }}</a>
                        <a href="#features" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">{{ t('welcome.nav.features') }}</a>
                        <a href="#pricing" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">{{ t('welcome.nav.pricing') }}</a>
                    </div>

                    <!-- Auth Buttons & Language Selector -->
                    <div class="flex items-center gap-3">
                        <!-- Language Selector -->
                        <LanguageSelector
                            :languages="languages"
                            :current-language="currentLanguage"
                        />

                        <template v-if="canLogin">
                            <Link
                                v-if="$page.props.auth?.user"
                                :href="route('dashboard')"
                                class="hidden sm:inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors"
                            >
                                {{ t('welcome.nav.dashboard') }}
                            </Link>
                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="hidden sm:inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors"
                                >
                                    {{ t('welcome.nav.sign_in') }}
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors shadow-sm"
                                >
                                    {{ t('welcome.nav.get_started') }}
                                </Link>
                            </template>
                        </template>

                        <!-- Mobile menu button -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden p-2 text-gray-600 hover:text-gray-900"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Navigation -->
                <div v-if="mobileMenuOpen" class="md:hidden py-4 border-t border-gray-100">
                    <div class="flex flex-col gap-2">
                        <a href="#how-it-works" @click="mobileMenuOpen = false" class="px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg">{{ t('welcome.nav.how_it_works') }}</a>
                        <a href="#features" @click="mobileMenuOpen = false" class="px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg">{{ t('welcome.nav.features') }}</a>
                        <a href="#pricing" @click="mobileMenuOpen = false" class="px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg">{{ t('welcome.nav.pricing') }}</a>
                        <Link v-if="canLogin && !$page.props.auth?.user" :href="route('login')" class="px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg">{{ t('welcome.nav.sign_in') }}</Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 sm:pt-40 sm:pb-28 overflow-hidden">
            <!-- Background gradient -->
            <div class="absolute inset-0 bg-gradient-to-b from-violet-50/50 via-white to-white"></div>
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[600px] bg-gradient-to-br from-violet-200/30 via-indigo-200/20 to-transparent rounded-full blur-3xl"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-4xl mx-auto">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-violet-50 border border-violet-100 mb-8">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-violet-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-violet-500"></span>
                        </span>
                        <span class="text-sm font-medium text-violet-700">{{ t('welcome.hero.badge') }}</span>
                    </div>

                    <!-- Headline -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 tracking-tight mb-6">
                        {{ t('welcome.hero.title_1') }}
                        <span class="bg-gradient-to-r from-violet-600 to-indigo-600 bg-clip-text text-transparent">{{ t('welcome.hero.title_2') }}</span>
                    </h1>

                    <!-- Subheadline -->
                    <p class="text-lg sm:text-xl text-gray-600 mb-10 max-w-2xl mx-auto leading-relaxed">
                        {{ t('welcome.hero.subtitle') }}
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                        <Link
                            :href="canRegister ? route('register') : '#pricing'"
                            class="inline-flex items-center justify-center px-8 py-3.5 text-base font-semibold text-white bg-gray-900 rounded-xl hover:bg-gray-800 transition-all shadow-lg shadow-gray-900/10 hover:shadow-xl hover:shadow-gray-900/20 hover:-translate-y-0.5"
                        >
                            {{ t('welcome.hero.cta_primary') }}
                            <svg class="ms-2 w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                        <a
                            v-if="sampleCard"
                            :href="route('card.public.share', sampleCard.share_url)"
                            target="_blank"
                            class="inline-flex items-center justify-center px-8 py-3.5 text-base font-semibold text-gray-700 bg-white rounded-xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all"
                        >
                            <svg class="me-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{ t('welcome.hero.cta_secondary') }}
                        </a>
                    </div>

                    <!-- Trust indicators -->
                    <div class="flex flex-wrap justify-center items-center gap-x-8 gap-y-4 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ t('welcome.hero.trust_free') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ t('welcome.hero.trust_no_card') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ t('welcome.hero.trust_setup') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div v-if="stats && (stats.users > 0 || stats.cards > 0)" class="mt-16 flex justify-center">
                    <div class="inline-flex items-center gap-8 sm:gap-12 px-8 py-4 bg-white rounded-2xl border border-gray-100 shadow-lg shadow-gray-100/50">
                        <div class="text-center" v-if="stats.users > 0">
                            <div class="text-2xl sm:text-3xl font-bold text-gray-900">{{ formatNumber(stats.users) }}+</div>
                            <div class="text-sm text-gray-500">{{ t('welcome.stats.users') }}</div>
                        </div>
                        <div class="w-px h-10 bg-gray-200" v-if="stats.users > 0 && stats.cards > 0"></div>
                        <div class="text-center" v-if="stats.cards > 0">
                            <div class="text-2xl sm:text-3xl font-bold text-gray-900">{{ formatNumber(stats.cards) }}+</div>
                            <div class="text-sm text-gray-500">{{ t('welcome.stats.cards') }}</div>
                        </div>
                        <div class="w-px h-10 bg-gray-200" v-if="stats.cards > 0 && stats.views > 0"></div>
                        <div class="text-center" v-if="stats.views > 0">
                            <div class="text-2xl sm:text-3xl font-bold text-gray-900">{{ formatNumber(stats.views) }}+</div>
                            <div class="text-sm text-gray-500">{{ t('welcome.stats.views') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it Works Section -->
        <section id="how-it-works" class="py-20 sm:py-28 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">{{ t('welcome.how_it_works.title') }}</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ t('welcome.how_it_works.subtitle') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                    <div v-for="(step, index) in steps" :key="index" class="relative">
                        <!-- Connector line -->
                        <div v-if="index < steps.length - 1" class="hidden md:block absolute top-12 start-1/2 w-full h-0.5 bg-gradient-to-r from-violet-200 to-indigo-200 rtl:bg-gradient-to-l"></div>

                        <div class="relative bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg hover:border-violet-100 transition-all duration-300">
                            <!-- Step number -->
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white font-bold text-lg mb-6 shadow-lg shadow-violet-500/20">
                                {{ step.number }}
                            </div>

                            <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ step.title }}</h3>
                            <p class="text-gray-600 leading-relaxed">{{ step.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 sm:py-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">{{ t('welcome.features.title') }}</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ t('welcome.features.subtitle') }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="group p-6 rounded-2xl border border-gray-100 hover:border-violet-100 hover:bg-violet-50/30 transition-all duration-300"
                    >
                        <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center mb-5 group-hover:bg-violet-200 transition-colors">
                            <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="feature.icon" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ feature.title }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-20 sm:py-28 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">{{ t('welcome.pricing.title') }}</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ t('welcome.pricing.subtitle') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <div
                        v-for="(plan, index) in plans"
                        :key="plan.id"
                        class="relative bg-white rounded-2xl p-8 transition-all duration-300"
                        :class="plan.is_popular
                            ? 'ring-2 ring-violet-600 shadow-xl shadow-violet-500/10 scale-105 z-10'
                            : 'border border-gray-200 hover:border-violet-200 hover:shadow-lg'"
                    >
                        <!-- Popular badge -->
                        <div v-if="plan.is_popular" class="absolute -top-4 left-1/2 -translate-x-1/2">
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-lg">
                                {{ t('welcome.pricing.most_popular') }}
                            </span>
                        </div>

                        <div class="text-center mb-8">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ plan.name }}</h3>
                            <p v-if="plan.description" class="text-sm text-gray-500 mb-4">{{ plan.description }}</p>
                            <div class="flex items-baseline justify-center gap-1">
                                <span class="text-4xl font-bold text-gray-900">{{ formatPrice(plan.price) }}</span>
                                <span v-if="plan.price > 0" class="text-gray-500">{{ getBillingLabel(plan.billing_cycle) }}</span>
                            </div>
                        </div>

                        <ul class="space-y-4 mb-8">
                            <li v-for="(feature, fIndex) in plan.features" :key="fIndex" class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-violet-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-600 text-sm">{{ feature }}</span>
                            </li>
                        </ul>

                        <Link
                            :href="canRegister ? route('register') : '#'"
                            class="block w-full text-center py-3 px-6 rounded-xl font-semibold transition-all"
                            :class="plan.is_popular
                                ? 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white hover:from-violet-700 hover:to-indigo-700 shadow-lg shadow-violet-500/20'
                                : 'bg-gray-900 text-white hover:bg-gray-800'"
                        >
                            {{ plan.price === 0 ? t('welcome.pricing.start_free') : t('welcome.pricing.get_started') }}
                        </Link>
                    </div>
                </div>

                <!-- Fallback if no plans -->
                <div v-if="!plans || plans.length === 0" class="text-center py-12">
                    <p class="text-gray-500">{{ t('welcome.pricing.coming_soon') }}</p>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="inline-flex items-center justify-center mt-4 px-8 py-3 text-base font-semibold text-white bg-gray-900 rounded-xl hover:bg-gray-800 transition-colors"
                    >
                        {{ t('welcome.nav.get_started') }}
                    </Link>
                </div>
            </div>
        </section>

        <!-- Final CTA Section -->
        <section class="py-20 sm:py-28 bg-gradient-to-br from-gray-900 via-gray-900 to-violet-900 relative overflow-hidden">
            <!-- Background pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'30\' height=\'30\' viewBox=\'0 0 30 30\' fill=\'none\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z\' fill=\'rgba(255,255,255,0.5)\'/%3E%3C/svg%3E'); background-repeat: repeat;"></div>
            </div>

            <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6">
                    {{ t('welcome.cta.title') }}
                </h2>
                <p class="text-lg sm:text-xl text-gray-300 mb-10 max-w-2xl mx-auto">
                    {{ t('welcome.cta.subtitle') }}
                </p>
                <Link
                    :href="canRegister ? route('register') : '#pricing'"
                    class="inline-flex items-center justify-center px-10 py-4 text-lg font-semibold text-gray-900 bg-white rounded-xl hover:bg-gray-100 transition-all shadow-xl hover:shadow-2xl hover:-translate-y-0.5"
                >
                    {{ t('welcome.cta.button') }}
                    <svg class="ms-2 w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-100 py-12 sm:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
                    <!-- Brand -->
                    <div class="col-span-2">
                        <Link href="/" class="flex items-center gap-2.5 mb-4">
                            <div class="h-9 w-9 bg-gradient-to-br from-violet-600 to-indigo-600 rounded-xl flex items-center justify-center">
                                <span class="text-white font-bold text-lg">{{ settings?.site_name?.charAt(0) || 'Q' }}</span>
                            </div>
                            <span class="text-xl font-semibold text-gray-900">{{ settings?.site_name || 'Qard' }}</span>
                        </Link>
                        <p class="text-gray-600 max-w-sm text-sm leading-relaxed">
                            {{ t('welcome.footer.description') }}
                        </p>
                    </div>

                    <!-- Product Links -->
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-4">{{ t('welcome.footer.product') }}</h4>
                        <ul class="space-y-3 text-sm">
                            <li><a href="#features" class="text-gray-600 hover:text-violet-600 transition-colors">{{ t('welcome.nav.features') }}</a></li>
                            <li><a href="#pricing" class="text-gray-600 hover:text-violet-600 transition-colors">{{ t('welcome.nav.pricing') }}</a></li>
                            <li><a href="#how-it-works" class="text-gray-600 hover:text-violet-600 transition-colors">{{ t('welcome.nav.how_it_works') }}</a></li>
                        </ul>
                    </div>

                    <!-- Company Links -->
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-4">{{ t('welcome.footer.company') }}</h4>
                        <ul class="space-y-3 text-sm">
                            <li><a href="#" class="text-gray-600 hover:text-violet-600 transition-colors">{{ t('welcome.footer.about') }}</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-violet-600 transition-colors">{{ t('welcome.footer.contact') }}</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-violet-600 transition-colors">{{ t('welcome.footer.privacy') }}</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-violet-600 transition-colors">{{ t('welcome.footer.terms') }}</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom -->
                <div class="pt-8 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-gray-500">
                        &copy; {{ new Date().getFullYear() }} {{ settings?.site_name || 'Qard' }}. {{ t('welcome.footer.copyright') }}
                    </p>
                    <div class="flex items-center gap-4">
                        <a href="#" class="text-gray-400 hover:text-violet-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-violet-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-violet-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
