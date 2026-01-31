<?php

namespace App\Contracts\Sms;

/**
 * Result object for OTP operations.
 */
class OtpResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $error = null,
        public readonly ?int $expiresInSeconds = null,
        public readonly ?int $cooldownSeconds = null,
        public readonly ?int $attemptsRemaining = null,
        public readonly array $metadata = []
    ) {}

    /**
     * Create a successful result.
     */
    public static function success(
        int $expiresInSeconds,
        ?int $cooldownSeconds = null,
        ?int $attemptsRemaining = null,
        array $metadata = []
    ): self {
        return new self(
            success: true,
            expiresInSeconds: $expiresInSeconds,
            cooldownSeconds: $cooldownSeconds,
            attemptsRemaining: $attemptsRemaining,
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
     * Create a rate-limited result.
     */
    public static function rateLimited(int $cooldownSeconds): self
    {
        return new self(
            success: false,
            error: 'rate_limited',
            cooldownSeconds: $cooldownSeconds
        );
    }

    /**
     * Create a max attempts exceeded result.
     */
    public static function maxAttemptsExceeded(): self
    {
        return new self(
            success: false,
            error: 'max_attempts_exceeded'
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isRateLimited(): bool
    {
        return $this->error === 'rate_limited';
    }

    public function isMaxAttemptsExceeded(): bool
    {
        return $this->error === 'max_attempts_exceeded';
    }
}
