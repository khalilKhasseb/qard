<script setup>
import Checkbox from '@/Components/Shared/Checkbox.vue';
import InputError from '@/Components/Shared/InputError.vue';
import InputLabel from '@/Components/Shared/InputLabel.vue';
import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';
import TextInput from '@/Components/Shared/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    allowEmailLogin: {
        type: Boolean,
        default: true,
    },
    allowPhoneLogin: {
        type: Boolean,
        default: true,
    },
});

const form = useForm({
    identifier: '',
    password: '',
    remember: false,
});

const identifierLabel = computed(() => {
    if (props.allowEmailLogin && props.allowPhoneLogin) {
        return t('auth.login.email_or_phone');
    }
    if (props.allowEmailLogin) {
        return t('auth.login.email');
    }
    if (props.allowPhoneLogin) {
        return t('auth.login.phone');
    }
    return t('auth.login.email_or_phone');
});

const identifierPlaceholder = computed(() => {
    if (props.allowEmailLogin && props.allowPhoneLogin) {
        return t('auth.login.enter_email_or_phone');
    }
    if (props.allowEmailLogin) {
        return t('auth.login.enter_email');
    }
    if (props.allowPhoneLogin) {
        return t('auth.login.enter_phone');
    }
    return t('auth.login.enter_email_or_phone');
});

const inputType = computed(() => {
    // Use text to allow both email and phone inputs
    return 'text';
});

const submit = async () => {
    try {
        await axios.get('/sanctum/csrf-cookie');
    } catch (error) {
        console.error('Failed to initialize CSRF protection:', error);
    }

    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen bg-white">
        <Head :title="t('auth.login.title')" />

        <div class="flex min-h-screen">
            <!-- Left Side - Branding -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-purple-600/20"></div>
                <div class="relative z-10 flex flex-col justify-center px-12 text-white">
                    <div class="mb-8">
                        <Link href="/" class="flex items-center space-x-3 mb-8">
                            <div class="h-12 w-12 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                                <span class="text-white font-bold text-xl">Q</span>
                            </div>
                            <span class="text-3xl font-bold">Qard</span>
                        </Link>
                        <h1 class="text-4xl font-bold mb-4 leading-tight">
                            {{ t('auth.login.welcome_back') }}
                        </h1>
                        <p class="text-xl text-gray-300 leading-relaxed">
                            {{ t('auth.login.subtitle') }}
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
                                <p class="font-medium">{{ t('auth.features.secure') }}</p>
                                <p class="text-gray-400 text-sm">{{ t('auth.features.secure_desc') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">{{ t('auth.features.realtime') }}</p>
                                <p class="text-gray-400 text-sm">{{ t('auth.features.realtime_desc') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">{{ t('auth.features.multidevice') }}</p>
                                <p class="text-gray-400 text-sm">{{ t('auth.features.multidevice_desc') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics preview -->
                    <div class="mt-12 bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="h-8 w-8 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                                    <span class="text-white font-bold">Q</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-white">{{ t('auth.analytics.title') }}</p>
                                    <p class="text-xs text-gray-300">{{ t('auth.analytics.subtitle') }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-green-400 bg-green-400/20 px-2 py-1 rounded-full">{{ t('auth.analytics.active') }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-lg font-semibold text-white">2.1K</p>
                                <p class="text-xs text-gray-300">{{ t('auth.analytics.views') }}</p>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-white">4.8%</p>
                                <p class="text-xs text-gray-300">{{ t('auth.analytics.engagement') }}</p>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-white">156</p>
                                <p class="text-xs text-gray-300">{{ t('auth.analytics.saves') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Background pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-20 left-20 w-32 h-32 bg-white rounded-full"></div>
                    <div class="absolute bottom-20 right-20 w-24 h-24 bg-indigo-400 rounded-full"></div>
                    <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-purple-400 rounded-full"></div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-12 xl:px-20">
                <div class="mx-auto w-full max-w-md">
                    <!-- Mobile logo -->
                    <div class="lg:hidden mb-8 text-center">
                        <Link href="/" class="flex items-center justify-center space-x-2">
                            <div class="h-10 w-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-lg">Q</span>
                            </div>
                            <span class="text-2xl font-bold text-gray-900">Qard</span>
                        </Link>
                    </div>

                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ t('auth.login.heading') }}</h2>
                        <p class="text-gray-600">{{ t('auth.login.description') }}</p>
                    </div>

                    <div v-if="status" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm font-medium text-green-800">{{ status }}</p>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="identifier" :value="identifierLabel" class="text-gray-700 font-medium" />

                            <TextInput
                                id="identifier"
                                :type="inputType"
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                v-model="form.identifier"
                                required
                                autofocus
                                autocomplete="username"
                                :placeholder="identifierPlaceholder"
                            />

                            <InputError class="mt-2" :message="form.errors.identifier" />
                        </div>

                        <div>
                            <InputLabel for="password" :value="t('auth.login.password')" class="text-gray-700 font-medium" />

                            <TextInput
                                id="password"
                                type="password"
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                :placeholder="t('auth.login.enter_password')"
                            />

                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <Checkbox name="remember" v-model:checked="form.remember" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900" />
                                <span class="ms-2 text-sm text-gray-600">{{ t('auth.login.remember') }}</span>
                            </label>

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-gray-600 hover:text-gray-900 transition"
                            >
                                {{ t('auth.login.forgot_password') }}
                            </Link>
                        </div>

                        <div class="pt-4">
                            <PrimaryButton
                                class="w-full bg-gray-900 hover:bg-gray-800 text-white px-8 py-3 rounded-lg font-medium transition shadow-sm"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">{{ t('auth.login.submitting') }}</span>
                                <span v-else>{{ t('auth.login.submit') }}</span>
                            </PrimaryButton>
                        </div>
                    </form>

                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-600">
                            {{ t('auth.login.no_account') }}
                            <Link :href="route('register')" class="text-gray-900 hover:text-gray-700 font-medium transition">
                                {{ t('auth.login.create_account') }}
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
