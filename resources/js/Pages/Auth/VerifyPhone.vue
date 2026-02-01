<script setup>
import InputError from '@/Components/Shared/InputError.vue';
import PrimaryButton from '@/Components/Shared/PrimaryButton.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    phone: String,
    hasOtp: Boolean,
    cooldownSeconds: Number,
    remainingAttempts: Number,
    status: String,
});

const form = useForm({
    code: '',
});

const otpInputs = ref(['', '', '', '', '', '']);
const inputRefs = ref([]);
const isVerifying = ref(false);
const isSending = ref(false);
const cooldown = ref(props.cooldownSeconds || 0);
const error = ref('');
const successMessage = ref(props.status || '');
const remainingAttempts = ref(props.remainingAttempts);

let cooldownInterval = null;

const canResend = computed(() => cooldown.value === 0 && !isSending.value);

onMounted(() => {
    if (cooldown.value > 0) {
        startCooldownTimer();
    }

    // Focus first input after a short delay to avoid autofocus conflicts
    setTimeout(() => {
        if (inputRefs.value[0]) {
            inputRefs.value[0].focus();
        }
    }, 100);
});

onUnmounted(() => {
    if (cooldownInterval) {
        clearInterval(cooldownInterval);
    }
});

function startCooldownTimer() {
    cooldownInterval = setInterval(() => {
        if (cooldown.value > 0) {
            cooldown.value--;
        } else {
            clearInterval(cooldownInterval);
        }
    }, 1000);
}

function handleInput(index, event) {
    const value = event.target.value;

    // Only allow digits
    if (!/^\d*$/.test(value)) {
        otpInputs.value[index] = '';
        return;
    }

    otpInputs.value[index] = value.slice(-1); // Keep only last digit

    // Move to next input
    if (value && index < 5) {
        inputRefs.value[index + 1]?.focus();
    }

    // Auto-submit when all fields filled
    const code = otpInputs.value.join('');
    if (code.length === 6) {
        verifyCode();
    }
}

function handleKeydown(index, event) {
    // Handle backspace
    if (event.key === 'Backspace' && !otpInputs.value[index] && index > 0) {
        inputRefs.value[index - 1]?.focus();
    }

    // Handle paste via Ctrl+V/Cmd+V - let the paste event handler deal with it
    // Don't try to use navigator.clipboard here as it may not be available
}

function handlePaste(event) {
    event.preventDefault();

    try {
        const text = event.clipboardData?.getData('text') || '';
        const digits = text.replace(/\D/g, '').slice(0, 6);

        if (digits.length > 0) {
            // Fill in the OTP inputs
            const newInputs = ['', '', '', '', '', ''];
            digits.split('').forEach((digit, i) => {
                if (i < 6) {
                    newInputs[i] = digit;
                }
            });
            otpInputs.value = newInputs;

            // Focus the appropriate input
            if (digits.length < 6) {
                inputRefs.value[digits.length]?.focus();
            } else {
                // Auto-verify when all 6 digits are pasted
                verifyCode();
            }
        }
    } catch (err) {
        console.error('Paste handling error:', err);
    }
}

async function verifyCode() {
    const code = otpInputs.value.join('');
    if (code.length !== 6) {
        error.value = 'Please enter all 6 digits';
        return;
    }

    error.value = '';
    isVerifying.value = true;

    try {
        // Use axios directly - the interceptor in bootstrap.js handles CSRF refresh
        const response = await axios.post(route('phone.verification.verify'), { code });

        if (response.data.success) {
            successMessage.value = response.data.message;
            // Redirect to dashboard
            setTimeout(() => {
                router.visit(response.data.redirect || route('dashboard'));
            }, 1000);
        }
    } catch (err) {
        if (err.response?.status === 422) {
            error.value = err.response.data.message || 'Invalid verification code';
            remainingAttempts.value = err.response.data.remainingAttempts;
            // Clear inputs
            otpInputs.value = ['', '', '', '', '', ''];
            inputRefs.value[0]?.focus();
        } else if (err.response?.status === 419) {
            error.value = 'Session expired. Please refresh the page and try again.';
        } else {
            error.value = 'An error occurred. Please try again.';
        }
    } finally {
        isVerifying.value = false;
    }
}

