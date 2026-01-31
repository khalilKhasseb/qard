<script setup>
import InputError from '@/Components/Shared/InputError.vue';
import InputLabel from '@/Components/Shared/InputLabel.vue';
import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';
import TextInput from '@/Components/Shared/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    currentPhone: String,
});

const form = useForm({
    phone: '',
});

const submit = () => {
    form.post(route('phone.update'));
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-4">
        <Head title="Update Phone - Qard" />

        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center space-x-2">
                    <div class="h-12 w-12 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                        <span class="text-white font-bold text-xl">Q</span>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">Qard</span>
                </Link>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8">
                <!-- Icon -->
                <div class="mx-auto w-16 h-16 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>

                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Update Phone Number</h1>
                    <p class="text-gray-600">
                        Enter your new phone number.<br />
                        We'll send a verification code to confirm it.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div v-if="currentPhone" class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">Current number:</p>
                        <p class="font-medium text-gray-900">{{ currentPhone }}</p>
                    </div>

                    <div>
                        <InputLabel for="phone" value="New Phone Number" class="text-gray-700 font-medium" />

                        <TextInput
                            id="phone"
                            type="tel"
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            v-model="form.phone"
                            required
                            autofocus
                            autocomplete="tel"
                            placeholder="+1 (555) 123-4567"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Include country code (e.g., +1 for US)
                        </p>

                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>

                    <PrimaryButton
                        type="submit"
                        :disabled="form.processing"
                        class="w-full justify-center bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 py-3 rounded-lg font-medium"
                    >
                        <span v-if="form.processing" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Updating...
                        </span>
                        <span v-else>Send Verification Code</span>
                    </PrimaryButton>
                </form>

                <!-- Back link -->
                <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                    <Link
                        :href="route('phone.verification.notice')"
                        class="text-sm text-gray-600 hover:text-gray-900 transition"
                    >
                        Back to verification
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
