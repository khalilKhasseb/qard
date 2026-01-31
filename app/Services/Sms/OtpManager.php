<?php

namespace App\Services\Sms;

use App\Contracts\Sms\OtpResult;
use App\Contracts\Sms\OtpService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * OTP Manager - Handles OTP generation, storage, and verification.
 *
 * Uses cache for OTP storage with configurable expiration.
 * Implements rate limiting and max attempts protection.
 */
class OtpManager implements OtpService
{
    protected SmsManager $smsManager;

    /**
     * OTP length (number of digits).
     */
    protected int $length;

    /**
     * OTP expiration time in seconds.
     */
    protected int $expiresIn;

    /**
     * Cooldown between OTP requests in seconds.
     */
    protected int $cooldown;

    /**
     * Maximum verification attempts.
     */
    protected int $maxAttempts;

    /**
     * Message template for OTP.
     */
    protected string $messageTemplate;

    public function __construct(SmsManager $smsManager)
    {
        $this->smsManager = $smsManager;
        $this->length = config('sms.otp.length', 6);
        $this->expiresIn = config('sms.otp.expires_in', 300); // 5 minutes
        $this->cooldown = config('sms.otp.cooldown', 60); // 1 minute
        $this->maxAttempts = config('sms.otp.max_attempts', 5);
        $this->messageTemplate = config('sms.otp.message', 'Your verification code is: {code}. Valid for {minutes} minutes.');
    }

    public function send(string $phone, string $purpose = 'verification'): OtpResult
    {
        $phone = $this->normalizePhone($phone);

        // Check cooldown
        $cooldownRemaining = $this->getCooldownSeconds($phone, $purpose);
        if ($cooldownRemaining > 0) {
            return OtpResult::rateLimited($cooldownRemaining);
        }

        // Generate OTP
        $code = $this->generateCode();

        // Store OTP in cache
        $this->storeOtp($phone, $purpose, $code);

        // Set cooldown
        $this->setCooldown($phone, $purpose);

        // Build message
        $message = $this->buildMessage($code);

        // Send via SMS provider
        $result = $this->smsManager->send($phone, $message);

        if ($result->isFailure()) {
            Log::error('OTP SMS send failed', [
                'phone' => $this->maskPhone($phone),
                'purpose' => $purpose,
                'error' => $result->error,
            ]);

            return OtpResult::failure($result->error ?? 'Failed to send OTP');
        }

        Log::info('OTP sent', [
            'phone' => $this->maskPhone($phone),
            'purpose' => $purpose,
            'provider' => $this->smsManager->getDefaultProvider(),
        ]);

        return OtpResult::success(
            expiresInSeconds: $this->expiresIn,
            cooldownSeconds: $this->cooldown,
            attemptsRemaining: $this->maxAttempts
        );
    }

    public function verify(string $phone, string $code, string $purpose = 'verification'): bool
    {
        $phone = $this->normalizePhone($phone);
        $cacheKey = $this->getCacheKey($phone, $purpose);
        $attemptsKey = $this->getAttemptsKey($phone, $purpose);

        // Check if OTP exists
        $storedData = Cache::get($cacheKey);
        if (! $storedData) {
            Log::warning('OTP verification failed - no OTP found', [
                'phone' => $this->maskPhone($phone),
                'purpose' => $purpose,
            ]);

            return false;
        }

        // Check attempts
        $attempts = Cache::get($attemptsKey, 0);
        if ($attempts >= $this->maxAttempts) {
            Log::warning('OTP verification failed - max attempts exceeded', [
                'phone' => $this->maskPhone($phone),
                'purpose' => $purpose,
            ]);

            return false;
        }

        // Verify code
        if (hash_equals($storedData['code'], $code)) {
            // Success - clear OTP and attempts
            $this->invalidate($phone, $purpose);

            Log::info('OTP verified successfully', [
                'phone' => $this->maskPhone($phone),
                'purpose' => $purpose,
            ]);

            return true;
        }

        // Failed - increment attempts
        Cache::put($attemptsKey, $attempts + 1, $this->expiresIn);

        Log::warning('OTP verification failed - invalid code', [
            'phone' => $this->maskPhone($phone),
            'purpose' => $purpose,
            'attempts' => $attempts + 1,
        ]);

        return false;
    }

