<?php

namespace App\Contracts\Sms;

/**
 * Contract for OTP (One-Time Password) service.
 *
 * Handles generation, storage, sending, and verification of OTPs.
 */
interface OtpService
{
    /**
     * Generate and send an OTP to the given phone number.
     *
     * @param  string  $phone  Phone number in E.164 format
     * @param  string  $purpose  Purpose identifier (e.g., 'registration', 'login', 'password_reset')
     * @return OtpResult Result of the operation
     */
    public function send(string $phone, string $purpose = 'verification'): OtpResult;

    /**
     * Verify an OTP code.
     *
     * @param  string  $phone  Phone number the OTP was sent to
     * @param  string  $code  The OTP code to verify
     * @param  string  $purpose  Purpose identifier (must match the send purpose)
     * @return bool True if verification successful
     */
    public function verify(string $phone, string $code, string $purpose = 'verification'): bool;

    /**
     * Resend an OTP to the given phone number.
     *
     * Respects rate limiting and cooldown periods.
     *
     * @param  string  $phone  Phone number in E.164 format
     * @param  string  $purpose  Purpose identifier
     * @return OtpResult Result of the operation
     */
    public function resend(string $phone, string $purpose = 'verification'): OtpResult;

    /**
     * Check if an OTP exists and is still valid (not expired).
     *
     * @param  string  $phone  Phone number
     * @param  string  $purpose  Purpose identifier
     */
    public function hasValidOtp(string $phone, string $purpose = 'verification'): bool;

    /**
     * Get remaining cooldown seconds before a new OTP can be sent.
     *
     * @param  string  $phone  Phone number
     * @param  string  $purpose  Purpose identifier
     * @return int Seconds remaining, 0 if no cooldown
     */
    public function getCooldownSeconds(string $phone, string $purpose = 'verification'): int;

    /**
     * Invalidate any existing OTP for the given phone and purpose.
     *
     * @param  string  $phone  Phone number
     * @param  string  $purpose  Purpose identifier
     */
    public function invalidate(string $phone, string $purpose = 'verification'): void;
}
