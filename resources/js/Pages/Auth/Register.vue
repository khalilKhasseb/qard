<script setup>
import InputError from '@/Components/Shared/InputError.vue';
import InputLabel from '@/Components/Shared/InputLabel.vue';
import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';
import TextInput from '@/Components/Shared/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { useTranslations } from '@/composables/useTranslations';
import { ref, computed } from 'vue';

const { t } = useTranslations();

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    plan_id: null,
});

const currentStep = ref(1);

const selectedPlan = computed(() => {
    return props.plans.find(plan => plan.id === form.plan_id);
});

const selectPlan = (planId) => {
    form.plan_id = planId;
};

const goToStep2 = () => {
    if (form.plan_id) {
        currentStep.value = 2;
    }
};

const goBackToStep1 = () => {
    currentStep.value = 1;
};

const submit = () => {
    // Inertia's useForm handles CSRF automatically
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
    }).format(price);
};
</script>

<template>
    <div class="flex min-h-screen flex justify-center items-center bg-white">
        <Head :title="t('auth.register.title')" />

        <div class="container mx-auto w-3/4 sm:w-full mt-3 flex items-center py-3">
            <!-- Left Side - Branding -->
            <div class="rounded-lg hidden lg:flex lg:w-1/2 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-purple-600/20"></div>
                <div class="relative z-10 flex flex-col justify-center px-12 py-12 text-white">
                    <div class="mb-8">
                        <Link href="/" class="flex items-center space-x-3 mb-8">
                            <div class="h-12 w-12 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                                <span class="text-white font-bold text-xl">Q</span>
                            </div>
                            <span class="text-3xl font-bold">Qard</span>
                        </Link>
                        <h1 class="text-4xl font-bold mb-4 leading-tight">
                            {{ t('auth.register.hero_title') }}
                        </h1>
                        <p class="text-xl text-gray-300 leading-relaxed">
                            {{ t('auth.register.hero_subtitle') }}
                        </p>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">{{ t('auth.register.feature_free') }}</p>
                                <p class="text-gray-400 text-sm">{{ t('auth.register.feature_free_desc') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">{{ t('auth.register.feature_setup') }}</p>
                                <p class="text-gray-400 text-sm">{{ t('auth.register.feature_setup_desc') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">{{ t('auth.register.feature_share') }}</p>
                                <p class="text-gray-400 text-sm">{{ t('auth.register.feature_share_desc') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Mock card preview -->
                    <div class="mt-12 bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="h-8 w-8 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                                    <span class="text-white font-bold">Q</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-white">{{ t('auth.register.card_preview') }}</p>
                                    <p class="text-xs text-gray-300">{{ t('auth.register.card_ready') }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-green-400 bg-green-400/20 px-2 py-1 rounded-full">{{ t('auth.register.card_live') }}</span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <svg class="h-4 w-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                                <span class="text-sm text-gray-300">your.email@company.com</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="h-4 w-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span class="text-sm text-gray-300">+1 (555) 123-4567</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Background pattern -->
                <div class="absolute inset-0 opacity-10 ">
                    <div class="absolute top-20 left-20 w-32 h-32 bg-white rounded-full"></div>
                    <div class="absolute bottom-20 right-20 w-24 h-24 bg-indigo-400 rounded-full"></div>
                    <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-purple-400 rounded-full"></div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-12 xl:px-20">
                <div class="mx-auto w-full max-w-xl">
                    <!-- Mobile logo -->
                    <div class="lg:hidden mb-8 text-center">
                        <Link href="/" class="flex items-center justify-center space-x-2">
                            <div class="h-10 w-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-lg">Q</span>
                            </div>
                            <span class="text-2xl font-bold text-gray-900">Qard</span>
                        </Link>
                    </div>

                    <!-- Step Indicator -->
                    <div class="flex items-center justify-center mb-8">
                        <div class="flex items-center">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold"
                                :class="currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600'"
                            >
                                1
                            </div>
                            <div class="w-16 h-1 mx-2" :class="currentStep >= 2 ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold"
                                :class="currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600'"
                            >
                                2
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Plan Selection -->
                    <div v-if="currentStep === 1">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ t('auth.register.choose_plan') }}</h2>
                            <p class="text-gray-600">{{ t('auth.register.choose_plan_desc') }}</p>
                        </div>

                        <!-- Plan Cards -->
                        <div class="space-y-4 mb-8">
                            <div
                                v-for="plan in plans"
                                :key="plan.id"
                                @click="selectPlan(plan.id)"
                                class="border-2 rounded-xl p-4 cursor-pointer transition-all duration-200"
                                :class="form.plan_id === plan.id
                                    ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-600 ring-offset-2'
                                    : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50'"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900">{{ plan.name }}</h3>
                                            <span
                                                v-if="plan.slug === 'pro' || plan.slug === 'professional'"
                                                class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full"
                                            >
                                                {{ t('auth.register.popular') }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-3">{{ plan.description }}</p>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center text-xs text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 me-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                {{ plan.cards_limit }} {{ t('auth.register.cards') }}
                                            </span>
                                            <span class="inline-flex items-center text-xs text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 me-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                {{ plan.themes_limit }} {{ t('auth.register.themes') }}
                                            </span>
                                            <span v-if="plan.analytics_enabled" class="inline-flex items-center text-xs text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 me-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                {{ t('auth.register.analytics') }}
                                            </span>
                                            <span v-if="plan.nfc_enabled" class="inline-flex items-center text-xs text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 me-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                NFC
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right ms-4">
                                        <div class="text-2xl font-bold text-gray-900">{{ formatPrice(plan.price) }}</div>
                                        <div class="text-xs text-gray-500">/{{ plan.billing_cycle }}</div>
                                    </div>
                                </div>

                                <!-- Selection indicator -->
                                <div v-if="form.plan_id === plan.id" class="mt-3 pt-3 border-t border-indigo-200">
                                    <div class="flex items-center text-indigo-600 text-sm font-medium">
                                        <svg class="w-5 h-5 me-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        {{ t('auth.register.plan_selected') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <InputError class="mb-4" :message="form.errors.plan_id" />

                        <button
                            @click="goToStep2"
                            :disabled="!form.plan_id"
                            class="w-full bg-gray-900 hover:bg-gray-800 text-white px-8 py-3 rounded-lg font-medium transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ t('auth.register.continue') }}
                        </button>

                        <div class="mt-6 text-center">
                            <Link
                                :href="route('login')"
                                class="text-sm text-gray-600 hover:text-gray-900 transition"
                            >
                                {{ t('auth.register.have_account') }}
                            </Link>
                        </div>
                    </div>

                    <!-- Step 2: Account Details -->
                    <div v-else>
                        <div class="text-center mb-6">
                            <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ t('auth.register.heading') }}</h2>
                            <p class="text-gray-600">{{ t('auth.register.description') }}</p>
                        </div>

                        <!-- Selected Plan Summary -->
                        <div v-if="selectedPlan" class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-indigo-600 font-medium">{{ t('auth.register.selected_plan') }}</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ selectedPlan.name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900">{{ formatPrice(selectedPlan.price) }}</p>
                                    <button @click="goBackToStep1" class="text-sm text-indigo-600 hover:text-indigo-800">
                                        {{ t('auth.register.change_plan') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-5">
                            <div>
                                <InputLabel for="name" :value="t('auth.register.name')" class="text-gray-700 font-medium" />

                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />

                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="email" :value="t('auth.register.email')" class="text-gray-700 font-medium" />

                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                    v-model="form.email"
                                    required
                                    autocomplete="username"
                                />

                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div>
                                <InputLabel for="phone" :value="t('auth.register.phone')" class="text-gray-700 font-medium" />

                                <TextInput
                                    id="phone"
                                    type="tel"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                    v-model="form.phone"
                                    required
                                    autocomplete="tel"
                                    placeholder="+1 (555) 123-4567"
                                />

                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>

                            <div>
                                <InputLabel for="password" :value="t('auth.register.password')" class="text-gray-700 font-medium" />

                                <TextInput
                                    id="password"
                                    type="password"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                    v-model="form.password"
                                    required
                                    autocomplete="new-password"
                                />

                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div>
                                <InputLabel
                                    for="password_confirmation"
                                    :value="t('auth.register.confirm_password')"
                                    class="text-gray-700 font-medium"
                                />

                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                    v-model="form.password_confirmation"
                                    required
                                    autocomplete="new-password"
                                />

                                <InputError
                                    class="mt-2"
                                    :message="form.errors.password_confirmation"
                                />
                            </div>

                            <div class="flex items-center justify-between pt-4">
                                <button
                                    type="button"
                                    @click="goBackToStep1"
                                    class="text-sm text-gray-600 hover:text-gray-900 transition flex items-center"
                                >
                                    <svg class="w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    {{ t('auth.register.back') }}
                                </button>

                                <PrimaryButton
                                    class="bg-gray-900 hover:bg-gray-800 text-white px-8 py-2 rounded-lg font-medium transition shadow-sm"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing">{{ t('auth.register.submitting') }}</span>
                                    <span v-else>{{ t('auth.register.submit') }}</span>
                                </PrimaryButton>
                            </div>
                        </form>

                        <div class="mt-8 text-center">
                            <p class="text-xs text-gray-500">
                                {{ t('auth.register.terms_agree') }}
                                <a href="#" class="text-gray-700 hover:text-gray-900 underline">{{ t('auth.register.terms_of_service') }}</a>
                                {{ t('auth.register.and') }}
                                <a href="#" class="text-gray-700 hover:text-gray-900 underline">{{ t('auth.register.privacy_policy') }}</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