    public function resend(string $phone, string $purpose = 'verification'): OtpResult
    {
        // Resend is essentially the same as send, but we might want to
        // track resend counts separately in the future
        return $this->send($phone, $purpose);
    }

    public function hasValidOtp(string $phone, string $purpose = 'verification'): bool
    {
        $phone = $this->normalizePhone($phone);
        $cacheKey = $this->getCacheKey($phone, $purpose);

        return Cache::has($cacheKey);
    }

    public function getCooldownSeconds(string $phone, string $purpose = 'verification'): int
    {
        $phone = $this->normalizePhone($phone);
        $cooldownKey = $this->getCooldownKey($phone, $purpose);

        $expiresAt = Cache::get($cooldownKey);
        if (! $expiresAt) {
            return 0;
        }

        $remaining = $expiresAt - time();

        return max(0, $remaining);
    }

    public function invalidate(string $phone, string $purpose = 'verification'): void
    {
        $phone = $this->normalizePhone($phone);

        Cache::forget($this->getCacheKey($phone, $purpose));
        Cache::forget($this->getAttemptsKey($phone, $purpose));
        Cache::forget($this->getCooldownKey($phone, $purpose));
    }

    /**
     * Get remaining verification attempts.
     */
    public function getRemainingAttempts(string $phone, string $purpose = 'verification'): int
    {
        $phone = $this->normalizePhone($phone);
        $attemptsKey = $this->getAttemptsKey($phone, $purpose);
        $attempts = Cache::get($attemptsKey, 0);

        return max(0, $this->maxAttempts - $attempts);
    }

    /**
     * Generate a random OTP code.
     */
    protected function generateCode(): string
    {
        $min = pow(10, $this->length - 1);
        $max = pow(10, $this->length) - 1;

        return (string) random_int($min, $max);
    }

    /**
     * Store OTP in cache.
     */
    protected function storeOtp(string $phone, string $purpose, string $code): void
    {
        $cacheKey = $this->getCacheKey($phone, $purpose);

        Cache::put($cacheKey, [
            'code' => $code,
            'created_at' => time(),
        ], $this->expiresIn);
    }

    /**
     * Set cooldown for OTP requests.
     */
    protected function setCooldown(string $phone, string $purpose): void
    {
        $cooldownKey = $this->getCooldownKey($phone, $purpose);
        Cache::put($cooldownKey, time() + $this->cooldown, $this->cooldown);
    }

    /**
     * Build the OTP message from template.
     */
    protected function buildMessage(string $code): string
    {
        return str_replace(
            ['{code}', '{minutes}'],
            [$code, ceil($this->expiresIn / 60)],
            $this->messageTemplate
        );
    }

    /**
     * Normalize phone number to E.164 format.
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Ensure it starts with +
        if (! str_starts_with($phone, '+')) {
            // Assume it needs a + prefix
            $phone = '+'.$phone;
        }

        return $phone;
    }

    /**
     * Mask phone number for logging.
     */
    protected function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 6) {
            return '***';
        }

        return substr($phone, 0, 4).str_repeat('*', $length - 6).substr($phone, -2);
    }

    /**
     * Get cache key for OTP storage.
     */
    protected function getCacheKey(string $phone, string $purpose): string
    {
        return "otp:{$purpose}:{$phone}";
    }

    /**
     * Get cache key for attempts tracking.
     */
    protected function getAttemptsKey(string $phone, string $purpose): string
    {
        return "otp_attempts:{$purpose}:{$phone}";
    }

    /**
     * Get cache key for cooldown tracking.
     */
    protected function getCooldownKey(string $phone, string $purpose): string
    {
        return "otp_cooldown:{$purpose}:{$phone}";
    }
}
