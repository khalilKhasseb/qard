<?php

namespace App\Contracts\Sms;

/**
 * Result object for SMS send operations.
 *
 * Provides a consistent interface for checking send results regardless of provider.
 */
class SmsResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $messageId = null,
        public readonly ?string $error = null,
        public readonly array $metadata = []
    ) {}

    /**
     * Create a successful result.
     */
    public static function success(?string $messageId = null, array $metadata = []): self
    {
        return new self(
            success: true,
            messageId: $messageId,
            metadata: $metadata
        );
    }

    /**
     * Create a failed result.
     */
    public static function failure(string $error, array $metadata = []): self
    {
        return new self(
            success: false,
            error: $error,
            metadata: $metadata
        );
    }

    /**
     * Check if the send was successful.
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Check if the send failed.
     */
    public function isFailure(): bool
    {
        return ! $this->success;
    }
}
