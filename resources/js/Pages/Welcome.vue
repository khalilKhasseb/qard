<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const features = [
    {
        icon: '🎨',
        title: 'Custom Branding',
        description: 'Design cards that reflect your unique identity with professional themes and layouts.'
    },
    {
        icon: '📱',
        title: 'Universal Access',
        description: 'Seamlessly share across devices with responsive design and instant updates.'
    },
    {
        icon: '📊',
        title: 'Smart Analytics',
        description: 'Track engagement and optimize your networking with detailed insights.'
    },
    {
        icon: '🔗',
        title: 'Dynamic Content',
        description: 'Add sections for contact info, social links, portfolios, and testimonials.'
    },
];

const pricingPlans = [
    {
        name: 'Starter',
        price: 0,
        period: 'forever',
        features: [
            '1 Business Card',
            '3 Custom Themes',
            'Basic Analytics',
            'QR Code Sharing',
            'Email Support',
        ],
        cta: 'Get Started',
        popular: false,
    },
    {
        name: 'Professional',
        price: 12,
        period: 'month',
        features: [
            '10 Business Cards',
            'Unlimited Themes',
            'Advanced Analytics',
            'Custom Domain',
            'NFC Integration',
            'Priority Support',
        ],
        cta: 'Start Trial',
        popular: true,
    },
    {
        name: 'Enterprise',
        price: 39,
        period: 'month',
        features: [
            'Unlimited Cards',
            'Team Collaboration',
            'White Labeling',
            'API Access',
            'Custom Integrations',
            'Dedicated Support',
        ],
        cta: 'Contact Sales',
        popular: false,
    },
];
</script>

<template>
    <Head title="Qard - Professional Digital Business Cards" />

    <div class="min-h-screen bg-white">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full bg-white/95 backdrop-blur-sm border-b border-gray-100 z-50">
            <div class="max-w-6xl mx-auto px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <Link href="/" class="flex items-center space-x-2">
                            <div class="h-8 w-8 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-sm">Q</span>
                            </div>
                            <span class="text-xl font-semibold text-gray-900">Qard</span>
                        </Link>
                    </div>

                    <div class="hidden md:flex items-center space-x-8">
                        <a href="#features" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition">Features</a>
                        <a href="#pricing" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition">Pricing</a>
                        <a href="#" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition">Templates</a>
                    </div>

                    <div class="flex items-center space-x-4">
                        <template v-if="canLogin">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="text-gray-700 hover:text-gray-900 text-sm font-medium"
                            >
                                Dashboard
                            </Link>
                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="text-gray-700 hover:text-gray-900 text-sm font-medium"
                                >
                                    Sign In
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition"
                                >
                                    Start Free
                                </Link>
                            </template>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-24 pb-16 px-6 lg:px-8">
            <div class="max-w-6xl mx-auto">
                <div class="text-center">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-gray-50 border border-gray-200 text-xs font-medium text-gray-600 mb-8">
                        ✨ Professional networking, simplified
                    </div>
                    <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        Digital business cards
                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">that work</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto leading-relaxed">
                        Create, share, and update your professional identity with elegant digital cards. No printing, no limits.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                        <Link
                            :href="canRegister ? route('register') : '#pricing'"
                            class="bg-gray-900 text-white px-8 py-3 rounded-lg font-medium hover:bg-gray-800 transition shadow-sm"
                        >
                            Create Your Card
                        </Link>
                        <Link
                            href="#features"
                            class="bg-white text-gray-900 px-8 py-3 rounded-lg font-medium border border-gray-200 hover:bg-gray-50 transition"
                        >
                            Learn More
                        </Link>
                    </div>
                    <div class="flex flex-wrap justify-center items-center gap-8 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Free forever plan
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            No credit card required
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Setup in 2 minutes
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Preview Section -->
        <section class="py-16 px-6 lg:px-8 bg-gray-50">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">See it in action</h2>
                    <p class="text-gray-600">Professional cards that adapt to any device</p>
                </div>
                <div class="flex justify-center">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 max-w-md w-full">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                                    <span class="text-white font-bold">Q</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Your Name</p>
                                    <p class="text-sm text-gray-500">Your Position</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">Live</span>
                        </div>
                        <div class="space-y-4 mb-6">
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm text-gray-600">hello@yourcompany.com</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span class="text-sm text-gray-600">+1 (555) 123-4567</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-lg font-semibold text-gray-900">1.2K</p>
                                <p class="text-xs text-gray-500">Views</p>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-gray-900">4.8%</p>
                                <p class="text-xs text-gray-500">Engagement</p>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-gray-900">89</p>
                                <p class="text-xs text-gray-500">Saves</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 px-6 lg:px-8">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Everything you need</h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Build professional connections with powerful features designed for modern business.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="text-center"
                    >
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gray-50 mb-4">
                            <span class="text-2xl">{{ feature.icon }}</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            {{ feature.title }}
                        </h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-20 px-6 lg:px-8 bg-gray-50">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Simple pricing</h2>
                    <p class="text-xl text-gray-600">Start free, scale as you grow</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <div
                        v-for="(plan, index) in pricingPlans"
                        :key="index"
                        class="bg-white rounded-2xl border border-gray-200 p-8 relative"
                        :class="plan.popular ? 'ring-2 ring-gray-900 shadow-lg' : 'shadow-sm'"
                    >
                        <div v-if="plan.popular" class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                            <span class="bg-gray-900 text-white text-xs font-medium px-3 py-1 rounded-full">Most Popular</span>
                        </div>
                        <div class="text-center">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                {{ plan.name }}
                            </h3>
                            <div class="mb-6">
                                <span class="text-4xl font-bold text-gray-900">${{ plan.price }}</span>
                                <span class="text-gray-500 text-sm">/{{ plan.period }}</span>
                            </div>

                            <ul class="space-y-3 mb-8 text-sm text-left">
                                <li
                                    v-for="(feature, fIndex) in plan.features"
                                    :key="fIndex"
                                    class="flex items-center gap-3"
                                >
                                    <svg class="h-4 w-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-600">{{ feature }}</span>
                                </li>
                            </ul>

                            <Link
                                :href="canRegister ? route('register') : '#'"
                                :class="plan.popular ? 'bg-gray-900 hover:bg-gray-800 text-white' : 'bg-white hover:bg-gray-50 text-gray-900 border border-gray-200'"
                                class="block w-full text-center py-3 px-6 rounded-lg font-medium transition"
                            >
                                {{ plan.cta }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 px-6 lg:px-8 bg-gray-900 text-white">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-4">
                    Ready to modernize your networking?
                </h2>
                <p class="text-xl text-gray-300 mb-8">
                    Join thousands of professionals using Qard to make lasting impressions.
                </p>
                <Link
                    :href="canRegister ? route('register') : '#'"
                    class="inline-flex items-center justify-center bg-white text-gray-900 px-8 py-3 rounded-lg font-medium hover:bg-gray-50 transition"
                >
                    Create Your Card
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-100 py-12 px-6 lg:px-8">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="h-8 w-8 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-sm">Q</span>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Qard</h3>
                        </div>
                        <p class="text-gray-600 max-w-md">
                            Professional digital business cards for the modern world. Share your story with elegance and precision.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-4">Product</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#features" class="text-gray-600 hover:text-gray-900 transition">Features</a></li>
                            <li><a href="#pricing" class="text-gray-600 hover:text-gray-900 transition">Pricing</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 transition">Templates</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-4">Company</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 transition">About</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 transition">Contact</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 transition">Privacy</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-100 mt-8 pt-6 text-center text-sm text-gray-500">
                    <p>&copy; 2026 Qard. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
