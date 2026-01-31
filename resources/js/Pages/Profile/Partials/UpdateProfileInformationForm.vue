<script setup>
import InputError from '@/Components/Shared/InputError.vue';
import InputLabel from '@/Components/Shared/InputLabel.vue';
import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';
import TextInput from '@/Components/Shared/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    verificationMethod: {
        type: String,
        default: 'email',
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone || '',
});

// Track if phone has been changed
const originalPhone = ref(user.phone || '');
const phoneChanged = computed(() => {
    // Normalize both for comparison (remove non-digits except +)
    const normalizePhone = (p) => p.replace(/[^0-9+]/g, '');
    return normalizePhone(form.phone) !== normalizePhone(originalPhone.value);
});

const showEmailVerificationWarning = computed(() => {
    return props.mustVerifyEmail && user.email_verified_at === null;
});

const showPhoneVerificationWarning = computed(() => {
    return user.phone && user.phone_verified_at === null;
});

const statusMessage = computed(() => {
    switch (props.status) {
        case 'verification-link-sent':
            return t('profile.status.verification_link_sent');
        case 'profile-updated-verify-email':
            return t('profile.status.profile_updated_verify_email');
        case 'profile-updated-verify-phone':
            return t('profile.status.profile_updated_verify_phone');
        case 'profile-updated-verify-both':
            return t('profile.status.profile_updated_verify_both');
        default:
            return null;
    }
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ t('profile.information.title') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ t('profile.information.description') }}
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" :value="t('profile.information.name')" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" :value="t('profile.information.email')" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />

                <div v-if="showEmailVerificationWarning" class="mt-2">
                    <p class="text-sm text-gray-800">
                        {{ t('profile.email_verification.unverified') }}
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="rounded-md text-sm text-indigo-600 underline hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ t('profile.email_verification.resend') }}
                        </Link>
                    </p>
                </div>
            </div>

            <div>
                <InputLabel for="phone" :value="t('profile.information.phone')" />

                <TextInput
                    id="phone"
                    type="tel"
                    class="mt-1 block w-full"
                    v-model="form.phone"
                    required
                    autocomplete="tel"
                    placeholder="+1234567890"
                />

                <p class="mt-1 text-xs text-gray-500">
                    {{ t('profile.information.phone_hint') }}
                </p>

                <InputError class="mt-2" :message="form.errors.phone" />

                <!-- Phone change warning -->
                <div v-if="phoneChanged" class="mt-2 rounded-md bg-amber-50 p-3">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ms-3">
                            <p class="text-sm text-amber-700">
                                {{ t('profile.phone_verification.change_warning') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="showPhoneVerificationWarning && !phoneChanged" class="mt-2">
                    <p class="text-sm text-gray-800">
                        {{ t('profile.phone_verification.unverified') }}
                        <Link
                            :href="route('phone.verification.notice')"
                            class="rounded-md text-sm text-indigo-600 underline hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ t('profile.phone_verification.verify_link') }}
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Status Messages -->
            <div v-if="statusMessage" class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ms-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ statusMessage }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">
                    <span v-if="form.processing">{{ t('profile.information.saving') }}</span>
                    <span v-else-if="phoneChanged">{{ t('profile.information.save_verify_phone') }}</span>
                    <span v-else>{{ t('common.buttons.save') }}</span>
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        {{ t('profile.information.saved') }}
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
