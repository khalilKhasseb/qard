<?php

namespace App\Contracts\Sms;

/**
 * Contract for SMS providers.
 *
 * Implement this interface to add support for any SMS gateway.
 * The provider is responsible only for sending the message - OTP logic is handled separately.
 */
interface SmsProvider
{
    /**
     * Send an SMS message.
     *
     * @param  string  $to  Phone number in E.164 format (e.g., +1234567890)
     * @param  string  $message  The message content
     * @return SmsResult Result of the send operation
     */
    public function send(string $to, string $message): SmsResult;

    /**
     * Get the provider name for logging/debugging.
     */
    public function getName(): string;

    /**
     * Check if the provider is properly configured and ready to use.
     */
    public function isConfigured(): bool;
}
