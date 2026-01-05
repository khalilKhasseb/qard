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
        title: 'Custom Themes',
        description: 'Create beautiful, branded themes with custom colors, fonts, and layouts for your business cards.'
    },
    {
        icon: '📱',
        title: 'Mobile Optimized',
        description: 'Your cards look perfect on any device. Responsive design ensures great experience everywhere.'
    },
    {
        icon: '📊',
        title: 'Analytics & Insights',
        description: 'Track views, shares, and NFC taps. Understand how people interact with your cards.'
    },
    {
        icon: '🔗',
        title: 'Smart Sections',
        description: 'Add contact info, social links, products, services, galleries, testimonials, and more.'
    },
];

const pricingPlans = [
    {
        name: 'Free',
        price: 0,
        period: 'forever',
        features: [
            '1 Business Card',
            '2 Custom Themes',
            'Basic Analytics',
            'QR Code Sharing',
            'Community Support',
        ],
        cta: 'Get Started',
        popular: false,
    },
    {
        name: 'Pro',
        price: 9.99,
        period: 'month',
        features: [
            '10 Business Cards',
            'Unlimited Themes',
            'Advanced Analytics',
            'Custom Domain',
            'NFC Support',
            'Priority Support',
        ],
        cta: 'Start Free Trial',
        popular: true,
    },
    {
        name: 'Business',
        price: 29.99,
        period: 'month',
        features: [
            'Unlimited Cards',
            'Unlimited Themes',
            'Team Collaboration',
            'White Label',
            'API Access',
            'Dedicated Support',
        ],
        cta: 'Contact Sales',
        popular: false,
    },
];
</script>

<template>
    <Head title="Qard - Digital Business Cards Made Simple" />
    
    <div class="min-h-screen bg-white">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full bg-white/80 backdrop-blur-lg border-b border-gray-200 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <Link href="/" class="text-2xl font-bold text-indigo-600">
                            Qard
                        </Link>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <template v-if="canLogin">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="text-gray-700 hover:text-indigo-600 px-4 py-2 rounded-md font-medium"
                            >
                                Dashboard
                            </Link>
                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="text-gray-700 hover:text-indigo-600 px-4 py-2 rounded-md font-medium"
                                >
                                    Log in
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="bg-indigo-600 text-white px-6 py-2 rounded-md font-medium hover:bg-indigo-700 transition"
                                >
                                    Get Started
                                </Link>
                            </template>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-indigo-50 via-white to-purple-50">
            <div class="max-w-7xl mx-auto">
                <div class="text-center">
                    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6">
                        Digital Business Cards
                        <span class="text-indigo-600">Made Simple</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                        Create stunning, shareable digital business cards in minutes. 
                        Track engagement, customize designs, and never run out of cards again.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            :href="canRegister ? route('register') : '#pricing'"
                            class="bg-indigo-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-indigo-700 transition text-lg"
                        >
                            Create Your Card Free
                        </Link>
                        <Link
                            href="#features"
                            class="bg-white text-indigo-600 px-8 py-4 rounded-lg font-semibold border-2 border-indigo-600 hover:bg-indigo-50 transition text-lg"
                        >
                            See How It Works
                        </Link>
                    </div>
                    
                    <!-- Demo Preview -->
                    <div class="mt-16 relative">
                        <div class="relative mx-auto max-w-5xl">
                            <div class="absolute inset-0 bg-gradient-to-r from-indigo-400 to-purple-400 rounded-3xl blur-3xl opacity-20"></div>
                            <div class="relative bg-white rounded-2xl shadow-2xl p-8 border border-gray-200">
                                <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-50 rounded-xl flex items-center justify-center">
                                    <div class="text-center">
                                        <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                        </svg>
                                        <p class="text-gray-400 text-lg">Beautiful Digital Cards Preview</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Everything You Need
                    </h2>
                    <p class="text-xl text-gray-600">
                        Powerful features to make your digital presence shine
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="bg-white p-8 rounded-xl border border-gray-200 hover:shadow-lg transition"
                    >
                        <div class="text-5xl mb-4">{{ feature.icon }}</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">
                            {{ feature.title }}
                        </h3>
                        <p class="text-gray-600">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Simple, Transparent Pricing
                    </h2>
                    <p class="text-xl text-gray-600">
                        Choose the plan that's right for you
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    <div
                        v-for="(plan, index) in pricingPlans"
                        :key="index"
                        class="bg-white rounded-2xl shadow-lg overflow-hidden"
                        :class="plan.popular ? 'ring-2 ring-indigo-600 scale-105' : ''"
                    >
                        <div v-if="plan.popular" class="bg-indigo-600 text-white text-center py-2 font-semibold">
                            Most Popular
                        </div>
                        <div class="p-8">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                                {{ plan.name }}
                            </h3>
                            <div class="mb-6">
                                <span class="text-5xl font-bold text-gray-900">${{ plan.price }}</span>
                                <span class="text-gray-600">/ {{ plan.period }}</span>
                            </div>

                            <ul class="space-y-4 mb-8">
                                <li
                                    v-for="(feature, fIndex) in plan.features"
                                    :key="fIndex"
                                    class="flex items-start"
                                >
                                    <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700">{{ feature }}</span>
                                </li>
                            </ul>

                            <Link
                                :href="canRegister ? route('register') : '#'"
                                :class="plan.popular ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-gray-900 hover:bg-gray-800 text-white'"
                                class="block w-full text-center py-3 px-6 rounded-lg font-semibold transition"
                            >
                                {{ plan.cta }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-indigo-600">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl font-bold text-white mb-6">
                    Ready to Go Digital?
                </h2>
                <p class="text-xl text-indigo-100 mb-8">
                    Join thousands of professionals using Qard for their digital business cards
                </p>
                <Link
                    :href="canRegister ? route('register') : '#'"
                    class="inline-block bg-white text-indigo-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition text-lg"
                >
                    Create Your Free Card Now
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <h3 class="text-2xl font-bold mb-4">Qard</h3>
                        <p class="text-gray-400 mb-4">
                            Modern digital business cards for professionals. Share your info instantly with QR codes, NFC, and links.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Product</h4>
                        <ul class="space-y-2">
                            <li><a href="#features" class="text-gray-400 hover:text-white">Features</a></li>
                            <li><a href="#pricing" class="text-gray-400 hover:text-white">Pricing</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Templates</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Company</h4>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white">About</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Contact</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white">Privacy</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; 2026 Qard. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