async function resendCode() {
    if (!canResend.value) return;

    error.value = '';
    isSending.value = true;

    try {
        // Use axios directly - the interceptor in bootstrap.js handles CSRF refresh
        const response = await axios.post(route('phone.verification.send'));

        if (response.data.success) {
            successMessage.value = 'New code sent!';
            cooldown.value = 60; // Default cooldown
            startCooldownTimer();
            // Clear inputs
            otpInputs.value = ['', '', '', '', '', ''];
            inputRefs.value[0]?.focus();
        }
    } catch (err) {
        if (err.response?.status === 429) {
            error.value = 'Please wait before requesting another code';
            cooldown.value = err.response.data.cooldownSeconds || 60;
            startCooldownTimer();
        } else if (err.response?.status === 419) {
            error.value = 'Session expired. Please refresh the page and try again.';
        } else {
            error.value = err.response?.data?.message || 'Failed to send code';
        }
    } finally {
        isSending.value = false;
    }
}

function formatCooldown(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return mins > 0 ? `${mins}:${secs.toString().padStart(2, '0')}` : `${secs}s`;
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-4">
        <Head title="Verify Phone - Qard" />

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
                <!-- Phone icon -->
                <div class="mx-auto w-16 h-16 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>

                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Verify Your Phone</h1>
                    <p class="text-gray-600">
                        We sent a 6-digit code to<br />
                        <span class="font-medium text-gray-900">{{ phone }}</span>
                    </p>
                </div>

                <!-- Success message -->
                <div v-if="successMessage" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-green-700 text-sm">{{ successMessage }}</span>
                    </div>
                </div>

                <!-- Error message -->
                <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-red-700 text-sm">{{ error }}</span>
                    </div>
                </div>

                <!-- OTP Input -->
                <div class="mb-6">
                    <div class="flex justify-center gap-3" @paste="handlePaste">
                        <input
                            v-for="(digit, index) in otpInputs"
                            :key="index"
                            :ref="el => inputRefs[index] = el"
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            :value="digit"
                            @input="handleInput(index, $event)"
                            @keydown="handleKeydown(index, $event)"
                            @paste="handlePaste"
                            class="w-12 h-14 text-center text-xl font-semibold rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                            :class="{ 'border-red-300': error }"
                            :disabled="isVerifying"
                        />
                    </div>
                </div>

                <!-- Verify Button -->
                <PrimaryButton
                    @click="verifyCode"
                    :disabled="isVerifying || otpInputs.join('').length !== 6"
                    class="w-full justify-center bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 py-3 rounded-lg font-medium"
                >
                    <span v-if="isVerifying" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Verifying...
                    </span>
                    <span v-else>Verify Phone</span>
                </PrimaryButton>

                <!-- Remaining attempts -->
                <p v-if="remainingAttempts !== null && remainingAttempts < 5" class="mt-4 text-center text-sm text-gray-500">
                    {{ remainingAttempts }} attempts remaining
                </p>

                <!-- Resend section -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Didn't receive a code?
                    </p>
                    <button
                        @click="resendCode"
                        :disabled="!canResend"
                        class="mt-2 text-sm font-medium transition-colors"
                        :class="canResend
                            ? 'text-indigo-600 hover:text-indigo-800 cursor-pointer'
                            : 'text-gray-400 cursor-not-allowed'"
                    >
                        <span v-if="cooldown > 0">
                            Resend in {{ formatCooldown(cooldown) }}
                        </span>
                        <span v-else-if="isSending">
                            Sending...
                        </span>
                        <span v-else>
                            Resend Code
                        </span>
                    </button>
                </div>

                <!-- Change phone -->
                <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                    <Link
                        :href="route('phone.update')"
                        class="text-sm text-gray-600 hover:text-gray-900 transition"
                    >
                        Wrong phone number? Update it
                    </Link>
                </div>
            </div>

            <!-- Help text -->
            <p class="mt-6 text-center text-xs text-gray-500">
                Having trouble? <a href="#" class="text-indigo-600 hover:underline">Contact support</a>
            </p>
        </div>
    </div>
</template>
